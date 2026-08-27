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
use App\Models\ReferralClick;
use App\Models\FunnelEvent;
use App\Traits\EnrollmentScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // Aplica filtro automático de carteira para o comercial
    use EnrollmentScope;

    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════
    public function dashboard()
    {
        $hoje   = today();
        $ontem  = today()->subDay();
        $mes    = now()->startOfMonth();
        $mesAnt = now()->subMonth()->startOfMonth();
        $mesAntF= now()->subMonth()->endOfMonth();

        // Base de enrollments — automáticamente filtrada pela carteira para comercial
        $baseEnroll = $this->enrollmentQuery(fn ($q) =>
            $q->whereIn('classes_id', Classes::where('express','1')->pluck('id'))
        );

        $matriculasHoje  = (clone $baseEnroll)->whereDate('created_at', $hoje)->count();
        $matriculasOntem = (clone $baseEnroll)->whereDate('created_at', $ontem)->count();
        $matriculasMes   = (clone $baseEnroll)->where('created_at', '>=', $mes)->count();
        $totalMatriculas = (clone $baseEnroll)->count();

        // Receita — para comercial, só da sua carteira
        $receitaMes    = (clone $baseEnroll)->where('status','checked')->where('created_at','>=',$mes)->sum('final_value');
        $receitaAnt    = (clone $baseEnroll)->where('status','checked')->whereBetween('created_at',[$mesAnt,$mesAntF])->sum('final_value');
        $receitaTotal  = (clone $baseEnroll)->where('status','checked')->sum('final_value');
        $ticketMedio   = $matriculasMes > 0 ? $receitaMes / $matriculasMes : 0;

        // Alunos — super admin vê todos, comercial vê só os da sua carteira
        if ($this->isSuperAdmin()) {
            $alunosMinisserieIds = Student::where('minisserie','1')->pluck('id');
            $totalAlunos  = User::whereIn('student_id', $alunosMinisserieIds)->count();
            $alunosHoje   = User::whereIn('student_id', $alunosMinisserieIds)->whereDate('created_at', $hoje)->count();
            $alunosMes    = User::whereIn('student_id', $alunosMinisserieIds)->where('created_at','>=',$mes)->count();
            $alunosMesAnt = User::whereIn('student_id', $alunosMinisserieIds)->whereBetween('created_at',[$mesAnt,$mesAntF])->count();

            // views_minisseries também recebe cápsulas de curso gravado (assinantes); KPIs de minissérie filtram por turma express=1.
            $idsExpress    = Classes::where('express','1')->pluck('id');
            $capsulasMes   = ViewsMinisserie::whereIn('classes_id',$idsExpress)->where('created_at','>=',$mes)->count();
            $usuariosAtivos= ViewsMinisserie::whereIn('classes_id',$idsExpress)->where('updated_at','>=',now()->subDays(7))->distinct('id_user')->count('id_user');
            $inadimplentes = (clone $baseEnroll)->where('status','not_checked')->count();
            $pendentes     = (clone $baseEnroll)->where('status','not_checked')->sum('final_value');
            $progressoMedio= 0;
        } else {
            // Comercial: alunos vinculados às matrículas da sua carteira
            $studentIdsCarteira = (clone $baseEnroll)->pluck('student_id')->unique();
            $totalAlunos  = $studentIdsCarteira->count();
            $alunosHoje   = 0;
            $alunosMes    = 0;
            $alunosMesAnt = 0;
            $capsulasMes  = 0;
            $usuariosAtivos= 0;
            $inadimplentes = (clone $baseEnroll)->where('status','not_checked')->count();
            $pendentes     = (clone $baseEnroll)->where('status','not_checked')->sum('final_value');
            $progressoMedio= 0;
        }

        // Últimas vendas — filtradas pelo scope
        $ultimasVendas = (clone $baseEnroll)
            ->where('status','checked')
            ->with(['student:id,name,email','classes:id,title'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // Top cursos — filtrados pelo scope
        $topCursos = (clone $baseEnroll)
            ->selectRaw('classes_id, COUNT(*) as total')
            ->groupBy('classes_id')
            ->orderByDesc('total')
            ->with('classes:id,title,photo')
            ->limit(5)
            ->get();

        // Gráfico 14 dias — filtrado pelo scope
        $matriculasPorDia = (clone $baseEnroll)
            ->where('created_at','>=',now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')->orderBy('dia')
            ->pluck('total','dia');

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
            'receitaMes','receitaAnt','receitaTotal','ticketMedio',
            'capsulasMes','usuariosAtivos','progressoMedio',
            'inadimplentes','pendentes'
        );

        // Ranking de carteiras — apenas super admin vê
        $rankingCarteiras = collect();
        if ($this->isSuperAdmin()) {
            $classesIds = Classes::where('express', '1')->pluck('id');
            $rankingCarteiras = Enrollment::whereIn('classes_id', $classesIds)
                ->whereNotNull('wallet')
                ->where('wallet', '!=', '')
                ->selectRaw("
                    wallet,
                    COUNT(*) as total_matriculas,
                    SUM(CASE WHEN status = 'checked' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN status = 'not_checked' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'checked' THEN final_value ELSE 0 END) as receita,
                    SUM(CASE WHEN status = 'not_checked' THEN final_value ELSE 0 END) as a_receber,
                    MAX(created_at) as ultima_venda
                ")
                ->groupBy('wallet')
                ->orderByDesc('receita')
                ->get();
        }

        return view('pages.admin.dashboard', compact('kpis','ultimasVendas','topCursos','labels','valores','rankingCarteiras'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // ALUNOS
    // ══════════════════════════════════════════════════════════════════════
    public function alunos(Request $request)
    {
        $busca = trim($request->get('q', ''));
        $ordem = $request->get('ordem', 'recentes');

        $alunosMinisserieIds = Student::where('minisserie','1')->pluck('id');

        // Comercial só vê alunos das suas matrículas
        if ($this->isComercial()) {
            $studentIdsCarteira = $this->enrollmentQuery()->pluck('student_id')->unique();
            $query = User::whereIn('student_id', $studentIdsCarteira);
        } else {
            $query = User::whereIn('student_id', $alunosMinisserieIds);
        }

        $query->select(['id','name','email','cpf','telefone','setor','funcao','avatar','power','student_id','corporativo_id','created_at','updated_at']);

        if ($busca) {
            $query->where(fn ($q) => $q
                ->where('name',  'like', "%{$busca}%")
                ->orWhere('email','like', "%{$busca}%")
                ->orWhere('cpf',  'like', "%{$busca}%")
            );
        }

        $query->when($ordem === 'nome', fn ($q) => $q->orderBy('name'), fn ($q) => $q->orderByDesc('created_at'));
        $alunos = $query->paginate(25)->withQueryString();

        // KPIs
        $baseKpi = $this->isComercial()
            ? User::whereIn('student_id', $this->enrollmentQuery()->pluck('student_id')->unique())
            : User::whereIn('student_id', $alunosMinisserieIds);

        $totalAlunos     = (clone $baseKpi)->count();
        $novosHoje       = (clone $baseKpi)->whereDate('created_at', today())->count();
        $novosSemana     = (clone $baseKpi)->whereBetween('created_at',[now()->startOfWeek(), now()])->count();
        $totalMatriculas = $this->enrollmentQuery()->where('modality','minisserie')->count();
        // Só cápsulas de minissérie (views_minisseries também recebe curso gravado dos assinantes).
        $idsExpress      = Classes::where('express','1')->pluck('id');
        $totalViews      = $this->isSuperAdmin()
            ? ViewsMinisserie::whereIn('classes_id',$idsExpress)->distinct('id_user')->count('id_user')
            : 0;

        $kpis = compact('totalAlunos','novosHoje','novosSemana','totalMatriculas') + ['alunosAtivos' => $totalViews];

        $userIds    = $alunos->pluck('student_id')->filter()->values();
        $userIdsAll = $alunos->pluck('id')->filter()->values();

        $matriculasPorAluno = $this->enrollmentQuery(fn ($q) => $q
            ->where('modality','minisserie')
            ->whereIn('student_id', $userIds)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
        )->pluck('total','student_id');

        $capsulasPorUser = $this->isSuperAdmin()
            ? ViewsMinisserie::whereIn('classes_id',$idsExpress)->whereIn('id_user',$userIdsAll)->selectRaw('id_user, COUNT(DISTINCT video_id) as total')->groupBy('id_user')->pluck('total','id_user')
            : collect();

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

        // Base filtrada pelo scope — comercial vê só a própria carteira
        $query = $this->enrollmentQuery(fn ($q) => $q
            ->whereIn('classes_id', $classesExpressIds)
            ->with(['aluno:id,name,email,student_id','classes:id,title,slug,photo'])
            ->orderByDesc('id')
        );

        if ($status !== 'todas') $query->where('status', $status);
        if ($forma)              $query->where('payment_method','like',"%{$forma}%");

        if ($busca) {
            $userIds = User::where('name','like',"%{$busca}%")->orWhere('email','like',"%{$busca}%")->pluck('student_id')->filter();
            $query->where(fn ($q) => $q
                ->whereIn('student_id',$userIds)
                ->orWhereHas('classes', fn ($c) => $c->where('title','like',"%{$busca}%"))
            );
        }

        $matriculas = $query->paginate(25)->withQueryString();

        // KPIs filtrados pelo scope
        $base = $this->enrollmentQuery(fn ($q) => $q->whereIn('classes_id',$classesExpressIds));

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
    // ALUNOS — criar / salvar
    // ══════════════════════════════════════════════════════════════════════
    public function alunoCreate()
    {
        return view('pages.admin.aluno-create');
    }

    public function alunoStore(Request $request)
    {
        $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255','unique:students,email'],
            'cpf'        => ['nullable','string','max:255'],
            'phone'      => ['nullable','string','max:255'],
            'cargo'      => ['nullable','string','max:255'],
            'entidade'   => ['nullable','string','max:255'],
            'city'       => ['nullable','string','max:255'],
            'state'      => ['nullable','string','max:2'],
            'nascimento' => ['nullable','date'],
            'status'     => ['required','in:able,disabled'],
            'password'   => ['required','string','min:6'],
        ]);

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
            'minisserie' => '1',
            'password'   => Hash::make($request->password),
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'student_id' => $student->id,
            'funcao'     => $request->cargo,
            'setor'      => $request->entidade,
            'power'      => 1,
        ]);

        if ($request->boolean('matricular_agora')) {
            return redirect()->route('admin.matriculas.create', ['student_id' => $student->id])
                ->with('success', "Aluno \"{$student->name}\" criado! Agora matricule-o.");
        }

        return redirect()->route('admin.alunos')->with('success', "Aluno \"{$student->name}\" criado com sucesso!");
    }

    public function alunoEdit(int $id)
    {
        $user    = User::findOrFail($id);
        $student = Student::find($user->student_id);

        // Comercial só edita alunos da própria carteira
        if ($this->isComercial()) {
            $permitido = $this->enrollmentQuery()->where('student_id', $user->student_id)->exists();
            abort_unless($permitido, 403, 'Você não tem permissão para editar este aluno.');
        }

        return view('pages.admin.aluno-edit', compact('user','student'));
    }

    public function alunoUpdate(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Comercial só edita alunos da própria carteira
        if ($this->isComercial()) {
            $permitido = $this->enrollmentQuery()->where('student_id', $user->student_id)->exists();
            abort_unless($permitido, 403);
        }

        $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255'],
            'funcao'   => ['nullable','string','max:255'],
            'setor'    => ['nullable','string','max:255'],
            'telefone' => ['nullable','string','max:255'],
            'power'    => ['nullable','integer','min:0'],
            'password' => ['nullable','string','min:6'],
        ]);

        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->funcao   = $request->funcao;
        $user->setor    = $request->setor;
        $user->telefone = $request->telefone;

        // Comercial NÃO pode alterar o power
        if ($this->isSuperAdmin()) {
            $user->power = $request->power ?? $user->power;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if ($user->student_id) {
            $student = Student::find($user->student_id);
            if ($student) {
                $student->name    = $request->name;
                $student->email   = $request->email;
                $student->cargo   = $request->funcao;
                $student->entidade= $request->setor;
                $student->phone   = $request->telefone;
                if ($request->filled('password')) {
                    $student->password = Hash::make($request->password);
                }
                $student->save();
            }
        }

        return redirect()->route('admin.alunos')->with('success', 'Aluno atualizado com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // MATRÍCULAS — criar / salvar / editar
    // ══════════════════════════════════════════════════════════════════════
    public function matriculaCreate(Request $request)
    {
        $student  = $request->get('student_id') ? Student::find($request->get('student_id')) : null;
        $classes  = Classes::where('express','1')->where('status','able')->orderBy('title')->get();
        return view('pages.admin.matricula-create', compact('classes','student'));
    }

    public function matriculaStore(Request $request)
    {
        $request->validate([
            'student_id'      => ['required','integer','exists:students,id'],
            'classes_id'      => ['required','integer','exists:classes,id'],
            'modality'        => ['required','in:distance_learning,in_person,hybrid,minisserie'],
            'status'          => ['required','in:not_checked,checked,scheduled_billing,canceled'],
            'value'           => ['nullable','numeric','min:0'],
            'discount'        => ['nullable','numeric','min:0'],
            'final_value'     => ['nullable','numeric','min:0'],
            'payment_method'  => ['nullable','string','max:255'],
            'start_date'      => ['nullable','date'],
            'end_date'        => ['nullable','date'],
            'payday'          => ['nullable','date'],
            'plano'           => ['nullable','string','max:255'],
            'company'         => ['nullable','string','max:255'],
            'entidade'        => ['nullable','string','max:255'],
            'wallet'          => ['nullable','string','max:255'],
            'transaction_code'=> ['nullable','string','max:255'],
        ]);

        $value      = (float) ($request->value      ?? 0);
        $discount   = (float) ($request->discount   ?? 0);
        $finalValue = $request->filled('final_value') ? (float) $request->final_value : max(0, $value - $discount);

        // Comercial sempre grava a própria carteira, não pode sobrescrever
        $wallet = $this->isComercial()
            ? $this->carteiraAtual()
            : ($request->wallet ?? null);

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
            'wallet'           => $wallet,
            'transaction_code' => $request->transaction_code,
            'log'              => auth()->user()->name,
        ]);

        $nomeAluno  = optional($enrollment->student)->name  ?? "#{$request->student_id}";
        $nomeClasse = optional($enrollment->classes)->title ?? "#{$request->classes_id}";

        return redirect()->route('admin.matriculas')
            ->with('success', "Matrícula de \"{$nomeAluno}\" em \"{$nomeClasse}\" criada com sucesso!");
    }

    public function matriculaEdit(int $id)
    {
        $enrollment = Enrollment::with('student','classes')->findOrFail($id);

        // Comercial só edita matrículas da própria carteira
        if ($this->isComercial()) {
            abort_unless($enrollment->wallet === $this->carteiraAtual(), 403);
        }

        $classes = Classes::where('express','1')->where('status','able')->orderBy('title')->get();
        return view('pages.admin.matricula-edit', compact('enrollment','classes'));
    }

    public function matriculaUpdate(Request $request, int $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        // Comercial só edita matrículas da própria carteira
        if ($this->isComercial()) {
            abort_unless($enrollment->wallet === $this->carteiraAtual(), 403);
        }

        $request->validate([
            'status'          => ['required','in:not_checked,checked,scheduled_billing,canceled'],
            'value'           => ['nullable','numeric','min:0'],
            'discount'        => ['nullable','numeric','min:0'],
            'final_value'     => ['nullable','numeric','min:0'],
            'payment_method'  => ['nullable','string','max:255'],
            'start_date'      => ['nullable','date'],
            'end_date'        => ['nullable','date'],
            'payday'          => ['nullable','date'],
            'plano'           => ['nullable','string','max:255'],
            'company'         => ['nullable','string','max:255'],
            'entidade'        => ['nullable','string','max:255'],
            'wallet'          => ['nullable','string','max:255'],
            'transaction_code'=> ['nullable','string','max:255'],
        ]);

        $value      = (float) ($request->value    ?? $enrollment->value);
        $discount   = (float) ($request->discount ?? $enrollment->discount);
        $finalValue = $request->filled('final_value') ? (float) $request->final_value : max(0, $value - $discount);

        // Comercial não pode mudar a carteira
        $wallet = $this->isComercial()
            ? $enrollment->wallet
            : ($request->wallet ?? $enrollment->wallet);

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
            'wallet'           => $wallet,
            'transaction_code' => $request->transaction_code,
            'log'              => auth()->user()->name,
        ]);
        $enrollment->save();

        return redirect()->route('admin.matriculas')->with('success', 'Matrícula atualizada com sucesso!');
    }

    // ══════════════════════════════════════════════════════════════════════
    // BUSCA AJAX — filtra pela carteira para o comercial
    // ══════════════════════════════════════════════════════════════════════
    public function alunosBusca(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json([]);

        $query = Student::where(fn ($sq) => $sq
            ->where('name',  'like', "%{$q}%")
            ->orWhere('email','like', "%{$q}%")
            ->orWhere('cpf',  'like', "%{$q}%")
        )->where('status','able');

        // Comercial só busca alunos da própria carteira
        if ($this->isComercial()) {
            $studentIds = $this->enrollmentQuery()->pluck('student_id')->unique();
            $query->whereIn('id', $studentIds);
        }

        return response()->json(
            $query->select('id','name','email','cpf','cargo','entidade','city','state')
                  ->limit(10)->get()
        );
    }

    // ══════════════════════════════════════════════════════════════════════
    // BUSCA GLOBAL ADMIN — filtra para o comercial
    // ══════════════════════════════════════════════════════════════════════
    public function adminBusca(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json(['results' => []]);

        $results = [];

        // Alunos
        $alunosQuery = User::whereNotNull('student_id')
            ->where(fn ($query) => $query
                ->where('name',  'like', "%{$q}%")
                ->orWhere('email','like', "%{$q}%")
            )->select('id','name','email','funcao','student_id')->limit(4);

        // Comercial só vê alunos da carteira
        if ($this->isComercial()) {
            $studentIds = $this->enrollmentQuery()->pluck('student_id')->unique();
            $alunosQuery->whereIn('student_id', $studentIds);
        }

        foreach ($alunosQuery->get() as $a) {
            $results[] = ['tipo'=>'aluno','titulo'=>$a->name,'sub'=>$a->email,'meta'=>$a->funcao,'url'=>route('admin.alunos.edit',$a->id),'icone'=>'user'];
        }

        // Matrículas — filtradas pelo scope
        $classesIds = Classes::where('express','1')->pluck('id');
        $matriculas = $this->enrollmentQuery(fn ($q2) => $q2
            ->whereIn('classes_id', $classesIds)
            ->whereHas('aluno', fn ($qa) => $qa->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"))
            ->with(['aluno:id,name,email,student_id','classes:id,title'])
            ->limit(3)
        )->get();

        foreach ($matriculas as $m) {
            $results[] = ['tipo'=>'matricula','titulo'=>optional($m->aluno)->name ?? "Matrícula #{$m->id}",'sub'=>optional($m->classes)->title ?? '—','meta'=>ucfirst(str_replace('_',' ',$m->status)),'url'=>route('admin.matriculas.edit',$m->id),'icone'=>'file-text'];
        }

        // Cursos — apenas super admin
        if ($this->isSuperAdmin()) {
            $cursos = Classes::where('express','1')
                ->where(fn ($query) => $query->where('title','like',"%{$q}%")->orWhere('subtitle','like',"%{$q}%"))
                ->select('id','title','subtitle','status')->limit(3)->get();
            foreach ($cursos as $c) {
                $results[] = ['tipo'=>'curso','titulo'=>$c->title,'sub'=>$c->subtitle,'meta'=>$c->status==='able'?'Publicada':'Inativa','url'=>route('admin.cursos.show',$c->id),'icone'=>'film'];
            }
        }

        return response()->json(['results' => $results, 'query' => $q]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // FINANCEIRO — apenas super admin (protegido também na rota)
    // ══════════════════════════════════════════════════════════════════════
    public function financeiro(Request $request)
    {
        $this->authorize('admin.financeiro');

        $mes    = $request->get('mes', now()->format('Y-m'));
        $inicio = Carbon::parse($mes.'-01')->startOfMonth();
        $fim    = Carbon::parse($mes.'-01')->endOfMonth();

        $classesIds = Classes::where('express','1')->pluck('id');
        $base       = Enrollment::whereIn('classes_id', $classesIds);

        $receitaBruta    = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->sum('final_value');
        $receitaPendente = (clone $base)->where('status','not_checked')->whereBetween('created_at',[$inicio,$fim])->sum('final_value');
        $totalDesconto   = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->sum('discount');
        $qtdVendas       = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->count();
        $qtdCanceladas   = (clone $base)->where('status','canceled')->whereBetween('created_at',[$inicio,$fim])->count();
        $ticketMedio     = $qtdVendas > 0 ? $receitaBruta / $qtdVendas : 0;

        $inicioAnt  = $inicio->copy()->subMonth()->startOfMonth();
        $fimAnt     = $inicio->copy()->subMonth()->endOfMonth();
        $receitaAnt = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicioAnt,$fimAnt])->sum('final_value');
        $varReceita = $receitaAnt > 0 ? (($receitaBruta-$receitaAnt)/$receitaAnt)*100 : 0;

        $porForma = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->selectRaw('payment_method, COUNT(*) as qtd, SUM(final_value) as total')->groupBy('payment_method')->orderByDesc('total')->get();
        $porCurso = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->selectRaw('classes_id, COUNT(*) as qtd, SUM(final_value) as total')->groupBy('classes_id')->with('classes:id,title')->orderByDesc('total')->limit(10)->get();

        $receitaPorDia = (clone $base)->where('status','checked')->whereBetween('created_at',[$inicio,$fim])->selectRaw('DATE(created_at) as dia, SUM(final_value) as total, COUNT(*) as qtd')->groupBy('dia')->orderBy('dia')->get()->keyBy('dia');

        $diasLabels = collect(); $diasReceita = collect(); $diasQtd = collect();
        $d = $inicio->copy();
        while ($d->lte($fim)) {
            $key = $d->format('Y-m-d');
            $diasLabels->push($d->format('d'));
            $diasReceita->push((float)($receitaPorDia[$key]->total ?? 0));
            $diasQtd->push((int)($receitaPorDia[$key]->qtd ?? 0));
            $d->addDay();
        }

        $transacoes = (clone $base)->whereBetween('created_at',[$inicio,$fim])->with(['student:id,name,email','classes:id,title'])->orderByDesc('id')->paginate(20)->withQueryString();

        $mesesDisponiveis = collect();
        for ($i = 0; $i < 12; $i++) {
            $m = now()->subMonths($i);
            $mesesDisponiveis->push(['value'=>$m->format('Y-m'),'label'=>ucfirst($m->isoFormat('MMMM [de] YYYY'))]);
        }

        $kpis = compact('receitaBruta','receitaPendente','totalDesconto','qtdVendas','qtdCanceladas','ticketMedio','receitaAnt','varReceita');

        return view('pages.admin.financeiro', compact('kpis','mes','mesesDisponiveis','porForma','porCurso','transacoes','diasLabels','diasReceita','diasQtd'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // ANALYTICS — apenas super admin
    // ══════════════════════════════════════════════════════════════════════
    public function analytics()
    {
        $this->authorize('admin.analytics');

        $classesIds     = Classes::where('express','1')->pluck('id');
        // views_minisseries também recebe cápsulas de curso gravado (assinantes): todos os KPIs abaixo filtram por turma express=1.
        $views          = fn () => ViewsMinisserie::whereIn('classes_id',$classesIds);
        $onlineAgora    = $views()->where('updated_at','>=',now()->subMinutes(15))->distinct('id_user')->count('id_user');
        $acessosHoje    = $views()->whereDate('created_at',today())->distinct('id_user')->count('id_user');
        $acessosSemana  = $views()->where('created_at','>=',now()->startOfWeek())->distinct('id_user')->count('id_user');
        $totalViews     = $views()->count();

        $viewsPorAluno = DB::table('views_minisseries')->selectRaw('AVG(cnt) as media')
            ->fromSub(DB::table('views_minisseries')->whereIn('classes_id',$classesIds)->selectRaw('id_user, COUNT(*) as cnt')->groupBy('id_user'),'sub')->value('media') ?? 0;
        $tempoMedioMin = (int) round($viewsPorAluno * 12);
        $h = intdiv($tempoMedioMin,60); $m = $tempoMedioMin % 60;
        $tempoMedio = $tempoMedioMin >= 60 ? $h.'h '.($m>0?$m.'min':'') : $tempoMedioMin.'min';

        $totalVideosDisp = DB::table('video_lessons')->join('panels','video_lessons.panel_id','=','panels.id')->whereIn('panels.classes_id',$classesIds)->count();
        $taxaConclusao = 0;
        if ($totalVideosDisp > 0) {
            $mediaViews = DB::table('views_minisseries')->selectRaw('AVG(cnt) as media')->fromSub(DB::table('views_minisseries')->whereIn('classes_id',$classesIds)->selectRaw('id_user, COUNT(DISTINCT video_id) as cnt')->groupBy('id_user'),'sub')->value('media') ?? 0;
            $taxaConclusao = min(100,(int) round(($mediaViews/($totalVideosDisp/max(1,$classesIds->count())))*100));
        }

        $ativosUlt30 = $views()->where('updated_at','>=',now()->subDays(30))->distinct('id_user')->count('id_user');
        $totalMatric = Enrollment::whereIn('classes_id',$classesIds)->distinct('student_id')->count('student_id');
        $retencao30d = $totalMatric > 0 ? min(100,(int)round(($ativosUlt30/$totalMatric)*100)) : 0;

        $topCapsulas = $views()->selectRaw('video_id, COUNT(*) as views')->groupBy('video_id')->orderByDesc('views')->limit(10)->get()->map(function($v){
            $video = DB::table('video_lessons')->join('panels','video_lessons.panel_id','=','panels.id')->join('classes','panels.classes_id','=','classes.id')->where('video_lessons.id',$v->video_id)->select('video_lessons.titulo','panels.title as panel','classes.title as classe')->first();
            return (object)['video_id'=>$v->video_id,'views'=>$v->views,'titulo'=>$video?->titulo??'Sem título','panel'=>$video?->panel??'—','classe'=>$video?->classe??'—'];
        });

        $topMinisseries = $views()->selectRaw('classes_id, COUNT(*) as views, COUNT(DISTINCT id_user) as alunos')->groupBy('classes_id')->orderByDesc('views')->limit(8)->get()->map(function($v){
            $classe = Classes::find($v->classes_id);
            return (object)['titulo'=>$classe?->title??"Curso #{$v->classes_id}",'views'=>$v->views,'alunos'=>$v->alunos];
        });

        $acessosPorDia = $views()->where('created_at','>=',now()->subDays(29)->startOfDay())->selectRaw('DATE(created_at) as dia, COUNT(DISTINCT id_user) as alunos, COUNT(*) as views')->groupBy('dia')->orderBy('dia')->get()->keyBy('dia');
        $grafLabels = collect(); $grafAlunos = collect(); $grafViews = collect();
        for ($i = 29; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $grafLabels->push(now()->subDays($i)->format('d/m'));
            $grafAlunos->push((int)($acessosPorDia[$key]->alunos ?? 0));
            $grafViews->push((int)($acessosPorDia[$key]->views  ?? 0));
        }

        $alunosAtivos = $views()->where('updated_at','>=',now()->subDays(30))->selectRaw('id_user, COUNT(DISTINCT video_id) as capsulas, MAX(updated_at) as ultimo')->groupBy('id_user')->orderByDesc('capsulas')->limit(8)->get()->map(function($v){
            $user = User::find($v->id_user);
            return (object)['nome'=>$user?->name??"User #{$v->id_user}",'email'=>$user?->email??'','capsulas'=>$v->capsulas,'ultimo'=>Carbon::parse($v->ultimo)->diffForHumans()];
        });

        $kpis = compact('onlineAgora','acessosHoje','acessosSemana','totalViews','tempoMedio','taxaConclusao','retencao30d','ativosUlt30');
        return view('pages.admin.analytics', compact('kpis','topCapsulas','topMinisseries','alunosAtivos','grafLabels','grafAlunos','grafViews'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS (somente super admin)
    // ══════════════════════════════════════════════════════════════════════
    public function cursos()
    {
        $this->authorize('admin.cursos');
        $classes = Classes::where('express','1')->where('status','able')->with(['panels'=>fn($q)=>$q->where('status','able')->with('video_lesson','material')])->orderByDesc('id')->get();
        $totalMinisseries = $classes->count();
        $totalCapsulas    = $classes->sum(fn($c)=>$c->panels->flatMap(fn($p)=>$p->video_lesson)->count());
        $totalMateriais   = $classes->sum(fn($c)=>$c->panels->flatMap(fn($p)=>$p->material)->count());
        $totalMatriculas  = Enrollment::where('modality','minisserie')->count();
        $progressoMedio   = 0;
        $kpis = compact('totalMinisseries','totalCapsulas','totalMateriais','progressoMedio','totalMatriculas');
        return view('pages.admin.cursos', compact('classes','kpis'));
    }

    public function cursoShow(int $id)       { $this->authorize('admin.cursos'); return $this->_cursoShow($id); }
    public function cursoCreate()            { $this->authorize('admin.cursos'); return view('pages.admin.curso-create'); }
    public function cursoEdit(int $id)       { $this->authorize('admin.cursos'); $classe = Classes::with(['panels'=>fn($q)=>$q->orderBy('start_time')->orderByRaw("CAST(horario AS TIME)")->with('video_lesson','material')])->findOrFail($id); return view('pages.admin.curso-edit', compact('classe')); }
    public function panelCreate(int $cId)    { $this->authorize('admin.cursos'); $classe = Classes::findOrFail($cId); $teachers = Teacher::where('status','able')->orderBy('name')->get(); return view('pages.admin.panel-create', compact('classe','teachers')); }
    public function panelEdit(int $id)       { $this->authorize('admin.cursos'); $panel = Panel::with('video_lesson','material','classes')->findOrFail($id); $teachers = Teacher::where('status','able')->orderBy('name')->get(); return view('pages.admin.panel-edit', compact('panel','teachers')); }
    public function materiais(Request $req)  { $this->authorize('admin.cursos'); $busca=$req->get('q',''); $tipo=$req->get('tipo',''); $q=Material::orderByDesc('id'); if($busca)$q->where(fn($x)=>$x->where('name','like',"%{$busca}%")->orWhere('file_name','like',"%{$busca}%")); if($tipo)$q->where('type',$tipo); $materiais=$q->paginate(30)->withQueryString(); $kpis=['total'=>Material::count(),'pdfs'=>Material::where('type','PDF')->count(),'podcasts'=>Material::where('type','PODCAST')->count(),'ativos'=>Material::where('status','able')->count()]; return view('pages.admin.materiais', compact('materiais','kpis','busca','tipo')); }
    public function materialCreate()         { $this->authorize('admin.cursos'); $classesIds=Classes::where('express','1')->where('status','able')->pluck('id'); $panels=Panel::whereIn('classes_id',$classesIds)->with('classes:id,title')->orderBy('classes_id')->get(); return view('pages.admin.material-form', compact('panels')); }
    public function materialParaPanel(int $pId) { $this->authorize('admin.cursos'); $panel=Panel::with('classes:id,title','material')->findOrFail($pId); return view('pages.admin.material-para-panel', compact('panel')); }

    // ══════════════════════════════════════════════════════════════════════
    // CURSOS — métodos de escrita (store/update)
    // ══════════════════════════════════════════════════════════════════════
    public function cursoStore(Request $request)        { $this->authorize('admin.cursos'); return $this->_cursoStore($request); }
    public function cursoUpdate(Request $request, int $id){ $this->authorize('admin.cursos'); return $this->_cursoUpdate($request,$id); }
    public function panelStore(Request $request, int $cId){ $this->authorize('admin.cursos'); return $this->_panelStore($request,$cId); }
    public function panelUpdate(Request $request, int $id){ $this->authorize('admin.cursos'); return $this->_panelUpdate($request,$id); }
    public function videoUpdate(Request $request, int $id){ $this->authorize('admin.cursos'); $video=VideoLesson::findOrFail($id); $video->fill($request->only(['titulo','link','tasting_link','bkp_link','source','subtitle','status'])); $video->save(); return response()->json(['ok'=>true,'video'=>$video]); }
    public function materialStore(Request $request)       { $this->authorize('admin.cursos'); return $this->_materialStore($request); }
    public function materialEdit(int $id)                 { $this->authorize('admin.cursos'); $material=Material::findOrFail($id); $panelsVinculados=MaterialPanels::where('material_id',$id)->pluck('panel_id'); $classesIds=Classes::where('express','1')->where('status','able')->pluck('id'); $panels=Panel::whereIn('classes_id',$classesIds)->with('classes:id,title')->orderBy('classes_id')->get(); return view('pages.admin.material-form', compact('material','panels','panelsVinculados')); }
    public function materialUpdate(Request $request, int $id){ $this->authorize('admin.cursos'); return $this->_materialUpdate($request,$id); }
    public function materialDestroy(int $id)               { $this->authorize('admin.cursos'); MaterialPanels::where('material_id',$id)->delete(); Material::findOrFail($id)->delete(); return redirect()->route('admin.materiais')->with('success','Material removido.'); }
    public function materialParaPanelStore(Request $request, int $pId){ $this->authorize('admin.cursos'); return $this->_materialParaPanelStore($request,$pId); }
    public function materialVincular(int $mId, int $pId)   { $this->authorize('admin.cursos'); $panel=Panel::find($pId); MaterialPanels::firstOrCreate(['material_id'=>$mId,'panel_id'=>$pId],['course_id'=>$panel?->classes_id,'status'=>'able']); return response()->json(['ok'=>true]); }
    public function materialDesvincular(int $mId, int $pId){ $this->authorize('admin.cursos'); MaterialPanels::where('material_id',$mId)->where('panel_id',$pId)->delete(); return response()->json(['ok'=>true]); }

    // ══════════════════════════════════════════════════════════════════════
    // DEMAIS PÁGINAS
    // ══════════════════════════════════════════════════════════════════════
    public function relatorios() { $this->authorize('admin.relatorios'); return view('pages.admin.em-breve',['titulo'=>'Relatórios']); }
    public function suporte()    { return view('pages.admin.em-breve',['titulo'=>'Suporte']); }
    public function equipe()     { $this->authorize('admin.equipe');     return view('pages.admin.em-breve',['titulo'=>'Equipe']); }
    public function permissoes() { $this->authorize('admin.permissoes'); return view('pages.admin.em-breve',['titulo'=>'Permissões']); }
    public function logs()       { $this->authorize('admin.logs');       return view('pages.admin.em-breve',['titulo'=>'Logs']); }
    public function integ()      { $this->authorize('admin.integ');      return view('pages.admin.em-breve',['titulo'=>'Integrações']); }
    public function config()     { $this->authorize('admin.config');     return view('pages.admin.em-breve',['titulo'=>'Configurações']); }
    public function vendas()     { $this->authorize('admin.financeiro'); return view('pages.admin.em-breve',['titulo'=>'Vendas']); }
    public function cupons()     { $this->authorize('admin.financeiro'); return view('pages.admin.em-breve',['titulo'=>'Cupons']); }
    public function certif()     { return view('pages.admin.em-breve',['titulo'=>'Certificados']); }

    // ══════════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS (lógica reutilizada)
    // ══════════════════════════════════════════════════════════════════════
    private function _cursoShow(int $id)
    {
        $classe = Classes::with(['panels'=>fn($q)=>$q->orderBy('start_time')->orderByRaw("CAST(horario AS TIME)")->with('video_lesson','material','teachers')])->findOrFail($id);
        $panels=$classe->panels; $todosVideos=$panels->flatMap(fn($p)=>$p->video_lesson); $todosMateriais=$panels->flatMap(fn($p)=>$p->material);
        $totalVideos=$todosVideos->count(); $dur=$totalVideos*12; $horas=intdiv($dur,60); $minutos=$dur%60;
        $duracao=$dur>0?($horas>0?$horas.'h '.($minutos>0?$minutos.'min':''):$minutos.'min'):'—';
        $totalMatriculas=Enrollment::where('classes_id',$id)->where('modality','minisserie')->count();
        $matriculasChecked=Enrollment::where('classes_id',$id)->where('status','checked')->count();
        $progressoMedio=0;
        if($totalVideos>0&&$totalMatriculas>0){$vpu=ViewsMinisserie::where('classes_id',$id)->whereIn('video_id',$todosVideos->pluck('id'))->selectRaw('id_user, COUNT(DISTINCT video_id) as vistos')->groupBy('id_user')->get();if($vpu->isNotEmpty())$progressoMedio=(int)round($vpu->avg(fn($v)=>($v->vistos/$totalVideos)*100));}
        $kpis=['totalPanels'=>$panels->count(),'totalVideos'=>$totalVideos,'totalMateriais'=>$todosMateriais->count(),'totalPodcasts'=>$todosMateriais->where('type','PODCAST')->count(),'totalPdfs'=>$todosMateriais->where('type','PDF')->count(),'totalMatriculas'=>$totalMatriculas,'matriculasChecked'=>$matriculasChecked,'progressoMedio'=>$progressoMedio,'duracao'=>$duracao];
        return view('pages.admin.curso-show', compact('classe','panels','kpis'));
    }

    private function _cursoStore(Request $request)
    {
        $request->validate(['title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:255'],'slug'=>['required','string','max:255','unique:classes,slug'],'workload'=>['nullable','string','max:100'],'valor'=>['nullable','integer'],'status'=>['required','in:able,disabled'],'live'=>['nullable','boolean'],'start_date'=>['nullable','date'],'end_date'=>['nullable','date'],'photo'=>['nullable','string','max:255'],'info'=>['nullable','string','max:255'],'polo'=>['nullable','string','max:255'],'incompany'=>['nullable','boolean'],'novidade'=>['nullable','boolean']]);
        $classe=Classes::create(['title'=>$request->title,'subtitle'=>$request->subtitle,'slug'=>Str::slug($request->slug),'workload'=>$request->workload,'valor'=>$request->valor??0,'status'=>$request->status,'live'=>$request->boolean('live')?1:0,'start_date'=>$request->start_date,'end_date'=>$request->end_date,'photo'=>$request->photo,'info'=>$request->info,'polo'=>$request->polo,'incompany'=>$request->boolean('incompany')?1:0,'novidade'=>$request->boolean('novidade')?1:0,'express'=>1,'cc'=>0,'mc'=>0,'cv'=>0,'seminario'=>0,'confirmed'=>0]);
        return redirect()->route('admin.cursos.show',$classe->id)->with('success','Minissérie criada com sucesso!');
    }

    private function _cursoUpdate(Request $request, int $id)
    {
        $request->validate(['title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:255'],'slug'=>['required','string','max:255'],'workload'=>['nullable','string','max:100'],'valor'=>['nullable','integer'],'status'=>['required','in:able,disabled'],'live'=>['nullable','boolean'],'start_date'=>['nullable','date'],'end_date'=>['nullable','date'],'photo'=>['nullable','string','max:255'],'info'=>['nullable','string','max:255'],'polo'=>['nullable','string','max:255'],'incompany'=>['nullable','boolean'],'novidade'=>['nullable','boolean']]);
        $classe=Classes::findOrFail($id); $classe->title=$request->title; $classe->subtitle=$request->subtitle; $classe->slug=Str::slug($request->slug); $classe->workload=$request->workload; $classe->valor=$request->valor??$classe->valor; $classe->status=$request->status; $classe->live=$request->boolean('live')?1:0; $classe->start_date=$request->start_date; $classe->end_date=$request->end_date; $classe->photo=$request->photo; $classe->info=$request->info; $classe->polo=$request->polo; $classe->incompany=$request->boolean('incompany')?1:0; $classe->novidade=$request->boolean('novidade')?1:0; $classe->express=1; $classe->save();
        return redirect()->route('admin.cursos.show',$id)->with('success','Minissérie atualizada com sucesso!');
    }

    private function _panelStore(Request $request, int $classeId)
    {
        $request->validate(['title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:255'],'content'=>['nullable','string'],'status'=>['required','in:able,disabled'],'start_time'=>['nullable','date'],'horario'=>['nullable','string','max:10'],'teacher_id'=>['nullable','integer','exists:teachers,id']]);
        Classes::findOrFail($classeId);
        $panel=Panel::create(['classes_id'=>$classeId,'teacher_id'=>$request->teacher_id,'title'=>$request->title,'subtitle'=>$request->subtitle,'content'=>$request->content,'status'=>$request->status,'start_time'=>$request->start_time,'horario'=>$request->horario,'confirmation'=>0]);
        if($request->has('videos')){foreach($request->videos as $dados){if(empty(array_filter($dados)))continue;VideoLesson::create(['panel_id'=>$panel->id,'titulo'=>$dados['titulo']??null,'link'=>$dados['link']??null,'tasting_link'=>$dados['tasting_link']??null,'bkp_link'=>$dados['bkp_link']??null,'source'=>$dados['source']??'youtube','subtitle'=>$dados['subtitle']??null,'status'=>$dados['status']??'able']);}}
        return redirect()->route('admin.cursos.edit',$classeId)->with('success',"Temporada \"{$panel->title}\" criada com sucesso!");
    }

    private function _panelUpdate(Request $request, int $id)
    {
        $request->validate(['title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:255'],'content'=>['nullable','string'],'status'=>['required','in:able,disabled'],'start_time'=>['nullable','date'],'horario'=>['nullable','string','max:10'],'teacher_id'=>['nullable','integer','exists:teachers,id']]);
        $panel=Panel::findOrFail($id); $panel->fill($request->only(['title','subtitle','content','status','start_time','horario','teacher_id'])); $panel->save();
        if($request->has('videos')){foreach($request->videos as $videoId=>$dados){$video=VideoLesson::find($videoId);if(!$video)continue;$video->fill(array_filter($dados,fn($v)=>$v!==null));$video->save();}}
        return redirect()->route('admin.cursos.show',$panel->classes_id)->with('success','Temporada atualizada com sucesso!');
    }

    private function _materialStore(Request $request)
    {
        $request->validate(['name'=>['required','string','max:255'],'file_name'=>['required','string','max:255'],'type'=>['required','in:PDF,PowerPoint,Excel,Word,Arquivo,PODCAST'],'status'=>['required','in:able,disabled'],'panels'=>['nullable','array'],'panels.*'=>['integer','exists:panels,id']]);
        $material=Material::create($request->only(['name','file_name','type','status']));
        if($request->panels){foreach($request->panels as $pId){$panel=Panel::find($pId);MaterialPanels::firstOrCreate(['material_id'=>$material->id,'panel_id'=>$pId],['course_id'=>$panel?->classes_id,'status'=>'able']);}}
        return redirect()->route('admin.materiais')->with('success','Material criado e vinculado com sucesso!');
    }

    private function _materialUpdate(Request $request, int $id)
    {
        $request->validate(['name'=>['required','string','max:255'],'file_name'=>['required','string','max:255'],'type'=>['required','in:PDF,PowerPoint,Excel,Word,Arquivo,PODCAST'],'status'=>['required','in:able,disabled'],'panels'=>['nullable','array'],'panels.*'=>['integer','exists:panels,id']]);
        $material=Material::findOrFail($id); $material->fill($request->only(['name','file_name','type','status'])); $material->save();
        MaterialPanels::where('material_id',$id)->delete();
        if($request->panels){foreach($request->panels as $pId){$panel=Panel::find($pId);MaterialPanels::create(['material_id'=>$id,'panel_id'=>$pId,'course_id'=>$panel?->classes_id,'status'=>'able']);}}
        return redirect()->route('admin.materiais')->with('success','Material atualizado com sucesso!');
    }

    private function _materialParaPanelStore(Request $request, int $panelId)
    {
        $request->validate(['acao'=>['required','in:novo,existente'],'name'=>['required_if:acao,novo','nullable','string','max:255'],'file_name'=>['required_if:acao,novo','nullable','string','max:255'],'type'=>['required_if:acao,novo','nullable','in:PDF,PowerPoint,Excel,Word,Arquivo,PODCAST'],'status'=>['nullable','in:able,disabled'],'material_id'=>['required_if:acao,existente','nullable','integer','exists:materials,id']]);
        $panel=Panel::findOrFail($panelId);
        $material=$request->acao==='novo'?Material::create(['name'=>$request->name,'file_name'=>$request->file_name,'type'=>$request->type,'status'=>$request->status??'able']):Material::findOrFail($request->material_id);
        MaterialPanels::firstOrCreate(['material_id'=>$material->id,'panel_id'=>$panelId],['course_id'=>$panel->classes_id,'status'=>'able']);
        return redirect()->route('admin.panels.edit',$panelId)->with('success',"Material \"{$material->name}\" vinculado com sucesso!");
    }


    public function meuLink()
{
    $user  = auth()->user();
    $token = urlencode($user->name); // token = nome do vendedor

    // URL base do site (ajuste se necessário)
    $baseUrl  = config('app.url');
    $linkBase = "{$baseUrl}/?ref={$token}";
    $linkCursos = "{$baseUrl}/minisseries?ref={$token}";

    // Estatísticas de cliques
    $totalCliques = \App\Models\ReferralClick::where('token', $user->name)->count();
    $cliquesHoje  = \App\Models\ReferralClick::where('token', $user->name)->whereDate('clicked_at', today())->count();
    $cliques7d    = \App\Models\ReferralClick::where('token', $user->name)->where('clicked_at', '>=', now()->subDays(7)->toDateString())->count();
    $cliques30d   = \App\Models\ReferralClick::where('token', $user->name)->where('clicked_at', '>=', now()->subDays(30)->toDateString())->count();

    // Cliques por dia (últimos 30 dias) para o gráfico
    $cliquesGraf = \App\Models\ReferralClick::where('token', $user->name)
        ->where('clicked_at', '>=', now()->subDays(29)->toDateString())
        ->selectRaw('clicked_at, COUNT(*) as total')
        ->groupBy('clicked_at')
        ->orderBy('clicked_at')
        ->get()
        ->keyBy('clicked_at');

    $grafLabels  = collect();
    $grafValores = collect();
    for ($i = 29; $i >= 0; $i--) {
        $d = now()->subDays($i)->toDateString();
        $grafLabels->push(now()->subDays($i)->format('d/m'));
        $grafValores->push($cliquesGraf[$d]->total ?? 0);
    }

    // Conversões (matrículas que vieram deste vendedor)
    $classesIds = \App\Models\Classes::where('express', '1')->pluck('id');
    $baseEnroll = \App\Models\Enrollment::whereIn('classes_id', $classesIds)->where('wallet', $user->name);

    $totalConversoes = (clone $baseEnroll)->count();
    $receitaTotal    = (clone $baseEnroll)->where('status', 'checked')->sum('final_value');
    $receitaMes      = (clone $baseEnroll)->where('status', 'checked')->where('created_at', '>=', now()->startOfMonth())->sum('final_value');
    $taxaConversao   = $totalCliques > 0 ? round(($totalConversoes / $totalCliques) * 100, 1) : 0;

    // Últimas matrículas convertidas
    $ultimasConversoes = (clone $baseEnroll)
        ->with(['student:id,name,email', 'classes:id,title'])
        ->orderByDesc('id')
        ->limit(10)
        ->get();

    return view('pages.admin.meu-link', compact(
        'user', 'linkBase', 'linkCursos', 'token',
        'totalCliques', 'cliquesHoje', 'cliques7d', 'cliques30d',
        'grafLabels', 'grafValores',
        'totalConversoes', 'receitaTotal', 'receitaMes', 'taxaConversao',
        'ultimasConversoes'
    ));
}

public function referralAnalytics(Request $request)
{
    $this->authorize('admin.super');

    $periodo = $request->get('periodo', '30'); // dias
    $inicio  = now()->subDays((int) $periodo - 1)->startOfDay();

    // ── KPIs gerais ───────────────────────────────────────────────────────
    $totalCliquesGeral  = \App\Models\ReferralClick::count();
    $cliquesNoPeriodo   = \App\Models\ReferralClick::where('created_at', '>=', $inicio)->count();
    $totalVendedores    = \App\Models\ReferralClick::distinct('token')->count('token');
    $cliquesHoje        = \App\Models\ReferralClick::whereDate('created_at', today())->count();

    // Total de conversões (matrículas com wallet preenchida por referral)
    $classesIds = \App\Models\Classes::where('express', '1')->pluck('id');
    $totalConversoes = \App\Models\Enrollment::whereIn('classes_id', $classesIds)
        ->whereNotNull('wallet')
        ->where('wallet', '!=', 'Matrícula automatica ASAAS')
        ->count();

    $receitaReferral = \App\Models\Enrollment::whereIn('classes_id', $classesIds)
        ->whereNotNull('wallet')
        ->where('wallet', '!=', 'Matrícula automatica ASAAS')
        ->where('status', 'checked')
        ->sum('final_value');

    // ── Cliques por dia (gráfico de linha geral) ──────────────────────────
    $cliquesporDia = \App\Models\ReferralClick::where('created_at', '>=', $inicio)
        ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
        ->groupBy('dia')
        ->orderBy('dia')
        ->get()
        ->keyBy('dia');

    $grafDiasLabels  = collect();
    $grafDiasCliques = collect();
    for ($i = (int) $periodo - 1; $i >= 0; $i--) {
        $d = now()->subDays($i)->format('Y-m-d');
        $grafDiasLabels->push(now()->subDays($i)->format('d/m'));
        $grafDiasCliques->push($cliquesporDia[$d]->total ?? 0);
    }

    // ── Cliques por vendedor no período ───────────────────────────────────
    $cliquesporVendedor = \App\Models\ReferralClick::where('created_at', '>=', $inicio)
        ->selectRaw('token, COUNT(*) as cliques')
        ->groupBy('token')
        ->orderByDesc('cliques')
        ->get();

    // ── Conversões por vendedor ───────────────────────────────────────────
    $conversoesPorVendedor = \App\Models\Enrollment::whereIn('classes_id', $classesIds)
        ->whereNotNull('wallet')
        ->where('wallet', '!=', 'Matrícula automatica ASAAS')
        ->where('created_at', '>=', $inicio)
        ->selectRaw('wallet, COUNT(*) as conversoes, SUM(CASE WHEN status="checked" THEN final_value ELSE 0 END) as receita')
        ->groupBy('wallet')
        ->orderByDesc('receita')
        ->get()
        ->keyBy('wallet');

    // ── Ranking completo por vendedor ─────────────────────────────────────
    $rankingVendedores = $cliquesporVendedor->map(function ($item) use ($conversoesPorVendedor) {
        $conv     = $conversoesPorVendedor[$item->token] ?? null;
        $conversoes = $conv?->conversoes ?? 0;
        $receita    = $conv?->receita    ?? 0;
        $taxa       = $item->cliques > 0 ? round(($conversoes / $item->cliques) * 100, 1) : 0;

        return (object) [
            'token'     => $item->token,
            'cliques'   => $item->cliques,
            'conversoes'=> $conversoes,
            'receita'   => $receita,
            'taxa'      => $taxa,
        ];
    });

    // ── Cliques por hora do dia (heatmap simplificado) ────────────────────
    $cliquesporHora = \App\Models\ReferralClick::where('created_at', '>=', $inicio)
        ->selectRaw('HOUR(created_at) as hora, COUNT(*) as total')
        ->groupBy('hora')
        ->orderBy('hora')
        ->get()
        ->keyBy('hora');

    $grafHorasLabels  = collect();
    $grafHorasCliques = collect();
    for ($h = 0; $h < 24; $h++) {
        $grafHorasLabels->push(str_pad($h, 2, '0', STR_PAD_LEFT) . 'h');
        $grafHorasCliques->push($cliquesporHora[$h]->total ?? 0);
    }

    // ── Top 5 tokens gráfico barras ───────────────────────────────────────
    $top5 = $cliquesporVendedor->take(5);
    $grafTop5Labels  = $top5->pluck('token');
    $grafTop5Cliques = $top5->pluck('cliques');

    $kpis = compact(
        'totalCliquesGeral', 'cliquesNoPeriodo', 'totalVendedores',
        'cliquesHoje', 'totalConversoes', 'receitaReferral'
    );

    return view('pages.admin.referral-analytics', compact(
        'kpis', 'periodo', 'rankingVendedores',
        'grafDiasLabels', 'grafDiasCliques',
        'grafHorasLabels', 'grafHorasCliques',
        'grafTop5Labels', 'grafTop5Cliques'
    ));
}
public function funilAnalytics(Request $request)
{
    $this->authorize('admin.super');

    $periodo      = (int) $request->get('periodo', 30);
    $origemFiltro = $request->get('origem', 'todos');
    $inicio       = now()->subDays($periodo - 1)->startOfDay();

    $etapas = ['visita','visualizou','carrinho','checkout','pagamento','converteu'];

    // ── Funil geral ───────────────────────────────────────────────────────
    $baseQuery = \App\Models\FunnelEvent::where('created_at', '>=', $inicio);
    if ($origemFiltro !== 'todos') {
        $baseQuery->where('origem', $origemFiltro);
    }

    $funil = $baseQuery->clone()
        ->selectRaw('etapa, COUNT(DISTINCT session_id) as total')
        ->groupBy('etapa')
        ->get()
        ->keyBy('etapa');

    // Garante todas as etapas na ordem certa
    $funilOrdenado = collect($etapas)->map(fn($e) => (object)[
        'etapa' => $e,
        'total' => $funil[$e]->total ?? 0,
    ]);

    // ── Gráfico por dia ───────────────────────────────────────────────────
    $eventosPorDia = $baseQuery->clone()
        ->selectRaw('DATE(created_at) as dia, etapa, COUNT(DISTINCT session_id) as total')
        ->groupBy('dia', 'etapa')
        ->orderBy('dia')
        ->get()
        ->groupBy('etapa');

    $grafDiasLabels = collect();
    for ($i = $periodo - 1; $i >= 0; $i--) {
        $grafDiasLabels->push(now()->subDays($i)->format('d/m'));
    }

    $grafDias = [];
    foreach (['visita','carrinho','checkout','converteu'] as $etapa) {
        $porDia = $eventosPorDia[$etapa]?->keyBy('dia') ?? collect();
        $serie  = [];
        for ($i = $periodo - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $serie[] = $porDia[$d]->total ?? 0;
        }
        $grafDias[$etapa] = $serie;
    }

    // ── Gráfico orgânico vs referral por etapa ────────────────────────────
    $porOrigem = $baseQuery->clone()
        ->selectRaw('etapa, origem, COUNT(DISTINCT session_id) as total')
        ->groupBy('etapa', 'origem')
        ->get()
        ->groupBy('etapa');

    $grafOrigem = ['organico' => [], 'referral' => []];
    foreach ($etapas as $etapa) {
        $grupo = $porOrigem[$etapa] ?? collect();
        $grafOrigem['organico'][] = (int) ($grupo->firstWhere('origem','organico')?->total ?? 0);
        $grafOrigem['referral'][] = (int) ($grupo->firstWhere('origem','referral')?->total ?? 0);
    }

    // ── Top miniséries no carrinho ─────────────────────────────────────────
    $topCarrinho = $baseQuery->clone()
        ->where('etapa', 'carrinho')
        ->whereNotNull('classes_id')
        ->selectRaw('classes_id, COUNT(*) as total')
        ->groupBy('classes_id')
        ->orderByDesc('total')
        ->limit(8)
        ->get();

    // ── Top cidades ───────────────────────────────────────────────────────
    $topCidades = $baseQuery->clone()
        ->whereNotNull('cidade')
        ->selectRaw('cidade, estado, pais, COUNT(DISTINCT session_id) as total')
        ->groupBy('cidade', 'estado', 'pais')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    // ── Funil por origem (orgânico + cada vendedor) ────────────────────────
    $funilPorOrigem = $baseQuery->clone()
        ->selectRaw('
            origem,
            COALESCE(referral, "organico") as referral,
            SUM(CASE WHEN etapa = "visita"     THEN 1 ELSE 0 END) as visitas,
            SUM(CASE WHEN etapa = "carrinho"   THEN 1 ELSE 0 END) as carrinhos,
            SUM(CASE WHEN etapa = "checkout"   THEN 1 ELSE 0 END) as checkouts,
            SUM(CASE WHEN etapa = "converteu"  THEN 1 ELSE 0 END) as convertidos
        ')
        ->groupBy('origem', 'referral')
        ->orderByDesc('visitas')
        ->get()
        ->map(function ($o) {
            $o->taxa = $o->visitas > 0 ? round(($o->convertidos / $o->visitas) * 100, 1) : 0;
            return $o;
        });
        $funil = $funilOrdenado;
        return view('pages.admin.funil-analytics', compact(
            'periodo', 'origemFiltro',
            'grafDiasLabels', 'grafDias', 'grafOrigem',
            'topCarrinho', 'topCidades', 'funilPorOrigem'
        ) + ['funil' => $funilOrdenado]);
}

}
