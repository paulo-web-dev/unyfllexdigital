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

class AdminController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard() { return view('pages.admin.dashboard'); }

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


    // ══════════════════════════════════════════════════════════════════════
    // DEMAIS PÁGINAS
    // ══════════════════════════════════════════════════════════════════════
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
