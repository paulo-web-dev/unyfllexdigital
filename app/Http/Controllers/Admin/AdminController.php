<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\MaterialPanels;
use App\Models\Panel;
use App\Models\Teacher;
use App\Models\VideoLesson;
use App\Models\ViewsMinisserie;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 
class AdminController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard()
    {
        $hoje    = today();
        $ontem   = today()->subDay();
        $mes     = now()->startOfMonth();
        $mesAnt  = now()->subMonth()->startOfMonth();
        $mesAntF = now()->subMonth()->endOfMonth();
    
        // ── Alunos ───────────────────────────────────────────────────────
        $totalAlunos   = Student::where('minisserie', '1')->count();
        $alunosHoje    = Student::where('minisserie', '1')->whereDate('created_at', $hoje)->count();
        $alunosMes     = Student::where('minisserie', '1')->where('created_at', '>=', $mes)->count();
        $alunosMesAnt  = Student::where('minisserie', '1')->whereBetween('created_at', [$mesAnt, $mesAntF])->count();
    
        // ── Matrículas ────────────────────────────────────────────────────
        $classesIds     = Classes::where('express', '1')->pluck('id');
        $baseEnroll     = Enrollment::whereIn('classes_id', $classesIds);
        $matriculasHoje = (clone $baseEnroll)->whereDate('created_at', $hoje)->count();
        $matriculasOntem= (clone $baseEnroll)->whereDate('created_at', $ontem)->count();
        $matriculasMes  = (clone $baseEnroll)->where('created_at', '>=', $mes)->count();
        $totalMatriculas= (clone $baseEnroll)->count();
    
        // ── Receita ───────────────────────────────────────────────────────
        $receitaMes    = (clone $baseEnroll)->where('status', 'checked')->where('created_at', '>=', $mes)->sum('final_value');
        $receitaMesAnt = (clone $baseEnroll)->where('status', 'checked')->whereBetween('created_at', [$mesAnt, $mesAntF])->sum('final_value');
        $receitaTotal  = (clone $baseEnroll)->where('status', 'checked')->sum('final_value');
        $ticketMedio   = $matriculasMes > 0 ? $receitaMes / $matriculasMes : 0;
        $receitaAnt = (clone $baseEnroll)
        ->where('status', 'checked')
        ->whereBetween('created_at', [$mesAnt, $mesAntF])
        ->sum('final_value');
        $receitaAnt = $receitaMesAnt; // alias para o compact
        // ── Cápsulas assistidas ────────────────────────────────────────────
        $capsulasMes   = ViewsMinisserie::where('created_at', '>=', $mes)->count();
        $usuariosAtivos= ViewsMinisserie::where('updated_at', '>=', now()->subDays(7))->distinct('id_user')->count('id_user');
    
        // ── Progresso médio ────────────────────────────────────────────────
        $totalVideos = DB::table('video_lessons')
            ->join('panels', 'video_lessons.panel_id', '=', 'panels.id')
            ->whereIn('panels.classes_id', $classesIds)
            ->count();
    
        $progressoMedio = 0;
        if ($totalVideos > 0) {
            $mediaViews = DB::table('views_minisseries')
            ->selectRaw('AVG(cnt) as media')
            ->fromSub(
                DB::table('views_minisseries')->selectRaw('id_user, COUNT(DISTINCT video_id) as cnt')->groupBy('id_user'),
                'sub'
            )->value('media') ?? 0;
            $progressoMedio = min(100, (int) round(($mediaViews / max(1, $totalVideos / max(1, $classesIds->count()))) * 100));
        }
    
        // ── Últimas vendas ────────────────────────────────────────────────
        $ultimasVendas = Enrollment::whereIn('classes_id', $classesIds)
            ->where('status', 'checked')
            ->with(['student:id,name,email', 'classes:id,title'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    
        // ── Alertas ───────────────────────────────────────────────────────
        $inadimplentes = (clone $baseEnroll)->where('status', 'not_checked')->count();
        $pendentes     = (clone $baseEnroll)->where('status', 'not_checked')->sum('final_value');
        $novosCursos   = Classes::where('express', '1')->where('status', 'able')->where('created_at', '>=', now()->subDays(30))->count();
    
        // ── Top minisséries por matrículas ────────────────────────────────
        $topCursos = Enrollment::whereIn('classes_id', $classesIds)
            ->selectRaw('classes_id, COUNT(*) as total')
            ->groupBy('classes_id')
            ->orderByDesc('total')
            ->with('classes:id,title,photo')
            ->limit(5)
            ->get();
    
        // ── Matrículas por dia (últimos 14 dias) ──────────────────────────
        $matriculasPorDia = (clone $baseEnroll)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');
    
        // Preenche dias sem matrícula com 0
        $labels = collect();
        $valores = collect();
        for ($i = 13; $i >= 0; $i--) {
            $data = now()->subDays($i)->format('Y-m-d');
            $labels->push(now()->subDays($i)->format('d/m'));
            $valores->push($matriculasPorDia[$data] ?? 0);
        }
    
        $kpis = compact(
            'totalAlunos','alunosHoje','alunosMes','alunosMesAnt',
            'matriculasHoje','matriculasOntem','matriculasMes','totalMatriculas',
            'receitaMes','receitaMesAnt','receitaTotal','receitaAnt','ticketMedio',
            'capsulasMes','usuariosAtivos','progressoMedio',
            'inadimplentes','pendentes','novosCursos'
        );  
    
        return view('pages.admin.dashboard', compact(
            'kpis', 'ultimasVendas', 'topCursos', 'labels', 'valores'
        ));
    }
    
    // ══════════════════════════════════════════════════════════════════════
    // FINANCEIRO
    // ══════════════════════════════════════════════════════════════════════
    public function financeiro(Request $request)
    {
        $mes    = $request->get('mes', now()->format('Y-m'));
        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fim    = Carbon::parse($mes . '-01')->endOfMonth();
    
        $classesIds = Classes::where('express', '1')->pluck('id');
        $base       = Enrollment::whereIn('classes_id', $classesIds);
    
        // ── KPIs do período ───────────────────────────────────────────────
        $receitaBruta    = (clone $base)->where('status', 'checked')->whereBetween('created_at', [$inicio, $fim])->sum('final_value');
        $receitaPendente = (clone $base)->where('status', 'not_checked')->whereBetween('created_at', [$inicio, $fim])->sum('final_value');
        $totalDesconto   = (clone $base)->where('status', 'checked')->whereBetween('created_at', [$inicio, $fim])->sum('discount');
        $qtdVendas       = (clone $base)->where('status', 'checked')->whereBetween('created_at', [$inicio, $fim])->count();
        $qtdCanceladas   = (clone $base)->where('status', 'canceled')->whereBetween('created_at', [$inicio, $fim])->count();
        $ticketMedio     = $qtdVendas > 0 ? $receitaBruta / $qtdVendas : 0;
    
        // Mês anterior para comparação
        $inicioAnt = $inicio->copy()->subMonth()->startOfMonth();
        $fimAnt    = $inicio->copy()->subMonth()->endOfMonth();
            $receitaAnt= (clone $base)->where('status', 'checked')->whereBetween('created_at', [$inicioAnt, $fimAnt])->sum('final_value');
            $varReceita = $receitaAnt > 0 ? (($receitaBruta - $receitaAnt) / $receitaAnt) * 100 : 0;
    
        // ── Por forma de pagamento ─────────────────────────────────────────
        $porForma = (clone $base)
            ->where('status', 'checked')
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('payment_method, COUNT(*) as qtd, SUM(final_value) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    
        // ── Por minissérie ────────────────────────────────────────────────
        $porCurso = (clone $base)
            ->where('status', 'checked')
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('classes_id, COUNT(*) as qtd, SUM(final_value) as total')
            ->groupBy('classes_id')
            ->with('classes:id,title')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    
        // ── Receita por dia (gráfico) ─────────────────────────────────────
        $receitaPorDia = (clone $base)
            ->where('status', 'checked')
            ->whereBetween('created_at', [$inicio, $fim])
            ->selectRaw('DATE(created_at) as dia, SUM(final_value) as total, COUNT(*) as qtd')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');
    
        $diasLabels  = collect();
        $diasReceita = collect();
        $diasQtd     = collect();
        $d = $inicio->copy();
        while ($d->lte($fim)) {
            $key = $d->format('Y-m-d');
            $diasLabels->push($d->format('d'));
            $diasReceita->push((float) ($receitaPorDia[$key]->total ?? 0));
            $diasQtd->push((int)   ($receitaPorDia[$key]->qtd   ?? 0));
            $d->addDay();
        }
    
        // ── Transações recentes ───────────────────────────────────────────
        $transacoes = (clone $base)
            ->whereBetween('created_at', [$inicio, $fim])
            ->with(['student:id,name,email', 'classes:id,title'])
            ->orderByDesc('id')
            ->paginate(20)->withQueryString();
    
        // Meses disponíveis para o seletor (últimos 12)
        $mesesDisponiveis = collect();
        for ($i = 0; $i < 12; $i++) {
            $m = now()->subMonths($i);
            $mesesDisponiveis->push(['value' => $m->format('Y-m'), 'label' => ucfirst($m->isoFormat('MMMM [de] YYYY'))]);
        }
    
        $kpis = compact(
            'receitaBruta','receitaPendente','totalDesconto',
            'qtdVendas','qtdCanceladas','ticketMedio',
            'receitaAnt','varReceita'
        );
    
        return view('pages.admin.financeiro', compact(
            'kpis', 'mes', 'mesesDisponiveis',
            'porForma', 'porCurso', 'transacoes',
            'diasLabels', 'diasReceita', 'diasQtd'
        ));
    }
    
    // ══════════════════════════════════════════════════════════════════════
    // ANALYTICS
    // ══════════════════════════════════════════════════════════════════════
    public function analytics()
    {
        $classesIds = Classes::where('express', '1')->pluck('id');
    
        // ── KPIs gerais ───────────────────────────────────────────────────
        $onlineAgora    = ViewsMinisserie::where('updated_at', '>=', now()->subMinutes(15))->distinct('id_user')->count('id_user');
        $acessosHoje    = ViewsMinisserie::whereDate('created_at', today())->distinct('id_user')->count('id_user');
        $acessosSemana  = ViewsMinisserie::where('created_at', '>=', now()->startOfWeek())->distinct('id_user')->count('id_user');
        $totalViews     = ViewsMinisserie::count();
    
        // Tempo médio estimado (views × 12 min por aluno)
        $viewsPorAluno = DB::table('views_minisseries')
        ->selectRaw('AVG(cnt) as media')
        ->fromSub(
            DB::table('views_minisseries')->selectRaw('id_user, COUNT(*) as cnt')->groupBy('id_user'),
            'sub'
        )->value('media') ?? 0;
        $tempoMedioMin  = (int) round($viewsPorAluno * 12);
        $h = intdiv($tempoMedioMin, 60);
        $m = $tempoMedioMin % 60;
        $tempoMedio = $tempoMedioMin >= 60
            ? ($h.'h '.($m > 0 ? $m.'min' : ''))
            : ($tempoMedioMin.'min');
    
        // Taxa de conclusão média
        $totalVideosDisp = DB::table('video_lessons')
            ->join('panels', 'video_lessons.panel_id', '=', 'panels.id')
            ->whereIn('panels.classes_id', $classesIds)
            ->count();
    
        $taxaConclusao = 0;
        if ($totalVideosDisp > 0) {
            $mediaViews = DB::table('views_minisseries')
            ->selectRaw('AVG(cnt) as media')
            ->fromSub(
                DB::table('views_minisseries')->selectRaw('id_user, COUNT(DISTINCT video_id) as cnt')->groupBy('id_user'),
                'sub'
            )->value('media') ?? 0;
            $taxaConclusao = min(100, (int) round(($mediaViews / ($totalVideosDisp / max(1, $classesIds->count()))) * 100));
        }
    
        // Retenção 30d: alunos que assistiram algo nos últimos 30 dias / total matriculados
        $ativosUlt30  = ViewsMinisserie::where('updated_at', '>=', now()->subDays(30))->distinct('id_user')->count('id_user');
        $totalMatric  = Enrollment::whereIn('classes_id', $classesIds)->distinct('student_id')->count('student_id');
        $retencao30d  = $totalMatric > 0 ? min(100, (int) round(($ativosUlt30 / $totalMatric) * 100)) : 0;
    
        // ── Top cápsulas mais assistidas ─────────────────────────────────
        $topCapsulas = ViewsMinisserie::selectRaw('video_id, COUNT(*) as views')
            ->groupBy('video_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(function ($v) {
                $video = DB::table('video_lessons')
                    ->join('panels', 'video_lessons.panel_id', '=', 'panels.id')
                    ->join('classes', 'panels.classes_id', '=', 'classes.id')
                    ->where('video_lessons.id', $v->video_id)
                    ->select('video_lessons.titulo', 'panels.title as panel', 'classes.title as classe')
                    ->first();
                return (object) [
                    'video_id' => $v->video_id,
                    'views'    => $v->views,
                    'titulo'   => $video?->titulo ?? 'Sem título',
                    'panel'    => $video?->panel   ?? '—',
                    'classe'   => $video?->classe  ?? '—',
                ];
            });
    
        // ── Top minisséries por engajamento ───────────────────────────────
        $topMinisseries = ViewsMinisserie::selectRaw('classes_id, COUNT(*) as views, COUNT(DISTINCT id_user) as alunos')
            ->groupBy('classes_id')
            ->orderByDesc('views')
            ->limit(8)
            ->get()
            ->map(function ($v) {
                $classe = Classes::find($v->classes_id);
                return (object) [
                    'titulo'  => $classe?->title ?? "Curso #{$v->classes_id}",
                    'views'   => $v->views,
                    'alunos'  => $v->alunos,
                ];
            });
    
        // ── Acessos por dia (últimos 30 dias) ─────────────────────────────
        $acessosPorDia = ViewsMinisserie::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as dia, COUNT(DISTINCT id_user) as alunos, COUNT(*) as views')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');
    
        $grafLabels  = collect();
        $grafAlunos  = collect();
        $grafViews   = collect();
        for ($i = 29; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $grafLabels->push(now()->subDays($i)->format('d/m'));
            $grafAlunos->push((int) ($acessosPorDia[$key]->alunos ?? 0));
            $grafViews->push((int)  ($acessosPorDia[$key]->views  ?? 0));
        }
    
        // ── Alunos mais ativos (últimos 30 dias) ──────────────────────────
        $alunosAtivos = ViewsMinisserie::where('updated_at', '>=', now()->subDays(30))
            ->selectRaw('id_user, COUNT(DISTINCT video_id) as capsulas, MAX(updated_at) as ultimo')
            ->groupBy('id_user')
            ->orderByDesc('capsulas')
            ->limit(8)
            ->get()
            ->map(function ($v) {
                $user = User::find($v->id_user);
                return (object) [
                    'nome'     => $user?->name    ?? "User #{$v->id_user}",
                    'email'    => $user?->email   ?? '',
                    'capsulas' => $v->capsulas,
                    'ultimo'   => Carbon::parse($v->ultimo)->diffForHumans(),
                ];
            });
    
        $kpis = compact(
            'onlineAgora','acessosHoje','acessosSemana','totalViews',
            'tempoMedio','taxaConclusao','retencao30d','ativosUlt30'
        );
    
        return view('pages.admin.analytics', compact(
            'kpis', 'topCapsulas', 'topMinisseries', 'alunosAtivos',
            'grafLabels', 'grafAlunos', 'grafViews'
        ));
    }
    public function adminBusca(Request $request)
{
    $q = trim($request->get('q', ''));

    if (strlen($q) < 2) {
        return response()->json(['results' => []]);
    }

    $results = [];

    // ── Alunos ─────────────────────────────────────────────────────────
    $alunos = User::whereNotNull('student_id')
        ->where(fn ($query) => $query
            ->where('name',  'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
        )
        ->select('id', 'name', 'email', 'funcao')
        ->limit(4)
        ->get();

    foreach ($alunos as $a) {
        $results[] = [
            'tipo'    => 'aluno',
            'titulo'  => $a->name,
            'sub'     => $a->email,
            'meta'    => $a->funcao,
            'url'     => route('admin.alunos.edit', $a->id),
            'icone'   => 'user',
        ];
    }

    // ── Matrículas ─────────────────────────────────────────────────────
    $classesIds = Classes::where('express', '1')->pluck('id');

    $matriculas = Enrollment::whereIn('classes_id', $classesIds)
        ->whereHas('aluno', fn ($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
        ->with(['aluno:id,name,email,student_id', 'classes:id,title'])
        ->limit(3)
        ->get();

    foreach ($matriculas as $m) {
        $results[] = [
            'tipo'   => 'matricula',
            'titulo' => optional($m->aluno)->name ?? "Matrícula #{$m->id}",
            'sub'    => optional($m->classes)->title ?? '—',
            'meta'   => ucfirst(str_replace('_', ' ', $m->status)),
            'url'    => route('admin.matriculas.edit', $m->id),
            'icone'  => 'file-text',
        ];
    }

    // ── Minisséries ────────────────────────────────────────────────────
    $cursos = Classes::where('express', '1')
        ->where(fn ($query) => $query
            ->where('title',    'like', "%{$q}%")
            ->orWhere('subtitle', 'like', "%{$q}%")
        )
        ->select('id', 'title', 'subtitle', 'status')
        ->limit(3)
        ->get();

    foreach ($cursos as $c) {
        $results[] = [
            'tipo'   => 'curso',
            'titulo' => $c->title,
            'sub'    => $c->subtitle,
            'meta'   => $c->status === 'able' ? 'Publicada' : 'Inativa',
            'url'    => route('admin.cursos.show', $c->id),
            'icone'  => 'film',
        ];
    }

    return response()->json([
        'results' => $results,
        'query'   => $q,
    ]);
}

    // ══════════════════════════════════════════════════════════════════════
    // ALUNOS
    // ══════════════════════════════════════════════════════════════════════
    public function alunos(Request $request)
    {
        $busca = trim($request->get('q', ''));
        $ordem = $request->get('ordem', 'recentes');

        $alunosMinisserieIds = Student::where('minisserie', '1')->pluck('id');

        $query = User::whereIn('student_id', $alunosMinisserieIds)
            ->select(['id','name','email','cpf','telefone','setor','funcao','avatar','power','student_id','corporativo_id','created_at','updated_at']);

        if ($busca) {
            $query->where(fn ($q) => $q
                ->where('name',  'like', "%{$busca}%")
                ->orWhere('email', 'like', "%{$busca}%")
                ->orWhere('cpf',   'like', "%{$busca}%")
            );
        }

        $query->when($ordem === 'nome', fn ($q) => $q->orderBy('name'), fn ($q) => $q->orderByDesc('created_at'));
        $alunos = $query->paginate(25)->withQueryString();

        $totalAlunos     = User::whereIn('student_id', $alunosMinisserieIds)->count();
        $novosHoje       = User::whereIn('student_id', $alunosMinisserieIds)->whereDate('created_at', today())->count();
        $novosSemana     = User::whereIn('student_id', $alunosMinisserieIds)->whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $totalMatriculas = Enrollment::where('modality', 'minisserie')->count();
        $totalViews      = ViewsMinisserie::distinct('id_user')->count('id_user');

        $kpis = compact('totalAlunos','novosHoje','novosSemana','totalMatriculas') + ['alunosAtivos' => $totalViews];

        $userIds    = $alunos->pluck('student_id')->filter()->values();
        $userIdsAll = $alunos->pluck('id')->filter()->values();

        $matriculasPorAluno = Enrollment::where('modality', 'minisserie')
            ->whereIn('student_id', $userIds)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')->pluck('total', 'student_id');

        $capsulasPorUser = ViewsMinisserie::whereIn('id_user', $userIdsAll)
            ->selectRaw('id_user, COUNT(DISTINCT video_id) as total')
            ->groupBy('id_user')->pluck('total', 'id_user');

        return view('pages.admin.alunos', compact('alunos','kpis','busca','ordem','matriculasPorAluno','capsulasPorUser'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATRÍCULAS
    // ══════════════════════════════════════════════════════════════════════
    public function matriculas(Request $request)
    {
        $classesExpressIds = Classes::where('express','1')->where('status','able')->pluck('id');
        $busca  = trim($request->get('q', ''));
        $status = $request->get('status', 'todas');
        $forma  = $request->get('forma', '');

        $query = Enrollment::whereIn('classes_id', $classesExpressIds)
            ->with(['aluno:id,name,email,student_id','classes:id,title,slug,photo'])
            ->orderByDesc('id');

        if ($status !== 'todas') $query->where('status', $status);
        if ($forma)              $query->where('payment_method', 'like', "%{$forma}%");
        if ($busca) {
            $userIds = User::where('name','like',"%{$busca}%")->orWhere('email','like',"%{$busca}%")->pluck('student_id')->filter();
            $query->where(fn ($q) => $q->whereIn('student_id', $userIds)->orWhereHas('classes', fn ($c) => $c->where('title','like',"%{$busca}%")));
        }

        $matriculas = $query->paginate(25)->withQueryString();
        $base       = Enrollment::whereIn('classes_id', $classesExpressIds);

        $kpis = [
            'totalGeral'     => (clone $base)->count(),
            'totalHoje'      => (clone $base)->whereDate('created_at', today())->count(),
            'totalChecked'   => (clone $base)->where('status','checked')->count(),
            'totalPending'   => (clone $base)->where('status','not_checked')->count(),
            'totalSched'     => (clone $base)->where('status','scheduled_billing')->count(),
            'receitaTotal'   => (clone $base)->where('status','checked')->sum('final_value'),
            'receitaPending' => (clone $base)->where('status','not_checked')->sum('final_value'),
        ];

        return view('pages.admin.matriculas', compact('matriculas','kpis','busca','status','forma'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — listagem
    // ══════════════════════════════════════════════════════════════════════
    public function cursos()
    {
        $classes = Classes::where('express','1')->where('status','able')
            ->with(['panels' => fn ($q) => $q->where('status','able')->with('video_lesson','material')])
            ->orderByDesc('id')->get();

        $totalMinisseries = $classes->count();
        $totalCapsulas    = $classes->sum(fn ($c) => $c->panels->flatMap(fn ($p) => $p->video_lesson)->count());
        $totalMateriais   = $classes->sum(fn ($c) => $c->panels->flatMap(fn ($p) => $p->material)->count());
        $totalMatriculas  = Enrollment::where('modality','minisserie')->count();

        $progressoMedio = 0;
        if ($totalMinisseries > 0) {
            $progressos = $classes->map(function ($classe) {
                $ids   = $classe->panels->flatMap(fn ($p) => $p->video_lesson)->pluck('id');
                $total = $ids->count();
                if (!$total) return null;
                $vistos = ViewsMinisserie::where('classes_id',$classe->id)->whereIn('video_id',$ids)->distinct('video_id')->count('video_id');
                return ($vistos / $total) * 100;
            })->filter()->values();
            $progressoMedio = $progressos->isNotEmpty() ? (int) round($progressos->average()) : 0;
        }

        $kpis = compact('totalMinisseries','totalCapsulas','totalMateriais','progressoMedio','totalMatriculas');
        return view('pages.admin.cursos', compact('classes','kpis'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — detalhe
    // ══════════════════════════════════════════════════════════════════════
    public function cursoShow(int $id)
    {
        $classe = Classes::with(['panels' => fn ($q) => $q->orderBy('start_time')->orderByRaw("CAST(horario AS TIME)")->with('video_lesson','material','teachers')])->findOrFail($id);
        $panels         = $classe->panels;
        $todosVideos    = $panels->flatMap(fn ($p) => $p->video_lesson);
        $todosMateriais = $panels->flatMap(fn ($p) => $p->material);
        $totalVideos    = $todosVideos->count();
        $dur            = $totalVideos * 12;
        $horas          = intdiv($dur, 60);
        $minutos        = $dur % 60;
        $duracao        = $dur > 0 ? ($horas > 0 ? $horas.'h '.($minutos > 0 ? $minutos.'min' : '') : $minutos.'min') : '—';

        $totalMatriculas   = Enrollment::where('classes_id',$id)->where('modality','minisserie')->count();
        $matriculasChecked = Enrollment::where('classes_id',$id)->where('status','checked')->count();

        $progressoMedio = 0;
        if ($totalVideos > 0 && $totalMatriculas > 0) {
            $vpu = ViewsMinisserie::where('classes_id',$id)->whereIn('video_id',$todosVideos->pluck('id'))
                ->selectRaw('id_user, COUNT(DISTINCT video_id) as vistos')->groupBy('id_user')->get();
            if ($vpu->isNotEmpty()) $progressoMedio = (int) round($vpu->avg(fn ($v) => ($v->vistos / $totalVideos) * 100));
        }

        $kpis = [
            'totalPanels'       => $panels->count(),
            'totalVideos'       => $totalVideos,
            'totalMateriais'    => $todosMateriais->count(),
            'totalPodcasts'     => $todosMateriais->where('type','PODCAST')->count(),
            'totalPdfs'         => $todosMateriais->where('type','PDF')->count(),
            'totalMatriculas'   => $totalMatriculas,
            'matriculasChecked' => $matriculasChecked,
            'progressoMedio'    => $progressoMedio,
            'duracao'           => $duracao,
        ];

        return view('pages.admin.curso-show', compact('classe','panels','kpis'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — criar (GET)
    // ══════════════════════════════════════════════════════════════════════
    public function cursoCreate()
    {
        return view('pages.admin.curso-create');
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — salvar novo (POST)
    // ══════════════════════════════════════════════════════════════════════
    public function cursoStore(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required','string','max:255'],
            'subtitle'   => ['nullable','string','max:255'],
            'slug'       => ['required','string','max:255','unique:classes,slug'],
            'workload'   => ['nullable','string','max:100'],
            'status'     => ['required','in:able,disabled'],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date'],
            'photo'      => ['nullable','string','max:255'],
        ]);

        $data['slug']    = Str::slug($data['slug']);
        $data['express'] = 1;
        $data['unyflex'] = 1;
        $data['type'] = "Minisserie";
        $data['id_polo'] = 2;
        $data['polo'] ="Curitiba - PR";
        $data['incompany'] =0;
        $data['brinde_modular'] =1;
        $data['course_id'] = 1013;

        $classe = Classes::create($data);

        return redirect()
            ->route('admin.cursos.show', $classe->id)
            ->with('success', 'Minissérie criada com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — editar (GET)
    // ══════════════════════════════════════════════════════════════════════
    public function cursoEdit(int $id)
    {
        $classe = Classes::with(['panels' => fn ($q) => $q->orderBy('start_time')->orderByRaw("CAST(horario AS TIME)")->with('video_lesson','material')])->findOrFail($id);
        return view('pages.admin.curso-edit', compact('classe'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — salvar edição (PUT)
    // ══════════════════════════════════════════════════════════════════════
    public function cursoUpdate(Request $request, int $id)
    {
        $request->validate([
            'title'      => ['required','string','max:255'],
            'subtitle'   => ['nullable','string','max:255'],
            'slug'       => ['required','string','max:255'],
            'workload'   => ['nullable','string','max:100'],
            'status'     => ['required','in:able,disabled'],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date'],
            'photo'      => ['nullable','string','max:255'],
        ]);

        $classe = Classes::findOrFail($id);
        $classe->fill($request->only(['title','subtitle','workload','status','start_date','end_date','photo']));
        $classe->slug = Str::slug($request->slug);
        $classe->save();

        return redirect()->route('admin.cursos.show', $id)->with('success', 'Minissérie atualizada com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // PANELS — editar (GET)
    // ══════════════════════════════════════════════════════════════════════
    // public function panelEdit(int $id)
    // {
    //     $panel = Panel::with('video_lesson','material','classes')->findOrFail($id);
    //     return view('pages.admin.panel-edit', compact('panel'));
    // }

    // ══════════════════════════════════════════════════════════════════════
    // PANELS — salvar (PUT)
    // ══════════════════════════════════════════════════════════════════════
    public function panelUpdate2(Request $request, int $id)
    {
        $request->validate([
            'title'               => ['required','string','max:255'],
            'subtitle'            => ['nullable','string','max:255'],
            'content'             => ['nullable','string'],
            'status'              => ['required','in:able,disabled'],
            'start_time'          => ['nullable','date'],
            'horario'             => ['nullable','string','max:10'],
            'videos'              => ['nullable','array'],
            'videos.*.titulo'         => ['nullable','string','max:255'],
            'videos.*.link'           => ['nullable','string','max:500'],
            'videos.*.tasting_link'   => ['nullable','string','max:500'],
            'videos.*.subtitle'       => ['nullable','string'],
            'videos.*.status'         => ['nullable','in:able,disabled'],
        ]);

        $panel = Panel::findOrFail($id);
        $panel->fill($request->only(['title','subtitle','content','status','start_time','horario']));
        $panel->save();

        if ($request->has('videos')) {
            foreach ($request->videos as $videoId => $dados) {
                $video = VideoLesson::find($videoId);
                if (!$video) continue;
                $video->fill(array_filter($dados, fn ($v) => $v !== null));
                $video->save();
            }
        }

        return redirect()->route('admin.cursos.show', $panel->classes_id)->with('success', 'Temporada atualizada com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // VÍDEOS — salvar individual (PUT/AJAX)
    // ══════════════════════════════════════════════════════════════════════
    public function videoUpdate(Request $request, int $id)
    {
        $video = VideoLesson::findOrFail($id);
        $video->fill($request->only(['titulo','link','tasting_link','subtitle','status']));
        $video->save();
        return response()->json(['ok' => true, 'video' => $video]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — listagem
    // ══════════════════════════════════════════════════════════════════════
    public function materiais(Request $request)
    {
        $busca = trim($request->get('q', ''));
        $tipo  = $request->get('tipo', '');

        $query = Material::query()->orderByDesc('id');
        if ($busca) $query->where(fn ($q) => $q->where('name','like',"%{$busca}%")->orWhere('file_name','like',"%{$busca}%"));
        if ($tipo)  $query->where('type', $tipo);

        $materiais = $query->paginate(30)->withQueryString();

        $kpis = [
            'total'    => Material::count(),
            'pdfs'     => Material::where('type','PDF')->count(),
            'podcasts' => Material::where('type','PODCAST')->count(),
            'ativos'   => Material::where('status','able')->count(),
        ];

        return view('pages.admin.materiais', compact('materiais','kpis','busca','tipo'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — criar (GET)
    // ══════════════════════════════════════════════════════════════════════
    public function materialCreate()
    {
        // Panels para vincular (só de minisséries express)
        $classesIds = Classes::where('express','1')->where('status','able')->pluck('id');
        $panels     = Panel::whereIn('classes_id', $classesIds)
            ->with('classes:id,title')
            ->orderBy('classes_id')
            ->get();

        return view('pages.admin.material-form', compact('panels'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — salvar novo (POST)
    // ══════════════════════════════════════════════════════════════════════
    public function materialStore(Request $request)
    {
        $request->validate([
            'name'      => ['required','string','max:255'],
            'file_name' => ['required','string','max:255'],
            'type'      => ['required','in:PDF,PODCAST'],
            'status'    => ['required','in:able,disabled'],
            'panels'    => ['nullable','array'],
            'panels.*'  => ['integer','exists:panels,id'],
        ]);

        $material = Material::create($request->only(['name','file_name','type','status']));

        // Vincula aos painéis selecionados
        if ($request->panels) {
            foreach ($request->panels as $panelId) {
                MaterialPanels::firstOrCreate([
                    'material_id' => $material->id,
                    'panel_id'    => $panelId,
                ]);
            }
        }

        return redirect()->route('admin.materiais')->with('success', 'Material criado e vinculado com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — editar (GET)
    // ══════════════════════════════════════════════════════════════════════
    public function materialEdit(int $id)
    {
        $material = Material::findOrFail($id);

        // Panels vinculados atualmente
        $panelsVinculados = MaterialPanels::where('material_id', $id)->pluck('panel_id');

        // Todos os panels de minisséries express
        $classesIds = Classes::where('express','1')->where('status','able')->pluck('id');
        $panels     = Panel::whereIn('classes_id', $classesIds)
            ->with('classes:id,title')
            ->orderBy('classes_id')
            ->get();

        return view('pages.admin.material-form', compact('material','panels','panelsVinculados'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — salvar edição (PUT)
    // ══════════════════════════════════════════════════════════════════════
    public function materialUpdate(Request $request, int $id)
    {
        $request->validate([
            'name'      => ['required','string','max:255'],
            'file_name' => ['required','string','max:255'],
            'type'      => ['required','in:PDF,PODCAST'],
            'status'    => ['required','in:able,disabled'],
            'panels'    => ['nullable','array'],
            'panels.*'  => ['integer','exists:panels,id'],
        ]);

        $material = Material::findOrFail($id);
        $material->fill($request->only(['name','file_name','type','status']));
        $material->save();

        // Sincroniza vínculos: remove todos e recria
        MaterialPanels::where('material_id', $id)->delete();
        if ($request->panels) {
            foreach ($request->panels as $panelId) {
                MaterialPanels::create(['material_id' => $id, 'panel_id' => $panelId]);
            }
        }

        return redirect()->route('admin.materiais')->with('success', 'Material atualizado com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — excluir (DELETE)
    // ══════════════════════════════════════════════════════════════════════
    public function materialDestroy(int $id)
    {
        MaterialPanels::where('material_id', $id)->delete();
        Material::findOrFail($id)->delete();
        return redirect()->route('admin.materiais')->with('success', 'Material removido.');
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATERIAIS — vincular/desvincular (AJAX)
    // ══════════════════════════════════════════════════════════════════════
    public function materialVincular(int $materialId, int $panelId)
    {
        MaterialPanels::firstOrCreate(['material_id' => $materialId, 'panel_id' => $panelId]);
        return response()->json(['ok' => true]);
    }

    public function materialDesvincular(int $materialId, int $panelId)
    {
        MaterialPanels::where('material_id', $materialId)->where('panel_id', $panelId)->delete();
        return response()->json(['ok' => true]);
    }

    //PANELS
    
// ══════════════════════════════════════════════════════════════════════
// PANELS — criar (GET)
// ══════════════════════════════════════════════════════════════════════
public function panelCreate(int $classeId)
{
    $classe   = Classes::findOrFail($classeId);
    $teachers = Teacher::where('status', 'able')->orderBy('name')->get();

    return view('pages.admin.panel-create', compact('classe', 'teachers'));
}

// ══════════════════════════════════════════════════════════════════════
// PANELS — salvar novo (POST)
// ══════════════════════════════════════════════════════════════════════
public function panelStore(Request $request, int $classeId)
{
    $request->validate([
        'title'      => ['required', 'string', 'max:255'],
        'subtitle'   => ['nullable', 'string', 'max:255'],
        'content'    => ['nullable', 'string'],
        'status'     => ['required', 'in:able,disabled'],
        'start_time' => ['nullable', 'date'],
        'horario'    => ['nullable', 'string', 'max:10'],
        'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],

        // Vídeos
        'videos'                  => ['nullable', 'array'],
        'videos.*.titulo'         => ['nullable', 'string', 'max:255'],
        'videos.*.link'           => ['nullable', 'string', 'max:500'],
        'videos.*.tasting_link'   => ['nullable', 'string', 'max:500'],
        'videos.*.bkp_link'       => ['nullable', 'string', 'max:500'],
        'videos.*.source'         => ['nullable', 'in:youtube,vimeo'],
        'videos.*.subtitle'       => ['nullable', 'string'],
        'videos.*.status'         => ['nullable', 'in:able,disabled'],
    ]);

    Classes::findOrFail($classeId); // garante que o curso existe

    // Cria o panel
    $panel = Panel::create([
        'classes_id'  => $classeId,
        'teacher_id'  => $request->teacher_id,
        'title'       => $request->title,
        'subtitle'    => $request->subtitle,
        'content'     => $request->content,
        'status'      => $request->status,
        'start_time'  => $request->start_time,
        'horario'     => $request->horario,
        'confirmation'=> '0',
        'id_antigo'=> '0',
    ]);

    // Cria os vídeos vinculados
    if ($request->has('videos')) {
        foreach ($request->videos as $dados) {
            // Ignora linhas completamente vazias
            if (empty(array_filter($dados))) continue;

            VideoLesson::create([
                'panel_id'    => $panel->id,
                'titulo'      => $dados['titulo']       ?? null,
                'link'        => $dados['link']         ?? null,
                'tasting_link'=> $dados['tasting_link'] ?? null,
                'bkp_link'    => $dados['bkp_link']     ?? null,
                'source'      => $dados['source']       ?? 'youtube',
                'subtitle'    => $dados['subtitle']     ?? null,
                'status'      => $dados['status']       ?? 'able',
            ]);
        }
    }

    return redirect()
        ->route('admin.cursos.edit', $classeId)
        ->with('success', "Temporada \"{$panel->title}\" criada com sucesso!");
}

// ══════════════════════════════════════════════════════════════════════
// PANELS — editar (GET)  — já existia, só garantindo que carrega teachers
// ══════════════════════════════════════════════════════════════════════
public function panelEdit(int $id)
{
    $panel    = Panel::with('video_lesson', 'material', 'classes')->findOrFail($id);
    $teachers = Teacher::where('status', 'able')->orderBy('name')->get();

    return view('pages.admin.panel-edit', compact('panel', 'teachers'));
}

// ══════════════════════════════════════════════════════════════════════
// PANELS — salvar edição (PUT) — já existia, adiciona teacher_id
// ══════════════════════════════════════════════════════════════════════
public function panelUpdate(Request $request, int $id)
{
    $request->validate([
        'title'               => ['required', 'string', 'max:255'],
        'subtitle'            => ['nullable', 'string', 'max:255'],
        'content'             => ['nullable', 'string'],
        'status'              => ['required', 'in:able,disabled'],
        'start_time'          => ['nullable', 'date'],
        'horario'             => ['nullable', 'string', 'max:10'],
        'teacher_id'          => ['nullable', 'integer', 'exists:teachers,id'],
        'videos'              => ['nullable', 'array'],
        'videos.*.titulo'         => ['nullable', 'string', 'max:255'],
        'videos.*.link'           => ['nullable', 'string', 'max:500'],
        'videos.*.tasting_link'   => ['nullable', 'string', 'max:500'],
        'videos.*.bkp_link'       => ['nullable', 'string', 'max:500'],
        'videos.*.source'         => ['nullable', 'in:youtube,vimeo'],
        'videos.*.subtitle'       => ['nullable', 'string'],
        'videos.*.status'         => ['nullable', 'in:able,disabled'],
    ]);

    $panel = Panel::findOrFail($id);
    $panel->fill($request->only([
        'title', 'subtitle', 'content', 'status',
        'start_time', 'horario', 'teacher_id',
    ]));
    $panel->save();

    // Atualiza vídeos existentes
    if ($request->has('videos')) {
        foreach ($request->videos as $videoId => $dados) {
            $video = VideoLesson::find($videoId);
            if (!$video) continue;
            $video->fill(array_filter($dados, fn ($v) => $v !== null));
            $video->save();
        }
    }

    return redirect()
        ->route('admin.cursos.show', $panel->classes_id)
        ->with('success', 'Temporada atualizada com sucesso!');
}


public function alunoCreate()
{
    return view('pages.admin.aluno-create');
}

// ══════════════════════════════════════════════════════════════════════
// ALUNOS — salvar novo (POST)
// Cria Student + User vinculado automaticamente
// ══════════════════════════════════════════════════════════════════════
public function alunoStore(Request $request)
{
    $request->validate([
        'name'       => ['required', 'string', 'max:255'],
        'email'      => ['required', 'email', 'max:255', 'unique:students,email'],
        'cpf'        => ['nullable', 'string', 'max:255'],
        'phone'      => ['nullable', 'string', 'max:255'],
        'cargo'      => ['nullable', 'string', 'max:255'],
        'entidade'   => ['nullable', 'string', 'max:255'],
        'city'       => ['nullable', 'string', 'max:255'],
        'state'      => ['nullable', 'string', 'max:2'],
        'nascimento' => ['nullable', 'date'],
        'status'     => ['required', 'in:able,disabled'],
        'password'   => ['required', 'string', 'min:6'],
    ], [
        'email.unique'    => 'Este e-mail já está cadastrado.',
        'password.min'    => 'A senha deve ter no mínimo 6 caracteres.',
    ]);

    // 1. Cria o Student
    $student = Student::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'cpf'        => $request->cpf,
        'phone'      => $request->phone,
        'cargo'      => $request->cargo,
        'entidade'   => $request->entidade,
        'city'       => $request->city,
        'state'      => $request->state,
        'nascimento' => $request->nascimento,
        'status'     => $request->status,
        'minisserie' => '1', // flag para aparecer no AVA
        'password'   => \Illuminate\Support\Facades\Hash::make($request->password),
    ]);

    // 2. Cria o User vinculado ao Student
    $user = User::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'password'   => \Illuminate\Support\Facades\Hash::make($request->password),
        'student_id' => $student->id,
        'funcao'     => $request->cargo,
        'setor'      => $request->entidade,
        'power'      => 1, // aluno padrão
        'teacher_id'      => 1, 
        'embaixador_id'      => 0, 
        'corporativo_id'      => 1, 
    ]);

    // 3. Se já veio com matrícula, redireciona para matricular
    if ($request->boolean('matricular_agora')) {
        return redirect()
            ->route('admin.matriculas.create', ['student_id' => $student->id])
            ->with('success', "Aluno \"{$student->name}\" criado! Agora matricule-o.");
    }

    return redirect()
        ->route('admin.alunos')
        ->with('success', "Aluno \"{$student->name}\" criado com sucesso!");
}

// ══════════════════════════════════════════════════════════════════════
// ALUNOS — editar (GET)
// ══════════════════════════════════════════════════════════════════════
public function alunoEdit(int $id)
{
    // Busca pelo ID do User
    $user    = User::findOrFail($id);
    $student = Student::find($user->student_id);

    return view('pages.admin.aluno-edit', compact('user', 'student'));
}

// ══════════════════════════════════════════════════════════════════════
// ALUNOS — salvar edição (PUT)
// ══════════════════════════════════════════════════════════════════════
public function alunoUpdate(Request $request, int $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255'],
        'funcao'   => ['nullable', 'string', 'max:255'],
        'setor'    => ['nullable', 'string', 'max:255'],
        'telefone' => ['nullable', 'string', 'max:255'],
        'power'    => ['nullable', 'integer', 'min:0'],
        'password' => ['nullable', 'string', 'min:6'],
    ]);

    $user->name     = $request->name;
    $user->email    = $request->email;
    $user->funcao   = $request->funcao;
    $user->setor    = $request->setor;
    $user->telefone = $request->telefone;
    $user->power    = $request->power ?? $user->power;

    if ($request->filled('password')) {
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
    }
    $user->save();

    // Atualiza também o Student se existir
    if ($user->student_id) {
        $student = Student::find($user->student_id);
        if ($student) {
            $student->name    = $request->name;
            $student->email   = $request->email;
            $student->cargo   = $request->funcao;
            $student->entidade= $request->setor;
            $student->phone   = $request->telefone;
            if ($request->filled('password')) {
                $student->password = \Illuminate\Support\Facades\Hash::make($request->password);
            }
            $student->save();
        }
    }

    return redirect()->route('admin.alunos')->with('success', 'Aluno atualizado com sucesso!');
}

// ══════════════════════════════════════════════════════════════════════
// MATRÍCULAS — criar (GET)
// ══════════════════════════════════════════════════════════════════════
public function matriculaCreate(Request $request)
{
    // Pode vir com student_id pré-selecionado (ex: após criar aluno)
    $studentId = $request->get('student_id');

    $student = $studentId ? Student::find($studentId) : null;

    // Minisséries disponíveis para matrícula
    $classes = Classes::where('express', '1')
        ->where('status', 'able')
        ->orderBy('title')
        ->get();

    return view('pages.admin.matricula-create', compact('classes', 'student'));
}

// ══════════════════════════════════════════════════════════════════════
// MATRÍCULAS — salvar nova (POST)
// ══════════════════════════════════════════════════════════════════════
public function matriculaStore(Request $request)
{
    $request->validate([
        'student_id'     => ['required', 'integer', 'exists:students,id'],
        'classes_id'     => ['required', 'integer', 'exists:classes,id'],
        'modality'       => ['required', 'in:distance_learning,in_person,hybrid,minisserie'],
        'status'         => ['required', 'in:not_checked,checked,scheduled_billing,canceled'],
        'value'          => ['nullable', 'numeric', 'min:0'],
        'discount'       => ['nullable', 'numeric', 'min:0'],
        'final_value'    => ['nullable', 'numeric', 'min:0'],
        'payment_method' => ['nullable', 'string', 'max:255'],
        'start_date'     => ['nullable', 'date'],
        'end_date'       => ['nullable', 'date'],
        'payday'         => ['nullable', 'date'],
        'plano'          => ['nullable', 'string', 'max:255'],
        'company'        => ['nullable', 'string', 'max:255'],
        'entidade'       => ['nullable', 'string', 'max:255'],
        'wallet'         => ['nullable', 'string', 'max:255'],
        'transaction_code'=> ['nullable', 'string', 'max:255'],
    ], [
        'student_id.exists' => 'Aluno não encontrado.',
        'classes_id.exists' => 'Minissérie não encontrada.',
    ]);

    // Calcula final_value se não informado
    $value      = (float) ($request->value      ?? 0);
    $discount   = (float) ($request->discount   ?? 0);
    $finalValue = $request->filled('final_value')
        ? (float) $request->final_value
        : max(0, $value - $discount);

    $enrollment = Enrollment::create([
        'student_id'       => $request->student_id,
        'classes_id'       => $request->classes_id,
        'modality'         => $request->modality,
        'status'           => $request->status,
        'value'            => $value,
        'discount'         => $discount,
        'final_value'      => $finalValue,
        'payment_method'   => $request->payment_method,
        'start_date'       => $request->start_date,
        'end_date'         => $request->end_date,
        'payday'           => $request->payday,
        'plano'            => $request->plano,
        'company'          => $request->company,
        'entidade'         => $request->entidade,
        'wallet'           => $request->wallet,
        'transaction_code' => $request->transaction_code,
        'log'              => auth()->user()->name,
    ]);

    $nomeAluno   = optional($enrollment->student)->name ?? "#{$request->student_id}";
    $nomeClasse  = optional($enrollment->classes)->title ?? "#{$request->classes_id}";

    return redirect()
        ->route('admin.matriculas')
        ->with('success', "Matrícula de \"{$nomeAluno}\" em \"{$nomeClasse}\" criada com sucesso!");
}

// ══════════════════════════════════════════════════════════════════════
// MATRÍCULAS — editar (GET)
// ══════════════════════════════════════════════════════════════════════
public function matriculaEdit(int $id)
{
    $enrollment = Enrollment::with('student', 'classes')->findOrFail($id);
    $classes    = Classes::where('express', '1')->where('status', 'able')->orderBy('title')->get();

    return view('pages.admin.matricula-edit', compact('enrollment', 'classes'));
}

// ══════════════════════════════════════════════════════════════════════
// MATRÍCULAS — salvar edição (PUT)
// ══════════════════════════════════════════════════════════════════════
public function matriculaUpdate(Request $request, int $id)
{
    $request->validate([
        'status'          => ['required', 'in:not_checked,checked,scheduled_billing,canceled'],
        'value'           => ['nullable', 'numeric', 'min:0'],
        'discount'        => ['nullable', 'numeric', 'min:0'],
        'final_value'     => ['nullable', 'numeric', 'min:0'],
        'payment_method'  => ['nullable', 'string', 'max:255'],
        'start_date'      => ['nullable', 'date'],
        'end_date'        => ['nullable', 'date'],
        'payday'          => ['nullable', 'date'],
        'plano'           => ['nullable', 'string', 'max:255'],
        'company'         => ['nullable', 'string', 'max:255'],
        'entidade'        => ['nullable', 'string', 'max:255'],
        'wallet'          => ['nullable', 'string', 'max:255'],
        'transaction_code'=> ['nullable', 'string', 'max:255'],
    ]);

    $enrollment = Enrollment::findOrFail($id);

    $value      = (float) ($request->value    ?? $enrollment->value);
    $discount   = (float) ($request->discount ?? $enrollment->discount);
    $finalValue = $request->filled('final_value')
        ? (float) $request->final_value
        : max(0, $value - $discount);

    $enrollment->fill([
        'status'           => $request->status,
        'value'            => $value,
        'discount'         => $discount,
        'final_value'      => $finalValue,
        'payment_method'   => $request->payment_method,
        'start_date'       => $request->start_date,
        'end_date'         => $request->end_date,
        'payday'           => $request->payday,
        'plano'            => $request->plano,
        'company'          => $request->company,
        'entidade'         => $request->entidade,
        'wallet'           => $request->wallet,
        'transaction_code' => $request->transaction_code,
        'log'              => auth()->user()->name,
    ]);
    $enrollment->save();

    return redirect()
        ->route('admin.matriculas')
        ->with('success', 'Matrícula atualizada com sucesso!');
}

// ══════════════════════════════════════════════════════════════════════
// API — busca de alunos via AJAX (para o campo de busca da matrícula)
// ══════════════════════════════════════════════════════════════════════
public function alunosBusca(Request $request)
{
    $q = trim($request->get('q', ''));

    if (strlen($q) < 2) {
        return response()->json([]);
    }

    $results = Student::where(function ($query) use ($q) {
        $query->where('name',  'like', "%{$q}%")
              ->orWhere('email', 'like', "%{$q}%")
              ->orWhere('cpf',   'like', "%{$q}%");
    })
    ->where('status', 'able')
    ->select('id', 'name', 'email', 'cpf', 'cargo', 'entidade', 'city', 'state')
    ->limit(10)
    ->get();

    return response()->json($results);
}
    // ══════════════════════════════════════════════════════════════════════
    // DEMAIS PÁGINAS
    // ══════════════════════════════════════════════════════════════════════
    public function vendas()     { return view('pages.admin.em-breve', ['titulo' => 'Vendas']); }
    public function cupons()     { return view('pages.admin.em-breve', ['titulo' => 'Cupons']); }
    public function certif()     { return view('pages.admin.em-breve', ['titulo' => 'Certificados']); }
    public function relatorios() { return view('pages.admin.em-breve', ['titulo' => 'Relatórios']); }
    public function suporte()    { return view('pages.admin.em-breve', ['titulo' => 'Suporte']); }
    public function equipe()     { return view('pages.admin.em-breve', ['titulo' => 'Equipe']); }
    public function permissoes() { return view('pages.admin.em-breve', ['titulo' => 'Permissões']); }
    public function logs()       { return view('pages.admin.em-breve', ['titulo' => 'Logs de atividade']); }
    public function integ()      { return view('pages.admin.em-breve', ['titulo' => 'Integrações']); }
    public function config()     { return view('pages.admin.em-breve', ['titulo' => 'Configurações']); }
}
