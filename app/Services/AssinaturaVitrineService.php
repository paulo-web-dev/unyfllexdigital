<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Vitrine pública da Assinatura Premium (/assinatura).
 *
 * Reaproveita o catálogo da área do assinante (AssinanteCatalogoService): mesmas regras de
 * visibilidade, deduplicação, turmas ocultas e categorias. Aqui só se acrescenta o que é de
 * vitrine: capas, carrosséis por categoria, números de marketing e planos.
 *
 * Números do catálogo (decisão de produto 2026-10-06):
 *  - "cursos" = painéis de minissérie + painéis gravados (sem os cards de Curso Livre, que
 *    repetem painéis) => base do custo por curso e do selo "+N cursos";
 *  - apostilas (modular_courses publicados) contadas à parte;
 *  - cards sem categoria contam no total, mas ficam fora dos carrosséis e das categorias.
 *
 * Tudo é somente leitura e cacheado (config assinatura_vitrine.cache_ttl).
 */
class AssinaturaVitrineService
{
    private const CACHE_CARDS = 'assinatura.vitrine.cards.v1';
    private const CACHE_META  = 'assinatura.vitrine.meta.v1';

    public function __construct(private AssinanteCatalogoService $catalogo)
    {
    }

    // ══════════════════════════════════════════════════════════════════════
    // Cache
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Recalcula e regrava os dois caches da vitrine (contadores e cards) — vitrine:aquecer-cache.
     * Calcula tudo antes de gravar: o cache antigo continua servindo durante o cálculo e os
     * dois snapshots entram juntos (contagens e cards consistentes entre si).
     * Também regrava o meta() da área do assinante, que é a fonte dos contadores.
     */
    public function aquecer(): void
    {
        $meta  = $this->catalogo->renovarMeta();
        $cards = $this->montarCards();

        Cache::put(self::CACHE_META, $meta, self::ttl());
        Cache::put(self::CACHE_CARDS, $cards, self::ttl());
    }

    /**
     * Snapshot próprio dos contadores do catálogo, com o TTL da vitrine (o meta() da área do
     * assinante expira em 10 min; aqui vale até o próximo aquecimento).
     */
    private function meta(): array
    {
        return Cache::remember(self::CACHE_META, self::ttl(), fn () => $this->catalogo->meta());
    }

    private static function ttl(): int
    {
        return (int) config('assinatura_vitrine.cache_ttl', 3600);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Números
    // ══════════════════════════════════════════════════════════════════════

    /** Totais do catálogo e números derivados (custo por curso, número de marketing). */
    public function resumo(): array
    {
        $meta = $this->meta();

        $cursos    = (int) $meta['minisserie'] + (int) $meta['gravado'];
        $apostilas = (int) $meta['modular'];
        $individual = (float) config('assinatura_vitrine.planos.individual.preco');

        return [
            'cursos'           => $cursos,
            'cursos_marketing' => self::numeroMarketing($cursos),
            'apostilas'        => $apostilas,
            'categorias'       => count($meta['categorias']),
            'custo_por_curso'  => $cursos > 0 ? $individual / $cursos : null,
        ];
    }

    /** Arredonda para baixo para uso em texto ("+450"): >=100 de 50 em 50, >=10 de 10 em 10. */
    public static function numeroMarketing(int $n): string
    {
        $base = match (true) {
            $n >= 100 => intdiv($n, 50) * 50,
            $n >= 10  => intdiv($n, 10) * 10,
            default   => $n,
        };

        return '+' . number_format($base, 0, ',', '.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // Planos
    // ══════════════════════════════════════════════════════════════════════

    /** Planos do config enriquecidos: valor por usuário, economia, %, link do WhatsApp. */
    public function planos(): array
    {
        $individual = (float) config('assinatura_vitrine.planos.individual.preco');
        $cursos     = $this->resumo()['cursos'];

        return collect(config('assinatura_vitrine.planos'))->map(function (array $p, string $chave) use ($individual, $cursos) {
            $usuarios   = max(1, (int) $p['usuarios']);
            $preco      = (float) $p['preco'];
            $precoDe    = $p['preco_de'] !== null ? (float) $p['preco_de'] : null;
            $economia   = $precoDe ? $precoDe - $preco : 0;
            $porUsuario = $preco / $usuarios;

            return (object) [
                'chave'        => $chave,
                'nome'         => $p['nome'],
                'rotulo'       => $p['rotulo'],
                'usuarios'     => $usuarios,
                'preco'        => $preco,
                'preco_de'     => $precoDe,
                'por_usuario'  => $porUsuario,
                'por_curso'    => $cursos > 0 ? $porUsuario / $cursos : null,
                'economia'     => $economia,
                'desconto_pct' => $precoDe ? (int) round($economia / $precoDe * 100) : 0,
                'selo'         => $p['selo'],
                'destaque'     => (bool) $p['destaque'],
                'chamada'      => $p['chamada'],
                'mensagem'     => self::mensagem('plano', ['plano' => $p['rotulo']]),
                'vs_avulsa'    => $porUsuario < (float) config('assinatura_vitrine.avulsa.preco'),
                'individual'   => $individual,
            ];
        })->values()->all();
    }

    /** Recursos comuns dos planos com os números reais aplicados. */
    public function recursosPlanos(): array
    {
        $r = $this->resumo();

        return array_map(fn ($t) => strtr($t, [
            ':cursos'    => self::numeroMarketing($r['cursos']),
            ':apostilas' => number_format($r['apostilas'], 0, ',', '.'),
        ]), config('assinatura_vitrine.recursos_planos', []));
    }

    /** Dados que a calculadora (vitrine.js) lê: preços e mensagens, nada fixo no JS. */
    public function dadosCalculadora(): array
    {
        return [
            'avulsa'  => (float) config('assinatura_vitrine.avulsa.preco'),
            'pacotes' => collect(config('assinatura_vitrine.planos'))
                ->map(fn ($p) => ['nome' => $p['nome'], 'usuarios' => (int) $p['usuarios'], 'preco' => (float) $p['preco']])
                ->values()->all(),
            'whatsapp'  => self::whatsappBase(),
            'mensagens' => [
                'sob_medida'  => config('assinatura_vitrine.mensagens.sob_medida'),
                'calculadora' => config('assinatura_vitrine.mensagens.calculadora'),
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // Categorias e cursos
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Categorias com contagem de cursos (cards exibidos na página da categoria), ícone,
     * identidade visual e flag de destaque. Inclui a categoria virtual das apostilas.
     */
    public function categorias(): Collection
    {
        $meta    = $this->meta();
        $icones  = config('assinatura_vitrine.icones_categoria', []);
        $virtual = config('assinatura_vitrine.categoria_apostilas');

        $lista = collect($meta['categorias'])
            ->map(fn ($c) => ['slug' => $c['slug'], 'titulo' => $c['titulo'], 'cursos' => (int) $c['paineis']]);

        if ((int) $meta['modular'] > 0) {
            $lista->push(['slug' => $virtual['slug'], 'titulo' => $virtual['titulo'], 'cursos' => (int) $meta['modular']]);
        }

        $destaques = config('assinatura_vitrine.categorias_destaque') ?: $lista->sortByDesc('cursos')->take(6)->pluck('slug')->all();

        return $lista->map(fn ($c) => (object) ($c + [
            'icone'    => $icones[$c['slug']] ?? 'folder',
            'estilo'   => AssinanteCatalogoService::estiloVisual(AssinanteCatalogoService::identidadeVisual($c['titulo'])),
            'destaque' => in_array($c['slug'], $destaques, true),
            'url'      => route('assinatura.categoria', $c['slug']),
        ]))->sortBy('titulo', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function categoria(string $slug): ?object
    {
        return $this->categorias()->firstWhere('slug', $slug);
    }

    /** Carrosséis da home: uma faixa por categoria (mais cursos primeiro), variando as turmas. */
    public function carrosseis(): Collection
    {
        $limite = (int) config('assinatura_vitrine.cursos_por_carrossel', 12);
        $cards  = $this->cards();

        return $this->categorias()
            ->sortByDesc('cursos')
            ->map(function ($cat) use ($cards, $limite) {
                $porTurma = [];
                $itens = $this->cardsDaCategoria($cards, $cat->slug)
                    // No máximo 2 cards por turma: evita uma faixa inteira com a mesma capa.
                    ->filter(function ($c) use (&$porTurma) {
                        $k = $c['classes_id'] ?? 'm' . $c['id'];
                        $porTurma[$k] = ($porTurma[$k] ?? 0) + 1;
                        return $porTurma[$k] <= 2;
                    })
                    ->take($limite)
                    ->values();

                return (object) ['categoria' => $cat, 'itens' => $itens];
            })
            ->filter(fn ($c) => $c->itens->isNotEmpty())
            ->values();
    }

    /** Cursos de uma categoria, com busca e paginação (feitas sobre o catálogo cacheado). */
    public function cursosDaCategoria(object $categoria, string $busca, int $pagina, int $porPagina = 24): LengthAwarePaginator
    {
        $itens = $this->cardsDaCategoria($this->cards(), $categoria->slug);

        if ($busca !== '') {
            $termo = Str::lower(Str::ascii($busca));
            $itens = $itens->filter(fn ($c) => Str::contains(Str::lower(Str::ascii($c['titulo'])), $termo));
        }

        return new LengthAwarePaginator(
            $itens->forPage($pagina, $porPagina)->values(),
            $itens->count(),
            $porPagina,
            $pagina,
            ['path' => route('assinatura.categoria', $categoria->slug), 'query' => array_filter(['busca' => $busca])]
        );
    }

    private function cardsDaCategoria(Collection $cards, string $slug): Collection
    {
        if ($slug === config('assinatura_vitrine.categoria_apostilas.slug')) {
            return $cards->where('tipo', 'modular')->values();
        }

        return $cards->filter(fn ($c) => in_array($slug, $c['categorias'], true))->values();
    }

    /**
     * Catálogo inteiro reduzido ao que a vitrine exibe, com a capa. Uma query para os cards
     * (AssinanteCatalogoService::todos) + uma para as capas das turmas + uma para as das apostilas.
     */
    private function cards(): Collection
    {
        return collect(Cache::remember(self::CACHE_CARDS, self::ttl(), fn () => $this->montarCards()));
    }

    private function montarCards(): array
    {
        $itens = $this->catalogo->todos();

        $base = (string) config('assinatura_vitrine.capa_turma_base');
        $fotos = DB::table('classes')
            ->whereIn('id', $itens->pluck('classes_id')->filter()->unique()->values()->all() ?: [0])
            ->whereNotNull('photo')->where('photo', '<>', '')
            ->pluck('photo', 'id');

        $capasModulares = $this->capasModulares($itens->where('tipo', 'modular')->pluck('id')->all());

        return $itens->map(fn ($i) => [
            'tipo'       => $i->tipo,
            'tipo_label' => $i->tipo_label,
            'id'         => $i->id,
            'classes_id' => $i->classes_id,
            'titulo'     => $i->titulo,
            'aulas'      => $i->aulas,
            'paineis'    => $i->paineis,
            'categoria'  => $i->tipo === 'modular' ? config('assinatura_vitrine.categoria_apostilas.titulo') : $i->categoria,
            'categorias' => array_column($i->categorias, 'slug'),
            'estilo'     => AssinanteCatalogoService::estiloVisual($i->visual),
            'capa'       => $i->tipo === 'modular'
                ? ($capasModulares[$i->id] ?? null)
                : (isset($fotos[$i->classes_id]) ? $base . rawurlencode($fotos[$i->classes_id]) : null),
        ])->all();
    }

    /** modular_course_id => URL da capa pronta mais recente. Sem a tabela, nenhuma capa. */
    private function capasModulares(array $ids): array
    {
        if (! $ids) {
            return [];
        }

        try {
            $base = rtrim((string) config('cursos_modulares.public_base_url'), '/');

            return DB::table('course_covers')
                ->whereIn('modular_course_id', $ids)
                ->where('status', 'pronto')
                ->whereNotNull('image_path')
                ->orderBy('id')
                ->pluck('image_path', 'modular_course_id') // o último (mais recente) vence
                ->map(fn ($p) => $base . '/' . ltrim($p, '/'))
                ->all();
        } catch (\Illuminate\Database\QueryException $e) {
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // Helpers de apresentação (usados também nos componentes Blade)
    // ══════════════════════════════════════════════════════════════════════

    /** "R$ 1.234,00" */
    public static function brl(float|int|null $valor, int $casas = 2): string
    {
        return 'R$ ' . number_format((float) $valor, $casas, ',', '.');
    }

    public static function whatsappBase(): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', (string) config('assinatura_vitrine.whatsapp'));
    }

    /** Mensagem do config com os :placeholders substituídos. */
    public static function mensagem(string $chave = 'padrao', array $vars = []): string
    {
        $texto = (string) config("assinatura_vitrine.mensagens.{$chave}", config('assinatura_vitrine.mensagens.padrao'));
        foreach ($vars as $k => $v) {
            $texto = str_replace(':' . $k, (string) $v, $texto);
        }

        return $texto;
    }

    public static function whatsapp(?string $mensagem = null): string
    {
        return self::whatsappBase() . '?text=' . rawurlencode($mensagem ?? self::mensagem());
    }
}
