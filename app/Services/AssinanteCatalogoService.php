<?php

namespace App\Services;

use App\Models\AccessLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Catálogo da área do assinante.
 *
 * Unidade do catálogo (o "card"):
 *  - PAINEL (panels) para minisséries e para turmas gravadas só com painéis de 1 aula;
 *  - TURMA (classes) inteira para turmas gravadas com algum painel de mais de 1 aula;
 *  - CURSO para os itens de modular_courses (não têm painel).
 *
 * NOMENCLATURA DE PRODUTO (só apresentação — chaves/código/banco intocados):
 *  - painel de minissérie                         => "Curso Minissérie"         (tipo 'minisserie')
 *  - painel de turma gravada só de 1 aula         => "Curso Modular"            (tipo 'gravado')
 *  - turma gravada com algum painel de >1 aula    => "Curso Livre Aprofundado"  (tipo 'livre')
 *  - modular_courses                              => "Apostilas e Materiais Pós-Graduação" (tipo 'modular')
 * Regra por TURMA (2026-09-02): basta um painel com mais de 1 aula para a turma inteira
 * virar um card único de Curso Livre, com todos os seus painéis dentro (inclusive os de
 * 1 aula). Atenção: o nome "Curso Modular" TROCOU de dono — antes designava
 * modular_courses, agora designa o painel de 1 aula. Não confundir ao mexer nos rótulos.
 *
 * Regras (decididas com o produto em 2026-08):
 *  - Turmas elegíveis: minissérie (express='1', able) e gravado (unyflex=1, express='0', able),
 *    exceto as listadas em assinante_catalogo_ocultos (database/assinante_catalogo_ocultos.sql):
 *    edições gravadas duplicadas de uma minissérie de mesmo nome. Só apresentação — matrícula,
 *    views, provas e o player de quem já acessa a turma não mudam. Sem a tabela, nada é ocultado.
 *  - Só entram painéis able com pelo menos uma video_lesson com link não vazio.
 *  - Deduplicação entre turmas por (course_id, título do painel): fica o da turma mais recente.
 *  - Título do card: "{turma} — {painel}". Painel sem título (vazio ou "-") ou com título
 *    repetido na mesma turma recebe o número do painel (ordem start_time, id — a mesma do player).
 *  - Categoria herdada da turma (classes.course_id → category_courses → categories), ignorando
 *    categorias disabled e as estruturais; painel sem categoria válida vai para "Sem categoria".
 *  - Filtros, busca, ordenação e paginação são server-side.
 *
 * Tudo aqui é somente leitura no banco.
 */
class AssinanteCatalogoService
{
    public const POR_PAGINA = 24;

    public const CATEGORIAS_EXCLUIDAS = ['Desativados', 'Minisseries', 'Outros', 'Midias'];

    public const SEM_CATEGORIA = 'sem-categoria';

    public const TIPOS = [
        'minisserie' => 'Cursos Minissérie',
        'gravado'    => 'Cursos Modulares',
        'livre'      => 'Cursos Livres Aprofundados',
        'modular'    => 'Apostilas e Materiais Pós-Graduação',
    ];

    /** Rótulo curto do badge do card (o filtro e os chips usam TIPOS). */
    public const TIPOS_BADGE = [
        'minisserie' => 'Curso Minissérie',
        'gravado'    => 'Curso Modular',
        'livre'      => 'Curso Livre Aprofundado',
        'modular'    => 'Pós-Graduação',
    ];

    public const ORDENACOES = [
        'recentes' => 'Mais recentes',
        'az'       => 'A – Z',
        'aulas'    => 'Mais aulas',
    ];

    private const CACHE_META    = 'assinante.catalogo.meta.v4';
    private const CACHE_OCULTOS = 'assinante.catalogo.ocultos.v1';
    private const CACHE_TTL     = 600; // 10 min

    // ══════════════════════════════════════════════════════════════════════
    // Entrada
    // ══════════════════════════════════════════════════════════════════════

    /** Normaliza os filtros vindos da query string. */
    public function filtros(Request $request): array
    {
        $tipo  = (string) $request->query('tipo', '');
        $ordem = (string) $request->query('ordem', 'recentes');

        return [
            'busca'     => Str::limit(trim((string) $request->query('busca', '')), 80, ''),
            'tipo'      => array_key_exists($tipo, self::TIPOS) ? $tipo : '',
            'categoria' => trim((string) $request->query('categoria', '')),
            'ordem'     => array_key_exists($ordem, self::ORDENACOES) ? $ordem : 'recentes',
            'assistido' => $request->boolean('assistido'),
        ];
    }

    /** Lista paginada de itens do catálogo já enriquecidos para a view. */
    public function listar(array $f, int $userId, ?int $studentId): LengthAwarePaginator
    {
        $modularesVistos = $this->modularesAssistidos($studentId);

        $consulta = $this->consultaUnificada($f, $userId, $modularesVistos);

        $itens = $consulta
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        $categoriasPorCurso = $this->categoriasPorCurso(
            $itens->getCollection()->pluck('course_id')->filter()->unique()->values()->all()
        );

        $itens->getCollection()->transform(
            fn ($row) => $this->montarItem($row, $categoriasPorCurso, $modularesVistos)
        );

        return $itens;
    }

    /** IDs dos painéis exibidos no catálogo (após dedup e filtro de aulas). Usado pela geração de provas. */
    public function idsPaineisExibiveis(): array
    {
        return DB::query()->fromSub($this->paineisExibiveis(), 'b')
            ->orderBy('b.item_id')
            ->pluck('b.item_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /** Contadores globais (em cards) e lista de categorias para o filtro. Cacheado. */
    public function meta(): array
    {
        return Cache::remember(self::CACHE_META, self::CACHE_TTL, function () {
            $porTipo = DB::query()->fromSub($this->cardsCursos(0), 'b')
                ->selectRaw('b.tipo, COUNT(*) AS qtd')
                ->groupBy('b.tipo')
                ->pluck('qtd', 'tipo');

            $modulares = DB::table('modular_courses')->where('status', 'publicado')->count();

            $categorias = DB::query()->fromSub($this->cardsCursos(0), 'b')
                ->join('category_courses as cc', 'cc.course_id', '=', 'b.course_id')
                ->join('categories as cat', 'cat.id', '=', 'cc.category_id')
                ->where('cat.status', 'able')
                ->whereNotIn('cat.title', self::CATEGORIAS_EXCLUIDAS)
                ->selectRaw('cat.slug, cat.title, COUNT(DISTINCT b.tipo, b.item_id) AS paineis')
                ->groupBy('cat.slug', 'cat.title')
                ->orderBy('cat.title')
                ->get()
                ->map(fn ($c) => ['slug' => $c->slug, 'titulo' => $c->title, 'paineis' => (int) $c->paineis])
                ->all();

            $semCategoria = DB::query()->fromSub($this->cardsCursos(0), 'b')
                ->whereNotExists(fn ($q) => $this->existsCategoriaValida($q))
                ->count();

            $minisserie = (int) ($porTipo['minisserie'] ?? 0);
            $gravado    = (int) ($porTipo['gravado'] ?? 0);
            $livre      = (int) ($porTipo['livre'] ?? 0);

            return [
                'minisserie'    => $minisserie,
                'gravado'       => $gravado,
                'livre'         => $livre,
                'paineis'       => $minisserie + $gravado + $livre, // cards de curso (sem as apostilas)
                'modular'       => $modulares,
                'categorias'    => $categorias,
                'sem_categoria' => $semCategoria,
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // Consultas
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Base: TODOS os painéis able das turmas elegíveis, com agregações por subselect
     * (sem N+1) e numeração por janela. O filtro "aulas > 0" fica para fora para que a
     * numeração do painel bata com a do player (que lista todos os painéis able).
     */
    private function paineisBase(): Builder
    {
        $aulas    = "(SELECT COUNT(*)   FROM video_lessons vl WHERE vl.panel_id = p.id AND vl.link IS NOT NULL AND vl.link <> '')";
        $primeiro = "(SELECT MIN(vl.id) FROM video_lessons vl WHERE vl.panel_id = p.id AND vl.link IS NOT NULL AND vl.link <> '')";

        // Inteiros embutidos (sem binding): esta query vira subselect dentro de UNION.
        $ocultos = $this->ocultos();

        return DB::table('panels as p')
            ->join('classes as c', 'c.id', '=', 'p.classes_id')
            ->where('p.status', 'able')
            ->where('c.status', 'able')
            ->when($ocultos, fn ($q) => $q->whereRaw('c.id NOT IN (' . implode(',', $ocultos) . ')'))
            ->where(function ($q) {
                $q->where('c.express', '1')
                  ->orWhere(fn ($q2) => $q2->where('c.unyflex', 1)->where('c.express', '0'));
            })
            ->selectRaw("
                CASE WHEN c.express = '1' THEN 'minisserie' ELSE 'gravado' END AS tipo,
                p.id          AS item_id,
                p.classes_id  AS classes_id,
                c.course_id   AS course_id,
                c.slug        AS slug,
                c.title       AS turma,
                c.start_date  AS turma_data,
                p.title       AS painel,
                p.start_time  AS data_ref,
                {$aulas}      AS aulas,
                {$primeiro}   AS primeiro_video_id,
                ROW_NUMBER() OVER (PARTITION BY p.classes_id ORDER BY p.start_time, p.id) AS numero,
                COUNT(*)     OVER (PARTITION BY p.classes_id, LOWER(TRIM(p.title)))       AS repeticoes
            ");
    }

    /**
     * Turmas ocultas do catálogo (assinante_catalogo_ocultos). Cache curto para a
     * edição no phpMyAdmin refletir em até 1 min; sem a tabela, lista vazia.
     *
     * @return int[]
     */
    protected function ocultos(): array
    {
        return Cache::remember(self::CACHE_OCULTOS, 60, function () {
            try {
                return DB::table('assinante_catalogo_ocultos')
                    ->pluck('classes_id')
                    ->map(fn ($v) => (int) $v)
                    ->all();
            } catch (\Illuminate\Database\QueryException $e) {
                return [];
            }
        });
    }

    /**
     * Painéis exibíveis: com aula E deduplicados entre turmas.
     * Dedup por (course_id, título do painel): vence a turma mais recente (start_date, depois id).
     * Mantém TODOS os painéis da turma vencedora com aquele título (duplicados internos à turma
     * continuam existindo e são diferenciados pelo número — decisão de produto).
     */
    private function paineisExibiveis(): Builder
    {
        $comAula = DB::query()->fromSub($this->paineisBase(), 'b')
            ->where('b.aulas', '>', 0)
            ->selectRaw("
                b.*,
                FIRST_VALUE(b.classes_id) OVER (
                    PARTITION BY b.course_id, LOWER(TRIM(b.painel))
                    ORDER BY b.turma_data DESC, b.classes_id DESC
                ) AS classe_vencedora
            ");

        return DB::query()->fromSub($comAula, 'e')
            ->whereColumn('e.classes_id', 'e.classe_vencedora');
    }

    /**
     * Cards de curso (minissérie, gravado e livre) com a coluna `vistos` do usuário.
     * Colunas na ordem do UNION com modularesFiltrados():
     *   tipo, item_id, classes_id, course_id, slug, turma, painel, data_ref, aulas,
     *   primeiro_video_id, numero, repeticoes, paineis, vistos
     *
     * Regra por TURMA: `max_aulas` = maior nº de aulas entre os painéis exibíveis da turma.
     *  - minissérie: 1 card por painel, sempre;
     *  - gravado com max_aulas = 1: 1 card por painel ("Curso Modular");
     *  - gravado com max_aulas > 1: 1 card por turma ("Curso Livre Aprofundado"), agregando
     *    todos os painéis exibíveis (item_id/primeiro_video_id = primeiro painel, na ordem
     *    do player; aulas = soma; vistos = vídeos da turma já vistos pelo usuário).
     */
    private function cardsCursos(int $userId): Builder
    {
        $uid = (int) $userId; // inteiro embutido de propósito: evita binding dentro de UNION

        $classificado = DB::query()->fromSub($this->paineisExibiveis(), 'e')
            ->selectRaw('e.*, MAX(e.aulas) OVER (PARTITION BY e.classes_id) AS max_aulas');

        $porPainel = DB::query()->fromSub($classificado, 'b')
            ->where(fn ($q) => $q->where('b.tipo', 'minisserie')->orWhere('b.max_aulas', 1))
            ->selectRaw("
                b.tipo, b.item_id, b.classes_id, b.course_id, b.slug, b.turma, b.painel,
                b.data_ref, b.aulas, b.primeiro_video_id, b.numero, b.repeticoes,
                1 AS paineis,
                (SELECT COUNT(DISTINCT v.video_id) FROM views_minisseries v
                  WHERE v.id_user = {$uid} AND v.panel_id = b.item_id) AS vistos
            ");

        $porTurma = DB::query()->fromSub($classificado, 'b')
            ->where('b.tipo', 'gravado')
            ->where('b.max_aulas', '>', 1)
            ->groupBy('b.classes_id', 'b.course_id', 'b.slug', 'b.turma')
            ->selectRaw("
                'livre' AS tipo,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(b.item_id ORDER BY b.numero), ',', 1) AS UNSIGNED) AS item_id,
                b.classes_id, b.course_id, b.slug, b.turma,
                NULL AS painel,
                MAX(b.data_ref) AS data_ref,
                SUM(b.aulas) AS aulas,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(b.primeiro_video_id ORDER BY b.numero), ',', 1) AS UNSIGNED) AS primeiro_video_id,
                1 AS numero, 1 AS repeticoes,
                COUNT(*) AS paineis,
                (SELECT COUNT(DISTINCT v.video_id) FROM views_minisseries v
                  JOIN panels px ON px.id = v.panel_id AND px.status = 'able'
                  JOIN video_lessons vl ON vl.id = v.video_id AND vl.link IS NOT NULL AND vl.link <> ''
                  WHERE v.id_user = {$uid} AND px.classes_id = b.classes_id) AS vistos
            ");

        return $porPainel->unionAll($porTurma);
    }

    /** Cards de curso com os filtros aplicados. */
    private function cursosFiltrados(array $f, int $userId): Builder
    {
        $q = DB::query()->fromSub($this->cardsCursos($userId), 'b')->select('b.*');

        if ($f['tipo'] !== '') {
            $q->where('b.tipo', $f['tipo']);
        }

        if ($f['categoria'] === self::SEM_CATEGORIA) {
            $q->whereNotExists(fn ($sub) => $this->existsCategoriaValida($sub));
        } elseif ($f['categoria'] !== '') {
            $q->whereExists(fn ($sub) => $this->existsCategoriaValida($sub)->where('cat.slug', $f['categoria']));
        }

        if ($f['assistido']) {
            $q->where('b.vistos', '>', 0);
        }

        return $q;
    }

    /** Cursos modulares publicados no mesmo formato de colunas dos painéis. */
    private function modularesFiltrados(array $f, array $modularesVistos): Builder
    {
        $q = DB::table('modular_courses as m')
            ->where('m.status', 'publicado')
            ->selectRaw("
                'modular'          AS tipo,
                m.id               AS item_id,
                NULL               AS classes_id,
                NULL               AS course_id,
                m.slug             AS slug,
                m.title            AS turma,
                NULL               AS painel,
                DATE(m.created_at) AS data_ref,
                0                  AS aulas,
                NULL               AS primeiro_video_id,
                1                  AS numero,
                1                  AS repeticoes,
                1                  AS paineis,
                0                  AS vistos
            ");

        if ($f['assistido']) {
            // Lido de access_logs (detail = "Modular: <título>") — formato preservado.
            $q->whereIn('m.title', $modularesVistos ?: ['']);
        }

        return $q;
    }

    /** UNION dos dois conjuntos + busca + ordenação, pronto para paginar. */
    private function consultaUnificada(array $f, int $userId, array $modularesVistos): Builder
    {
        $incluiPaineis   = $f['tipo'] === '' || $f['tipo'] !== 'modular';
        // Modulares não têm categoria: qualquer filtro de categoria os exclui.
        $incluiModulares = ($f['tipo'] === '' || $f['tipo'] === 'modular') && $f['categoria'] === '';

        if ($incluiPaineis && $incluiModulares) {
            $base = $this->cursosFiltrados($f, $userId)->unionAll($this->modularesFiltrados($f, $modularesVistos));
        } elseif ($incluiPaineis) {
            $base = $this->cursosFiltrados($f, $userId);
        } else {
            $base = $this->modularesFiltrados($f, $modularesVistos);
        }

        $q = DB::query()->fromSub($base, 'cat');

        if ($f['busca'] !== '') {
            $termo = '%' . str_replace(['%', '_'], ['\%', '\_'], $f['busca']) . '%';
            $q->where(fn ($w) => $w->where('cat.turma', 'like', $termo)->orWhere('cat.painel', 'like', $termo));
        }

        match ($f['ordem']) {
            'az'    => $q->orderBy('cat.turma')->orderBy('cat.numero')->orderBy('cat.item_id'),
            'aulas' => $q->orderByDesc('cat.aulas')->orderBy('cat.turma')->orderBy('cat.numero'),
            default => $q->orderByDesc('cat.data_ref')->orderByDesc('cat.item_id'),
        };

        return $q;
    }

    /** EXISTS de categoria válida para o course_id da linha `b`. */
    private function existsCategoriaValida(Builder $sub): Builder
    {
        return $sub->select(DB::raw(1))
            ->from('category_courses as cc')
            ->join('categories as cat', 'cat.id', '=', 'cc.category_id')
            ->whereColumn('cc.course_id', 'b.course_id')
            ->where('cat.status', 'able')
            ->whereNotIn('cat.title', self::CATEGORIAS_EXCLUIDAS);
    }

    /** Uma query para as categorias de todos os cursos da página: course_id => [títulos]. */
    private function categoriasPorCurso(array $courseIds): array
    {
        if (! $courseIds) {
            return [];
        }

        return DB::table('category_courses as cc')
            ->join('categories as cat', 'cat.id', '=', 'cc.category_id')
            ->whereIn('cc.course_id', $courseIds)
            ->where('cat.status', 'able')
            ->whereNotIn('cat.title', self::CATEGORIAS_EXCLUIDAS)
            ->orderBy('cat.title')
            ->get(['cc.course_id', 'cat.title', 'cat.slug'])
            ->groupBy('course_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => ['titulo' => $r->title, 'slug' => $r->slug])->values()->all())
            ->all();
    }

    /** Títulos de modulares acessados pelo aluno (access_logs, formato preservado). */
    private function modularesAssistidos(?int $studentId): array
    {
        if (! $studentId) {
            return [];
        }

        return AccessLog::where('student_id', $studentId)
            ->where('action', 'curso_view')
            ->where('detail', 'like', 'Modular: %')
            ->pluck('detail')
            ->map(fn ($d) => trim(str_replace('Modular:', '', $d)))
            ->unique()
            ->values()
            ->all();
    }

    // ══════════════════════════════════════════════════════════════════════
    // Apresentação
    // ══════════════════════════════════════════════════════════════════════

    private function montarItem(object $row, array $categoriasPorCurso, array $modularesVistos): object
    {
        $tipo = $row->tipo;

        if ($tipo === 'modular') {
            $categorias  = [];
            $chaveCat    = 'Apostilas e Materiais Pós-Graduação';
            $painelLabel = $row->turma;
            $titulo      = $row->turma;
            $url         = route('ava.modulares.show', $row->slug);
            $assistido   = in_array($row->turma, $modularesVistos, true);
        } elseif ($tipo === 'livre') {
            // Card da TURMA inteira: abre na primeira aula do primeiro painel (contexto de painel).
            $categorias  = $categoriasPorCurso[$row->course_id] ?? [];
            $chaveCat    = $categorias[0]['titulo'] ?? 'Sem categoria';
            $painelLabel = (int) $row->paineis . ' ' . ((int) $row->paineis === 1 ? 'curso' : 'cursos');
            $titulo      = trim((string) $row->turma);
            $url         = route('player.video', [$row->slug, $row->primeiro_video_id])
                . '?' . http_build_query(['painel' => $row->item_id, 'voltar' => self::voltarAtual()]);
            $assistido   = (int) $row->vistos > 0;
        } else {
            $categorias  = $categoriasPorCurso[$row->course_id] ?? [];
            $chaveCat    = $categorias[0]['titulo'] ?? 'Sem categoria';
            $painelLabel = $this->rotuloPainel($row);
            $titulo      = trim((string) $row->turma) . ' — ' . $painelLabel;
            // Contexto de painel para o player do assinante + filtros atuais para o "Voltar".
            $url         = route('player.video', [$row->slug, $row->primeiro_video_id])
                . '?' . http_build_query(['painel' => $row->item_id, 'voltar' => self::voltarAtual()]);
            $assistido   = (int) $row->vistos > 0;
        }

        return (object) [
            'tipo'         => $tipo,
            'tipo_label'   => self::TIPOS_BADGE[$tipo] ?? $tipo,
            'id'           => (int) $row->item_id,
            'titulo'       => $titulo,
            'painel_label' => $painelLabel,
            'turma'        => $row->turma,
            'painel'       => $row->painel,
            'numero'      => (int) $row->numero,
            'paineis'     => (int) ($row->paineis ?? 1),
            'aulas'       => (int) $row->aulas,
            'vistos'      => (int) $row->vistos,
            'assistido'   => $assistido,
            'concluido'   => $tipo !== 'modular' && (int) $row->aulas > 0 && (int) $row->vistos >= (int) $row->aulas,
            'categorias'  => $categorias,
            'categoria'   => $chaveCat,
            'url'         => $url,
            'visual'      => self::identidadeVisual($chaveCat),
        ];
    }

    /**
     * Parte do título referente ao painel. O título completo do card é "{turma} — {rótulo}".
     * Painel sem título (vazio ou "-") vira "Painel N"; título repetido na turma ganha "(Painel N)".
     * N = posição do painel na turma (ordem start_time, id — a mesma do player).
     */
    private function rotuloPainel(object $row): string
    {
        $painel = trim((string) $row->painel);
        $n      = (int) $row->numero;

        // Nomenclatura de produto: o assinante vê "curso", não "painel" (o código continua falando painel).
        if ($painel === '' || $painel === '-') {
            return "Curso {$n}";
        }
        if ((int) $row->repeticoes > 1) {
            return "{$painel} ({$n})";
        }
        return $painel;
    }

    /** Query string atual do catálogo (filtros + página), para o player devolver ao mesmo lugar. */
    public static function voltarAtual(): string
    {
        return Str::limit((string) request()->getQueryString(), 300, '');
    }

    /** Sanitiza o parâmetro `voltar` recebido pelo player: só uma query string simples e curta. */
    public static function voltarSeguro(?string $voltar): string
    {
        $v = trim((string) $voltar);
        if ($v === '' || strlen($v) > 300 || ! preg_match('/^[A-Za-z0-9=&%._+\-]+$/', $v)) {
            return '';
        }
        return $v;
    }

    /**
     * Identidade visual determinística por categoria: mesma string => mesmos valores sempre.
     * Mantém a família do azul #0088F4 (matiz ~207) variando matiz/ângulo/padrão.
     */
    public static function identidadeVisual(string $chave): array
    {
        if ($chave === 'Sem categoria') {
            return ['h1' => 215, 's1' => 8, 'l1' => 26, 'h2' => 215, 's2' => 6, 'l2' => 12, 'ang' => 135, 'px' => 70, 'py' => 30, 'pattern' => 0, 'neutro' => true];
        }

        $hash = crc32(mb_strtolower(trim($chave)));

        $h1 = 188 + ($hash % 72);                     // 188..259  (ciano → azul → violeta)
        $h2 = ($h1 + 18 + (($hash >> 8) % 34)) % 360; // deslocamento de 18..51º
        $ang = 105 + (($hash >> 16) % 150);           // 105..254º
        $px = 15 + (($hash >> 4) % 70);               // posição do foco radial
        $py = 15 + (($hash >> 12) % 70);
        $pattern = ($hash >> 24) % 4;                 // 0 listras, 1 pontos, 2 anéis, 3 grade

        return ['h1' => $h1, 's1' => 78, 'l1' => 34, 'h2' => $h2, 's2' => 70, 'l2' => 16, 'ang' => $ang, 'px' => $px, 'py' => $py, 'pattern' => $pattern, 'neutro' => false];
    }

    /** String de custom properties CSS para o card. */
    public static function estiloVisual(array $v): string
    {
        return sprintf(
            '--c1:hsl(%d %d%% %d%%);--c2:hsl(%d %d%% %d%%);--ang:%ddeg;--px:%d%%;--py:%d%%',
            $v['h1'], $v['s1'], $v['l1'], $v['h2'], $v['s2'], $v['l2'], $v['ang'], $v['px'], $v['py']
        );
    }
}
