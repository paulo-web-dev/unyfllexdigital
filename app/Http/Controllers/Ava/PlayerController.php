<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Panel;
use App\Models\VideoLesson;
use App\Models\ViewsMaterialsMinisserie;
use App\Models\ViewsMinisserie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    public function show(string $slug, ?int $videoId = null)
    {
        $user = Auth::user();

        // A rota 'player.pos' (/dashboard/playerpos/{slug}) é pública por compatibilidade com
        // links antigos; sem usuário logado, manda para o login em vez de estourar 500.
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        // Aceita minissérie (express=1) e curso gravado (express=0); o acesso é
        // controlado logo abaixo por matrícula OU assinatura.
        $classe = Classes::where('slug', $slug)
            ->where('status', 'able')
            ->firstOrFail();

        $matricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $classe->id)
            ->first();

        // Acesso: matrícula na minissérie OU assinatura ativa.
        $assinante = $this->assinaturaVigente($user);
        abort_unless($matricula || $assinante, 403, 'Você não tem acesso a esta minissérie.');

        // Registra o curso acessado (relatórios).
        // Rótulo por tipo de turma (a tela Acessos do admin agrupa por este texto).
        $rotulo = $classe->express ? 'Minissérie: ' : 'Curso gravado: ';
        \App\Models\AccessLog::registrar('curso_view', $user->student_id, $user->id, $rotulo.$classe->title);

        // Carrega panels SEM eager load de relacionamentos
        $panels = Panel::where('classes_id', $classe->id)
            ->where('status', 'able')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $panelIds = $panels->pluck('id');

        // Carrega TODOS os vídeos dos panels de uma vez — query separada totalmente independente
        $todosVideos = VideoLesson::whereIn('panel_id', $panelIds)
            ->where('status', '!=', 'acdvfdegvble')
            ->orderBy('panel_id')
            ->orderBy('id')
            ->get()
            ->groupBy('panel_id');

        // Carrega TODOS os materiais dos panels de uma vez — query separada
        $todosMateriais = DB::table('material_panels')
            ->join('materials', 'material_panels.material_id', '=', 'materials.id')
            ->whereIn('material_panels.panel_id', $panelIds)
            ->where('materials.status', 'able')
            ->select('materials.*', 'material_panels.panel_id as pivot_panel_id')
            ->orderBy('materials.id')
            ->get()
            ->groupBy('pivot_panel_id');

        // Injeta manualmente nos panels
        foreach ($panels as $panel) {
            $panel->setRelation('video_lesson', $todosVideos->get($panel->id, collect()));
            $panel->setRelation('material', $todosMateriais->get($panel->id, collect()));
        }

        // Views já assistidas
        $viewsIds = ViewsMinisserie::where('id_user', $user->id)
            ->where('classes_id', $classe->id)
            ->pluck('video_id')
            ->toArray();

        // Materiais já baixados
        $materiaisVistos = ViewsMaterialsMinisserie::where('id_user', $user->id)
            ->pluck('material_id')
            ->toArray();

        // Monta cápsulas flat
        $capsulas = $panels->flatMap(function ($panel) use ($viewsIds) {
            return $panel->video_lesson->map(fn ($v) => [
                'id' => $v->id,
                'panel_id' => $v->panel_id,
                'feita' => in_array($v->id, $viewsIds),
            ]);
        });

        $totalCnt = $capsulas->count();
        $watchedCnt = $capsulas->where('feita', true)->count();

        $allVideos = $capsulas->values();
        $primeiroNaoAssistido = $allVideos->firstWhere('feita', false);

        $capsulaAtiva = null;
        if ($videoId) {
            $capsulaAtiva = $allVideos->firstWhere('id', $videoId);
        }
        $capsulaAtiva = $capsulaAtiva ?? $primeiroNaoAssistido ?? $allVideos->first();

        $capsula = null;
        if ($capsulaAtiva) {
            $videoAtivo = VideoLesson::find($capsulaAtiva['id']);
            $panelAtivo = Panel::find($capsulaAtiva['panel_id']);

            $pNum = 1;
            $vNum = 1;
            foreach ($panels as $pIdx => $p) {
                foreach ($p->video_lesson as $vIdx => $v) {
                    if ($v->id == $capsulaAtiva['id']) {
                        $pNum = $pIdx + 1;
                        $vNum = $vIdx + 1;
                        break 2;
                    }
                }
            }

            $capsula = [
                'video_id' => $videoAtivo->id,
                'panel_id' => $panelAtivo->id,
                'numero' => $pNum.'.'.$vNum,
                'titulo' => $videoAtivo->titulo ?? '',
                'link' => $videoAtivo->link ?? '',
                'descricao' => $videoAtivo->subtitle ?? $panelAtivo->content ?? '',
                'resumo' => $panelAtivo->content ?? '',
                'trecho' => 'Temporada '.$pNum.': '.$panelAtivo->title,
            ];

            $this->_registrarView($user->id, $classe->id, $capsulaAtiva['panel_id'], $capsulaAtiva['id']);
        }

        $totalVideos = $totalCnt;
        $totalAssistidos = $watchedCnt;
        $progresso = $totalVideos > 0 ? (int) round(($watchedCnt / $totalVideos) * 100) : 0;

        // Assinante: player em unidade de PAINEL (view própria). Aluno matriculado: fluxo original.
        if ($assinante) {
            return $this->viewAssinante($classe, $panels, $capsula, $capsulaAtiva, $viewsIds, $materiaisVistos);
        }

        $layout = 'layouts.app';

        return view('pages.ava.player', compact(
            'classe', 'capsulas', 'capsula', 'capsulaAtiva',
            'panels', 'materiaisVistos',
            'totalCnt', 'watchedCnt',
            'totalVideos', 'totalAssistidos', 'progresso',
            'layout'
        ));
    }

    public function concluir(Request $request, string $slug, int $videoId)
    {
        $user = Auth::user();
        $video = VideoLesson::findOrFail($videoId);
        $panel = Panel::findOrFail($video->panel_id);

        // Acesso: matrícula confirmada OU assinatura vigente (mesma regra do show()).
        $temMatricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $panel->classes_id)
            ->where('status', 'checked')
            ->exists();

        if (! $temMatricula && ! $this->assinaturaVigente($user)) {
            return response()->json(['ok' => false, 'msg' => 'Sem acesso.'], 403);
        }

        $this->_registrarView($user->id, $panel->classes_id, $panel->id, $videoId);

        $totalVideos = VideoLesson::whereIn('panel_id',
            Panel::where('classes_id', $panel->classes_id)->where('status', 'able')->pluck('id')
        )->where('status', 'able')->count();

        $assistidos = ViewsMinisserie::where('id_user', $user->id)
            ->where('classes_id', $panel->classes_id)
            ->distinct('video_id')->count('video_id');

        $progressoPct = $totalVideos > 0 ? (int) round(($assistidos / $totalVideos) * 100) : 0;

        Log::info('[Player] Cápsula concluída', [
            'user_id' => $user->id,
            'video_id' => $videoId,
            'classes_id' => $panel->classes_id,
            'progresso' => $progressoPct.'%',
        ]);

        return response()->json([
            'ok' => true,
            'assistidos' => $assistidos,
            'total' => $totalVideos,
            'progresso' => $progressoPct,
        ]);
    }

    public function registrarMaterial(Request $request, string $slug, int $materialId)
    {
        $user = Auth::user();
        $material = Material::findOrFail($materialId);
        $classe = Classes::where('slug', $slug)->firstOrFail();

        $pertence = $material->panels()
            ->where('panels.classes_id', $classe->id)
            ->exists();

        if (! $pertence) {
            return response()->json(['ok' => false, 'msg' => 'Material não pertence a esta minissérie.'], 403);
        }

        ViewsMaterialsMinisserie::firstOrCreate([
            'id_user' => $user->id,
            'material_id' => $materialId,
        ]);

        Log::info('[Player] Material acessado', [
            'user_id' => $user->id,
            'material_id' => $materialId,
            'classes_id' => $classe->id,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Player do ASSINANTE: contexto é o painel (?painel=ID), não a turma inteira.
     * Não altera queries nem gravações — só recorta o que a view recebe.
     *
     * @param  \Illuminate\Support\Collection  $panels  painéis able da turma (ordem start_time, id), com video_lesson e material injetados
     * @param  array|null  $capsula  cápsula ativa já montada pelo show()
     * @param  array|null  $capsulaAtiva  ['id','panel_id','feita']
     * @param  int[]  $viewsIds  vídeos já vistos pelo usuário nesta turma
     */
    private function viewAssinante(Classes $classe, $panels, ?array $capsula, ?array $capsulaAtiva, array $viewsIds, array $materiaisVistos)
    {
        $panels = $panels->values();
        $painel = null;
        $painelId = (int) request()->query('painel');

        if ($painelId) {
            $painel = $panels->firstWhere('id', $painelId);
        }
        if (! $painel && $capsulaAtiva) {
            $painel = $panels->firstWhere('id', $capsulaAtiva['panel_id']);
        }
        if (! $painel) {
            $painel = $panels->first();
        }
        abort_if(! $painel, 404, 'Este curso ainda não tem aulas disponíveis.');

        // Aulas do painel = vídeos com link (mesma regra do catálogo).
        $aulas = $painel->video_lesson->filter(fn ($v) => filled($v->link))->values();
        abort_if($aulas->isEmpty(), 404, 'Este curso ainda não tem aulas disponíveis.');

        $voltar = \App\Services\AssinanteCatalogoService::voltarSeguro(request()->query('voltar'));
        $query = array_filter(['painel' => $painel->id, 'voltar' => $voltar]);

        // Cápsula ativa fora do painel (ou sem link): reposiciona na primeira aula do painel.
        if (! $capsula || (int) $capsula['panel_id'] !== (int) $painel->id || ! $aulas->contains('id', $capsula['video_id'])) {
            return redirect()->to(route('player.video', [$classe->slug, $aulas->first()->id]).'?'.http_build_query($query));
        }

        $indice = $panels->search(fn ($p) => $p->id === $painel->id);
        $numero = $indice + 1;
        $totalPaineis = $panels->count();

        // Próximo painel da MESMA turma que tenha aula com link (segue em contexto de painel).
        $proximo = null;
        foreach ($panels->slice($indice + 1) as $i => $p) {
            $primeira = $p->video_lesson->first(fn ($v) => filled($v->link));
            if ($primeira) {
                $proximo = [
                    'numero' => $i + 1,
                    'titulo' => $p->title,
                    'url' => route('player.video', [$classe->slug, $primeira->id]).'?'.http_build_query(array_filter(['painel' => $p->id, 'voltar' => $voltar])),
                ];
                break;
            }
        }

        // Cápsula ativa acabou de ser registrada no show(); conta como vista.
        $feitas = array_values(array_unique(array_merge($viewsIds, [(int) $capsula['video_id']])));

        // Nomenclatura de produto (mesma regra do catálogo, só apresentação): minissérie;
        // turma gravada com algum painel de mais de 1 aula = "Curso Livre Aprofundado";
        // turma gravada só de painéis de 1 aula = "Curso Modular".
        $aulasPorPainel = $panels->mapWithKeys(fn ($p) => [$p->id => $p->video_lesson->filter(fn ($v) => filled($v->link))->count()]);
        $tipoTurma = $classe->express
            ? 'Curso Minissérie'
            : ($aulasPorPainel->max() > 1 ? 'Curso Livre Aprofundado' : 'Curso Modular');

        // Índice de painéis da turma (salto direto), com progresso por painel.
        $indice = [];
        foreach ($panels as $i => $p) {
            $vids = $p->video_lesson->filter(fn ($v) => filled($v->link))->values();
            if ($vids->isEmpty()) {
                continue;
            }
            $ids = $vids->pluck('id')->map(fn ($v) => (int) $v);
            $atual = (int) $p->id === (int) $painel->id;
            $tituloP = trim((string) $p->title);
            $indice[] = [
                'id' => (int) $p->id,
                'numero' => $i + 1,
                'titulo' => ($tituloP === '' || $tituloP === '-') ? 'Curso '.($i + 1) : $tituloP,
                'aulas' => $ids->count(),
                'vistas' => $ids->intersect($atual ? $feitas : $viewsIds)->count(),
                'atual' => $atual,
                'url' => route('player.video', [$classe->slug, $vids->first()->id]).'?'.http_build_query(array_filter(['painel' => $p->id, 'voltar' => $voltar])),
            ];
        }

        $materiais = $painel->material->map(fn ($m) => [
            'id' => (int) $m->id,
            'type' => (string) $m->type,
            'name' => $m->name ?: $m->file_name,
            'file' => $m->file_name,
        ])->values()->all();

        $urlVoltar = route('assinante.home').($voltar !== '' ? '?'.$voltar : '');

        // Prova do painel (aba Prova). try/catch: a tabela panel_provas pode ainda
        // não existir (database/panel_provas.sql) — sem ela, sem aba, sem erro.
        $questoesProva = [];
        $tentativasProva = collect();
        $melhorProva = 0;
        try {
            $prova = \App\Models\PanelProva::where('panel_id', $painel->id)->where('status', 'pronto')->first();
            if ($prova) {
                $questoesProva = $prova->questoes();
                $sid = Auth::user()->student_id;
                if ($questoesProva && $sid) {
                    $tentativasProva = \App\Models\PanelProvaAttempt::where('panel_id', $painel->id)
                        ->where('student_id', $sid)
                        ->orderByDesc('id')
                        ->limit(10)
                        ->get();
                    $melhorProva = (int) ($tentativasProva->max('score') ?? 0);
                }
            }
        } catch (\Throwable $e) {
            $questoesProva = [];
        }

        // Certificado — regra 2026-09-02: melhor nota >= 70% na prova.
        //  - minissérie e Curso Modular: certificado do PAINEL (12h), botão na aba Prova;
        //  - Curso Livre Aprofundado: certificado da TURMA (20h) quando TODAS as provas
        //    dos painéis estiverem aprovadas; a aba Prova de cada painel mostra o
        //    progresso "X de Y" e o botão da turma. Sem botão por painel nesse caso.
        // O bloco vive na aba Prova; sem prova não há aba nem certificado possível.
        $certificado = null;
        $certTurma = null;
        if ($questoesProva) {
            $minimo = (int) ceil(count($questoesProva) * \App\Models\PanelCertificate::NOTA_MINIMA);
            if ($tipoTurma === 'Curso Livre Aprofundado') {
                $certTurma = $this->progressoCertificadoTurma($classe, $panels, (int) Auth::user()->student_id) + ['minimo' => $minimo];
            } else {
                $certificado = [
                    'horas' => \App\Models\PanelCertificate::HORAS_PAINEL,
                    'minimo' => $minimo,
                    'aprovado' => $melhorProva >= $minimo,
                    'url' => route('player.certificado', [$classe->slug, $painel->id]),
                ];
            }
        }

        return view('assinante.player', [
            'classe' => $classe,
            'painel' => $painel,
            'numero' => $numero,
            'totalPaineis' => $totalPaineis,
            'aulas' => $aulas,
            'capsula' => $capsula,
            'feitas' => $feitas,
            'proximo' => $proximo,
            'materiais' => $materiais,
            'materiaisVistos' => $materiaisVistos,
            'urlVoltar' => $urlVoltar,
            'queryContexto' => http_build_query($query),
            'questoesProva' => $questoesProva,
            'tentativasProva' => $tentativasProva,
            'melhorProva' => $melhorProva,
            'certificado' => $certificado,
            'certTurma' => $certTurma,
            'tipoTurma' => $tipoTurma,
            'indice' => $indice,
        ]);
    }

    /**
     * Certificado do PAINEL (minissérie e Curso Modular, 12h) — página de impressão/PDF.
     * Regra (2026-09-02): melhor nota >= 70% na prova do painel. Curso Livre
     * Aprofundado não emite por painel (ver certificadoTurma).
     * Mesma autorização da prova (matrícula checked OU assinatura vigente).
     * Registra 1 linha por (aluno, painel) em panel_certificates com token (página
     * pública de validação); sem a tabela, o certificado renderiza sem registrar.
     */
    public function certificado(string $slug, int $painelId)
    {
        $user = Auth::user();
        $painel = Panel::findOrFail($painelId);
        $classe = Classes::where('slug', $slug)->firstOrFail();
        abort_if((int) $painel->classes_id !== (int) $classe->id, 404);

        $temMatricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $painel->classes_id)
            ->where('status', 'checked')
            ->exists();
        abort_unless($temMatricula || $this->assinaturaVigente($user), 403, 'Sem acesso.');

        // Curso Livre Aprofundado: o certificado é da turma inteira (certificadoTurma), não do painel.
        $panels = $this->paineisComAulas($classe);
        abort_if(
            $this->tipoTurma($classe, $panels) === 'Curso Livre Aprofundado',
            403,
            'Neste Curso Livre Aprofundado o certificado é da turma inteira, emitido após a aprovação em todas as provas.'
        );

        try {
            $prova = \App\Models\PanelProva::where('panel_id', $painel->id)->where('status', 'pronto')->first();
        } catch (\Illuminate\Database\QueryException $e) {
            $prova = null;
        }
        abort_unless($prova, 404, 'Este curso não tem prova.');

        $total = count($prova->questoes());
        $minimo = (int) ceil($total * \App\Models\PanelCertificate::NOTA_MINIMA);

        $melhor = \App\Models\PanelProvaAttempt::where('panel_id', $painel->id)
            ->where('student_id', $user->student_id)
            ->max('score');
        abort_unless($total > 0 && (int) $melhor >= $minimo, 403, "Certificado liberado a partir de {$minimo}/{$total} acertos na prova.");

        // Data de conclusão: primeira tentativa que atingiu a nota mínima.
        $concluidoEm = \App\Models\PanelProvaAttempt::where('panel_id', $painel->id)
            ->where('student_id', $user->student_id)
            ->where('score', '>=', $minimo)
            ->min('created_at');

        $student = \App\Models\Student::find($user->student_id);
        $aluno = optional($student)->name ?: $user->name;
        $tituloPainel = trim((string) $painel->title) !== '' && trim((string) $painel->title) !== '-' ? trim($painel->title) : "Painel {$painel->id}";
        $titulo = trim(((string) $classe->title).' — '.$tituloPainel, ' —');
        $horas = \App\Models\PanelCertificate::HORAS_PAINEL;

        $cert = null;
        try {
            $cert = \App\Models\PanelCertificate::firstOrCreate(
                ['student_id' => $user->student_id, 'panel_id' => $painel->id],
                [
                    'token' => \Illuminate\Support\Str::random(40),
                    'aluno' => $aluno,
                    'titulo' => $titulo,
                    'horas' => $horas,
                    'score' => (int) $melhor,
                    'total' => $total,
                    'concluido_em' => substr((string) $concluidoEm, 0, 10),
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Tabela panel_certificates ainda não criada — renderiza sem registrar.
        }

        // Conteúdo programático: títulos das aulas do painel (ordem do player).
        $aulas = VideoLesson::where('panel_id', $painel->id)
            ->whereNotNull('link')->where('link', '<>', '')
            ->orderBy('id')
            ->pluck('titulo')
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->values()
            ->all();

        return view('assinante.certificado', [
            'aluno' => $cert->aluno ?? $aluno,
            'cpf' => $this->formatarCpf(optional($student)->cpf),
            'titulo' => $cert->titulo ?? $titulo,
            'tipoTurma' => (string) $classe->express === '1' ? 'Curso Minissérie' : 'Curso Modular',
            'horas' => (int) ($cert->horas ?? $horas),
            'concluidoEm' => $cert ? $cert->concluido_em : \Illuminate\Support\Carbon::parse($concluidoEm),
            'emitidoEm' => $cert?->created_at ?: now(),
            'numero' => $cert ? str_pad((string) $cert->id, 6, '0', STR_PAD_LEFT) : null,
            'token' => $cert->token ?? null,
            'urlValidacao' => $cert ? route('certificado.validar.token', $cert->token) : null,
            'aulas' => $aulas,
            'urlVoltar' => route('player', $classe->slug).'?painel='.$painel->id,
        ]);
    }

    /**
     * Certificado da TURMA ("Curso Livre Aprofundado", 20h) — página de impressão/PDF.
     * Regra (2026-09-02): nota >= 70% na prova de TODOS os painéis da turma que têm
     * prova pronta. Mesma autorização da prova. Registra 1 linha por (aluno, turma)
     * em class_certificates (database/class_certificates.sql); sem a tabela, renderiza
     * sem registrar (sem token), como o certificado por painel.
     */
    public function certificadoTurma(string $slug)
    {
        $user = Auth::user();
        $classe = Classes::where('slug', $slug)->firstOrFail();

        $temMatricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $classe->id)
            ->where('status', 'checked')
            ->exists();
        abort_unless($temMatricula || $this->assinaturaVigente($user), 403, 'Sem acesso.');

        $panels = $this->paineisComAulas($classe);
        abort_unless($this->tipoTurma($classe, $panels) === 'Curso Livre Aprofundado', 404, 'Este curso emite certificado por painel.');

        $prog = $this->progressoCertificadoTurma($classe, $panels, (int) $user->student_id);
        abort_unless($prog['total'] > 0, 404, 'Esta turma ainda não tem provas.');
        abort_unless(
            $prog['aprovado'],
            403,
            "Certificado liberado após a aprovação em todas as provas da turma ({$prog['aprovadas']} de {$prog['total']} aprovadas)."
        );

        $student = \App\Models\Student::find($user->student_id);
        $aluno = optional($student)->name ?: $user->name;
        $titulo = trim((string) $classe->title);

        $cert = null;
        try {
            $cert = \App\Models\ClassCertificate::firstOrCreate(
                ['student_id' => $user->student_id, 'classes_id' => $classe->id],
                [
                    'token' => \Illuminate\Support\Str::random(40),
                    'aluno' => $aluno,
                    'titulo' => $titulo,
                    'horas' => \App\Models\ClassCertificate::HORAS,
                    'provas_total' => $prog['total'],
                    'provas_aprovadas' => $prog['aprovadas'],
                    'concluido_em' => $prog['concluidoEm']->toDateString(),
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Tabela class_certificates ainda não criada — renderiza sem registrar.
        }

        // Conteúdo programático: os cursos (painéis) da turma.
        $cursos = $panels->values()->map(function ($p, $i) {
            $t = trim((string) $p->title);

            return ($t === '' || $t === '-') ? 'Curso '.($i + 1) : $t;
        })->all();

        return view('assinante.certificado', [
            'aluno' => $cert->aluno ?? $aluno,
            'cpf' => $this->formatarCpf(optional($student)->cpf),
            'titulo' => $cert->titulo ?? $titulo,
            'tipoTurma' => 'Curso Livre Aprofundado',
            'horas' => (int) ($cert->horas ?? \App\Models\ClassCertificate::HORAS),
            'concluidoEm' => $cert ? $cert->concluido_em : $prog['concluidoEm'],
            'emitidoEm' => $cert?->created_at ?: now(),
            'numero' => $cert ? 'T'.str_pad((string) $cert->id, 5, '0', STR_PAD_LEFT) : null,
            'token' => $cert->token ?? null,
            'urlValidacao' => $cert ? route('certificado.validar.token', $cert->token) : null,
            'aulas' => $cursos,
            'urlVoltar' => route('player', $classe->slug),
        ]);
    }

    /** Painéis able da turma que têm ao menos uma aula com link (ordem do player). */
    private function paineisComAulas(Classes $classe)
    {
        return Panel::where('classes_id', $classe->id)
            ->where('status', 'able')
            ->with('video_lesson')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get()
            ->filter(fn ($p) => $p->video_lesson->contains(fn ($v) => filled($v->link)))
            ->values();
    }

    /**
     * Nomenclatura de produto (mesma regra do catálogo, só apresentação): minissérie;
     * turma gravada com algum painel de mais de 1 aula = "Curso Livre Aprofundado";
     * turma gravada só de painéis de 1 aula = "Curso Modular".
     */
    private function tipoTurma(Classes $classe, $panels): string
    {
        if ($classe->express) {
            return 'Curso Minissérie';
        }
        $max = $panels->map(fn ($p) => $p->video_lesson->filter(fn ($v) => filled($v->link))->count())->max();

        return $max > 1 ? 'Curso Livre Aprofundado' : 'Curso Modular';
    }

    /**
     * Progresso do certificado de TURMA: quantas provas dos painéis (com prova pronta)
     * o aluno já aprovou. try/catch: sem as tabelas de prova, total 0.
     *
     * @return array{total:int, aprovadas:int, aprovado:bool, aprovadasIds:int[], pendentes:string[], concluidoEm:\Illuminate\Support\Carbon|null, horas:int, url:string}
     */
    private function progressoCertificadoTurma(Classes $classe, $panels, int $studentId): array
    {
        $ids = $panels->pluck('id')->map(fn ($v) => (int) $v)->all();
        $aprovadasIds = [];
        $pendentes = [];
        $total = 0;
        $concluidoEm = null;

        try {
            $provas = \App\Models\PanelProva::whereIn('panel_id', $ids)->where('status', 'pronto')->get()->keyBy('panel_id');
            $tentativas = \App\Models\PanelProvaAttempt::whereIn('panel_id', $ids)
                ->where('student_id', $studentId)
                ->orderBy('id')
                ->get()
                ->groupBy('panel_id');

            foreach ($panels as $i => $p) {
                $prova = $provas[$p->id] ?? null;
                if (! $prova) {
                    continue;
                }
                $nq = count($prova->questoes());
                if ($nq === 0) {
                    continue;
                }
                $total++;
                $minimo = (int) ceil($nq * \App\Models\PanelCertificate::NOTA_MINIMA);
                $primeiraAprovada = ($tentativas[$p->id] ?? collect())->first(fn ($t) => (int) $t->score >= $minimo);
                $t = trim((string) $p->title);
                $titulo = ($t === '' || $t === '-') ? 'Curso '.($i + 1) : $t;
                if ($primeiraAprovada) {
                    $aprovadasIds[] = (int) $p->id;
                    $data = \Illuminate\Support\Carbon::parse($primeiraAprovada->created_at);
                    $concluidoEm = $concluidoEm && $concluidoEm->gt($data) ? $concluidoEm : $data;
                } else {
                    $pendentes[] = $titulo;
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $total = 0;
        }

        $aprovadas = count($aprovadasIds);

        return [
            'total' => $total,
            'aprovadas' => $aprovadas,
            'aprovado' => $total > 0 && $aprovadas >= $total,
            'aprovadasIds' => $aprovadasIds,
            'pendentes' => $pendentes,
            'concluidoEm' => $concluidoEm,
            'horas' => \App\Models\ClassCertificate::HORAS,
            'url' => route('player.certificado.turma', $classe->slug),
        ];
    }

    /** CPF em 000.000.000-00; vazio se não houver 11 dígitos. */
    private function formatarCpf(?string $cpf): string
    {
        $d = preg_replace('/\D/', '', (string) $cpf);
        if (strlen($d) !== 11) {
            return '';
        }

        return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
    }

    /**
     * Grava uma tentativa da prova do painel (aba Prova do player do assinante).
     * Acesso: matrícula checked na turma OU assinatura vigente (regra do concluir()).
     * O score é RECALCULADO no servidor a partir de `answers` contra o gabarito
     * (diferente do fluxo modular, que confia no score do cliente).
     */
    public function provaResultado(Request $request, string $slug, int $painelId)
    {
        $user = Auth::user();
        $painel = Panel::findOrFail($painelId);
        $classe = Classes::where('slug', $slug)->firstOrFail();

        // A prova precisa pertencer ao painel da turma da URL.
        if ((int) $painel->classes_id !== (int) $classe->id) {
            return response()->json(['ok' => false, 'msg' => 'Prova não pertence a este curso.'], 403);
        }

        $temMatricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $painel->classes_id)
            ->where('status', 'checked')
            ->exists();

        if (! $temMatricula && ! $this->assinaturaVigente($user)) {
            return response()->json(['ok' => false, 'msg' => 'Sem acesso.'], 403);
        }

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['integer'],
        ]);

        try {
            $prova = \App\Models\PanelProva::where('panel_id', $painel->id)->where('status', 'pronto')->first();
            if (! $prova) {
                return response()->json(['ok' => false, 'msg' => 'Este curso não tem prova.'], 404);
            }

            $questoes = $prova->questoes();
            $total = count($questoes);
            $answers = $data['answers'] ?? null;

            if ($total > 0 && is_array($answers) && count($answers) === $total) {
                $score = 0;
                foreach ($questoes as $i => $q) {
                    if ((int) $answers[$i] === (int) ($q['correta'] ?? 0)) {
                        $score++;
                    }
                }
            } else {
                $total = max($total, 1);
                $score = min((int) $data['score'], $total);
            }

            $att = \App\Models\PanelProvaAttempt::create([
                'panel_id' => $painel->id,
                'student_id' => $user->student_id,
                'score' => $score,
                'total' => $total,
                'answers' => is_array($answers) ? json_encode($answers) : null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['ok' => false, 'msg' => 'Prova indisponível no momento.'], 503);
        }

        return response()->json([
            'ok' => true,
            'score' => $att->score,
            'total' => $att->total,
            'percent' => $att->percent(),
        ]);
    }

    /** True se o usuário logado tem assinatura ativa e dentro da validade. */
    private function assinaturaVigente($user): bool
    {
        if (! $user || ! $user->student_id) {
            return false;
        }
        $student = \App\Models\Student::find($user->student_id);

        return $student ? $student->isAssinante() : false;
    }

    private function _registrarView(int $userId, int $classesId, int $panelId, int $videoId): void
    {
        ViewsMinisserie::firstOrCreate([
            'id_user' => $userId,
            'classes_id' => $classesId,
            'panel_id' => $panelId,
            'video_id' => $videoId,
        ]);
    }
}
