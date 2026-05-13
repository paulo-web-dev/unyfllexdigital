<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Panel;
use App\Models\VideoLesson;
use App\Models\ViewsMinisserie;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()  { return view('pages.admin.dashboard'); }

    // ──────────────────────────────────────────────────────────────────────
    // ALUNOS
    // ──────────────────────────────────────────────────────────────────────
    public function alunos(Request $request)
    {
        $busca = trim($request->get('q', ''));
        $ordem = $request->get('ordem', 'recentes');

        $alunosMinisserieIds = Student::where('minisserie', '1')->pluck('id');

        $query = User::whereIn('student_id', $alunosMinisserieIds)
            ->select([
                'id', 'name', 'email', 'cpf', 'telefone',
                'setor', 'funcao', 'avatar', 'power',
                'student_id', 'corporativo_id', 'created_at', 'updated_at',
            ]);

        if ($busca) {
            $query->where(fn ($q) => $q
                ->where('name',  'like', "%{$busca}%")
                ->orWhere('email', 'like', "%{$busca}%")
                ->orWhere('cpf',   'like', "%{$busca}%")
            );
        }

        $query->when($ordem === 'nome',     fn ($q) => $q->orderBy('name'))
              ->when($ordem !== 'nome',     fn ($q) => $q->orderByDesc('created_at'));

        $alunos = $query->paginate(25)->withQueryString();

        $totalAlunos     = User::whereIn('student_id', $alunosMinisserieIds)->count();
        $novosHoje       = User::whereIn('student_id', $alunosMinisserieIds)->whereDate('created_at', today())->count();
        $novosSemana     = User::whereIn('student_id', $alunosMinisserieIds)->whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $totalMatriculas = Enrollment::where('modality', 'minisserie')->count();
        $totalViews      = ViewsMinisserie::distinct('id_user')->count('id_user');

        $kpis = compact('totalAlunos', 'novosHoje', 'novosSemana', 'totalMatriculas') + ['alunosAtivos' => $totalViews];

        $userIds    = $alunos->pluck('student_id')->filter()->values();
        $userIdsAll = $alunos->pluck('id')->filter()->values();

        $matriculasPorAluno = Enrollment::where('modality', 'minisserie')
            ->whereIn('student_id', $userIds)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $capsulasPorUser = ViewsMinisserie::whereIn('id_user', $userIdsAll)
            ->selectRaw('id_user, COUNT(DISTINCT video_id) as total')
            ->groupBy('id_user')
            ->pluck('total', 'id_user');

        return view('pages.admin.alunos', compact(
            'alunos', 'kpis', 'busca', 'ordem', 'matriculasPorAluno', 'capsulasPorUser'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // MATRÍCULAS
    // ──────────────────────────────────────────────────────────────────────
    public function matriculas(Request $request)
    {
        $classesExpressIds = Classes::where('express', '1')->where('status', 'able')->pluck('id');

        $busca  = trim($request->get('q', ''));
        $status = $request->get('status', 'todas');
        $forma  = $request->get('forma', '');

        $query = Enrollment::whereIn('classes_id', $classesExpressIds)
            ->with(['aluno:id,name,email,student_id', 'classes:id,title,slug,photo'])
            ->orderByDesc('id');

        if ($status !== 'todas') $query->where('status', $status);
        if ($forma)              $query->where('payment_method', 'like', "%{$forma}%");

        if ($busca) {
            $userIds = User::where('name', 'like', "%{$busca}%")
                ->orWhere('email', 'like', "%{$busca}%")
                ->pluck('student_id')->filter();
            $query->where(fn ($q) => $q
                ->whereIn('student_id', $userIds)
                ->orWhereHas('classes', fn ($c) => $c->where('title', 'like', "%{$busca}%"))
            );
        }

        $matriculas = $query->paginate(25)->withQueryString();

        $base           = Enrollment::whereIn('classes_id', $classesExpressIds);
        $totalGeral     = (clone $base)->count();
        $totalHoje      = (clone $base)->whereDate('created_at', today())->count();
        $totalChecked   = (clone $base)->where('status', 'checked')->count();
        $totalPending   = (clone $base)->where('status', 'not_checked')->count();
        $totalSched     = (clone $base)->where('status', 'scheduled_billing')->count();
        $receitaTotal   = (clone $base)->where('status', 'checked')->sum('final_value');
        $receitaPending = (clone $base)->where('status', 'not_checked')->sum('final_value');

        $kpis = compact('totalGeral', 'totalHoje', 'totalChecked', 'totalPending', 'totalSched', 'receitaTotal', 'receitaPending');

        return view('pages.admin.matriculas', compact('matriculas', 'kpis', 'busca', 'status', 'forma'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // CURSOS — listagem
    // ──────────────────────────────────────────────────────────────────────
    public function cursos()
    {
        $classes = Classes::where('express', '1')
            ->where('status', 'able')
            ->with(['panels' => fn ($q) => $q->where('status', 'able')->with('video_lesson', 'material')])
            ->orderByDesc('id')
            ->get();

        $totalMinisseries = $classes->count();
        $totalCapsulas    = $classes->sum(fn ($c) => $c->panels->flatMap(fn ($p) => $p->video_lesson)->count());
        $totalMateriais   = $classes->sum(fn ($c) => $c->panels->flatMap(fn ($p) => $p->material)->count());
        $totalMatriculas  = Enrollment::where('modality', 'minisserie')->count();

        $progressoMedio = 0;
        if ($totalMinisseries > 0) {
            $progressos = $classes->map(function ($classe) {
                $ids   = $classe->panels->flatMap(fn ($p) => $p->video_lesson)->pluck('id');
                $total = $ids->count();
                if (!$total) return null;
                $vistos = ViewsMinisserie::where('classes_id', $classe->id)->whereIn('video_id', $ids)->distinct('video_id')->count('video_id');
                return ($vistos / $total) * 100;
            })->filter()->values();
            $progressoMedio = $progressos->isNotEmpty() ? (int) round($progressos->average()) : 0;
        }

        $kpis = compact('totalMinisseries', 'totalCapsulas', 'totalMateriais', 'progressoMedio', 'totalMatriculas');

        return view('pages.admin.cursos', compact('classes', 'kpis'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // CURSOS — detalhe
    // ──────────────────────────────────────────────────────────────────────
    public function cursoShow(int $id)
    {
        $classe = Classes::where('id', $id)
            ->with(['panels' => fn ($q) => $q->orderBy('start_time')->orderByRaw("CAST(horario AS TIME)")->with('video_lesson', 'material', 'teachers')])
            ->firstOrFail();

        $panels         = $classe->panels;
        $todosVideos    = $panels->flatMap(fn ($p) => $p->video_lesson);
        $todosMateriais = $panels->flatMap(fn ($p) => $p->material);
        $totalPanels    = $panels->count();
        $totalVideos    = $todosVideos->count();
        $totalMateriais = $todosMateriais->count();
        $totalPodcasts  = $todosMateriais->where('type', 'PODCAST')->count();
        $totalPdfs      = $todosMateriais->where('type', 'PDF')->count();
        $dur            = $totalVideos * 12;
        $horas   = intdiv($dur, 60);
        $minutos = $dur % 60;
        $duracao = $dur > 0
            ? ($horas > 0 ? $horas . 'h ' . ($minutos > 0 ? $minutos . 'min' : '') : $minutos . 'min')
            : '—';
        $totalMatriculas   = Enrollment::where('classes_id', $id)->where('modality', 'minisserie')->count();
        $matriculasChecked = Enrollment::where('classes_id', $id)->where('status', 'checked')->count();

        $progressoMedio = 0;
        if ($totalVideos > 0 && $totalMatriculas > 0) {
            $vpu = ViewsMinisserie::where('classes_id', $id)->whereIn('video_id', $todosVideos->pluck('id'))
                ->selectRaw('id_user, COUNT(DISTINCT video_id) as vistos')->groupBy('id_user')->get();
            if ($vpu->isNotEmpty()) $progressoMedio = (int) round($vpu->avg(fn ($v) => ($v->vistos / $totalVideos) * 100));
        }

        $kpis = compact('totalPanels','totalVideos','totalMateriais','totalPodcasts','totalPdfs','totalMatriculas','matriculasChecked','progressoMedio','duracao');

        return view('pages.admin.curso-show', compact('classe', 'panels', 'kpis'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // CURSOS — editar (GET)
    // ──────────────────────────────────────────────────────────────────────
    public function cursoEdit(int $id)
    {
        $classe = Classes::with([
            'panels' => fn ($q) => $q
                ->orderBy('start_time')
                ->orderByRaw("CAST(horario AS TIME)")
                ->with('video_lesson', 'material')
        ])->findOrFail($id);

        return view('pages.admin.curso-edit', compact('classe'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // CURSOS — salvar (PUT)
    // ──────────────────────────────────────────────────────────────────────
    public function cursoUpdate(Request $request, int $id)
    {
        $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'slug'       => ['required', 'string', 'max:255'],
            'workload'   => ['nullable', 'string', 'max:100'],
            'status'     => ['required', 'in:able,disabled'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'photo'      => ['nullable', 'string', 'max:255'],
        ]);

        $classe = Classes::findOrFail($id);
        $classe->title      = $request->title;
        $classe->subtitle   = $request->subtitle;
        $classe->slug       = Str::slug($request->slug);
        $classe->workload   = $request->workload;
        $classe->status     = $request->status;
        $classe->start_date = $request->start_date;
        $classe->end_date   = $request->end_date;
        $classe->photo      = $request->photo;
        $classe->save();

        return redirect()
            ->route('admin.cursos.show', $id)
            ->with('success', 'Minissérie atualizada com sucesso!');
    }

    // ──────────────────────────────────────────────────────────────────────
    // PANELS — editar (GET)
    // ──────────────────────────────────────────────────────────────────────
    public function panelEdit(int $id)
    {
        $panel = Panel::with('video_lesson', 'material', 'classes')->findOrFail($id);
        return view('pages.admin.panel-edit', compact('panel'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // PANELS — salvar (PUT)
    // ──────────────────────────────────────────────────────────────────────
    public function panelUpdate(Request $request, int $id)
    {
        $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'content'    => ['nullable', 'string'],
            'status'     => ['required', 'in:able,disabled'],
            'start_time' => ['nullable', 'date'],
            'horario'    => ['nullable', 'string', 'max:10'],
            // Vídeos: arrays indexados por video_id
            'videos'     => ['nullable', 'array'],
            'videos.*.titulo'   => ['nullable', 'string', 'max:255'],
            'videos.*.link'     => ['nullable', 'string', 'max:500'],
            'videos.*.tasting_link' => ['nullable', 'string', 'max:500'],
            'videos.*.subtitle' => ['nullable', 'string'],
            'videos.*.status'   => ['nullable', 'in:able,disabled'],
        ]);

        $panel = Panel::findOrFail($id);
        $panel->title      = $request->title;
        $panel->subtitle   = $request->subtitle;
        $panel->content    = $request->content;
        $panel->status     = $request->status;
        $panel->start_time = $request->start_time;
        $panel->horario    = $request->horario;
        $panel->save();

        // Salva cada vídeo do painel
        if ($request->has('videos')) {
            foreach ($request->videos as $videoId => $dados) {
                $video = VideoLesson::find($videoId);
                if (!$video) continue;
                $video->titulo       = $dados['titulo']       ?? $video->titulo;
                $video->link         = $dados['link']         ?? $video->link;
                $video->tasting_link = $dados['tasting_link'] ?? $video->tasting_link;
                $video->subtitle     = $dados['subtitle']     ?? $video->subtitle;
                $video->status       = $dados['status']       ?? $video->status;
                $video->save();
            }
        }

        return redirect()
            ->route('admin.cursos.show', $panel->classes_id)
            ->with('success', 'Temporada e vídeos atualizados com sucesso!');
    }

    // ──────────────────────────────────────────────────────────────────────
    // VÍDEOS — salvar individual via AJAX (PUT)
    // ──────────────────────────────────────────────────────────────────────
    public function videoUpdate(Request $request, int $id)
    {
        $request->validate([
            'titulo'       => ['nullable', 'string', 'max:255'],
            'link'         => ['nullable', 'string', 'max:500'],
            'tasting_link' => ['nullable', 'string', 'max:500'],
            'subtitle'     => ['nullable', 'string'],
            'status'       => ['nullable', 'in:able,disabled'],
        ]);

        $video = VideoLesson::findOrFail($id);
        $video->fill($request->only(['titulo', 'link', 'tasting_link', 'subtitle', 'status']));
        $video->save();

        return response()->json(['ok' => true, 'video' => $video]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // DEMAIS PÁGINAS
    // ──────────────────────────────────────────────────────────────────────
    public function financeiro() { return view('pages.admin.financeiro'); }
    public function analytics()  { return view('pages.admin.analytics'); }
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
