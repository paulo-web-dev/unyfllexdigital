<?php
namespace App\Http\Controllers;

use App\Models\ColaboradorArp;
use App\Models\ConviteArp;
use App\Models\Empresas;
use App\Models\FuncionarioQuestionarioArp;
use App\Services\ConviteArpService;
use App\Jobs\EnviarConviteArpJob;
use Illuminate\Http\Request;
use Auth;

class ColaboradorArpController extends Controller
{
    public function __construct(private ConviteArpService $service)
    {
        $this->middleware('auth');
    }

    public function index(int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);

        $kpis = $this->service->kpis($idEmpresa);

        $colaboradores = ColaboradorArp::where('id_empresa', $idEmpresa)
            ->with(['conviteAtivo'])
            ->orderBy('nome')
            ->paginate(50);

        // ── Setor/função vindos do "DOC de ARP" ───────────────────────────────
        // O setor real é o que a pessoa preencheu no formulário e foi gravado em
        // funcionario_questionario_arp (colunas setor/funcao) — não em
        // colaboradores_arp. Cruzamos por e-mail (mesma lógica do relatório) e
        // anexamos a cada colaborador. orderBy('id') garante que, havendo mais de
        // uma resposta para o mesmo e-mail, prevaleça a última.
        $docPorEmail = FuncionarioQuestionarioArp::where('id_empresa', $idEmpresa)
            ->orderBy('id')
            ->get(['email', 'setor', 'funcao'])
            ->keyBy(fn ($f) => mb_strtolower(trim((string) $f->email)));

        foreach ($colaboradores as $c) {
            $doc = $docPorEmail->get(mb_strtolower(trim((string) $c->email)));

            // Prioriza o que veio do DOC de ARP; se ainda não respondeu, cai para
            // o que estiver no cadastro do colaborador.
            $c->setor_doc  = filled($doc?->setor)  ? $doc->setor  : ($c->setor ?: null);
            $c->funcao_doc = filled($doc?->funcao) ? $doc->funcao : ($c->cargo ?: null);
        }

        return view('arp.colaboradores.index', compact('empresa', 'kpis', 'colaboradores'));
    }

    public function create(int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);
        return view('arp.colaboradores.create', compact('empresa'));
    }

    public function store(Request $request, int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);

        $request->validate([
            'nome'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'cargo'           => 'nullable|string|max:255',
            'descricao_cargo' => 'nullable|string',
            'setor'           => 'nullable|string|max:255',
        ]);

        ColaboradorArp::firstOrCreate(
            ['id_empresa' => $idEmpresa, 'email' => strtolower($request->email)],
            [
                'nome'            => $request->nome,
                'cargo'           => $request->cargo,
                'descricao_cargo' => $request->descricao_cargo,
                'setor'           => $request->setor,
                'status'          => 'ativo',
            ]
        );

        return redirect()->route('arp.colaboradores.index', $idEmpresa)
            ->with('success', 'Colaborador cadastrado com sucesso!');
    }

    /** ── NOVO: Formulário de edição ── */
    public function edit(int $id)
    {
        $colaborador = ColaboradorArp::with('empresa')->findOrFail($id);
        abort_unless($colaborador->empresa->id_user == Auth::user()->id_instituicao, 403);

        $empresa = $colaborador->empresa;

        return view('arp.colaboradores.edit', compact('colaborador', 'empresa'));
    }

    /** ── NOVO: Salvar edição ── */
    public function update(Request $request, int $id)
    {
        $colaborador = ColaboradorArp::with('empresa')->findOrFail($id);
        abort_unless($colaborador->empresa->id_user == Auth::user()->id_instituicao, 403);

        $request->validate([
            'nome'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'cargo'           => 'nullable|string|max:255',
            'descricao_cargo' => 'nullable|string',
            'setor'           => 'nullable|string|max:255',
            'status'          => 'nullable|in:ativo,inativo',
        ]);

        $colaborador->update([
            'nome'            => $request->nome,
            'email'           => strtolower($request->email),
            'cargo'           => $request->cargo,
            'descricao_cargo' => $request->descricao_cargo,
            'setor'           => $request->setor,
            'status'          => $request->status ?? $colaborador->status,
        ]);

        return redirect()->route('arp.colaboradores.index', $colaborador->id_empresa)
            ->with('success', 'Colaborador atualizado com sucesso!');
    }

    public function importarLote(Request $request, int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);

        $request->validate(['lista' => 'required|string|min:5']);

        $resultado = $this->service->importarEmLote($idEmpresa, $request->lista);

        $msg = "✓ {$resultado['criados']} colaborador(es) importado(s).";
        if (!empty($resultado['erros'])) {
            $msg .= ' Erros: ' . implode(' | ', $resultado['erros']);
        }

        return redirect()->route('arp.colaboradores.index', $idEmpresa)->with('success', $msg);
    }

    public function toggleStatus(int $id)
    {
        $c = ColaboradorArp::findOrFail($id);
        abort_unless($c->empresa->id_user == Auth::user()->id_instituicao, 403);
        $c->update(['status' => $c->status === 'ativo' ? 'inativo' : 'ativo']);
        return back()->with('success', 'Status atualizado.');
    }

    public function destroy(int $id)
    {
        $c = ColaboradorArp::findOrFail($id);
        abort_unless($c->empresa->id_user == Auth::user()->id_instituicao, 403);
        $c->delete();
        return back()->with('success', 'Colaborador removido.');
    }

    public function criarConvites(Request $request, int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);
        $criados = $this->service->criarConvites($idEmpresa);
        return back()->with('success', "$criados convite(s) criado(s).");
    }

    public function dispararEmails(int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);
        $this->service->criarConvites($idEmpresa);
        $disparados = $this->service->dispararEnvioMassa($idEmpresa);
        return back()->with('success', "✉ $disparados e-mail(s) adicionado(s) à fila de envio.");
    }

    public function reenviarPendentes(int $idEmpresa)
    {
        $empresa = Empresas::findOrFail($idEmpresa);
        abort_unless($empresa->id_user == Auth::user()->id_instituicao, 403);
        $reenviados = $this->service->reenviarPendentes($idEmpresa);
        return back()->with('success', "↺ $reenviados lembrete(s) enviado(s).");
    }

    public function enviarIndividual(int $idColaborador)
    {
        $c = ColaboradorArp::with('empresa')->findOrFail($idColaborador);
        abort_unless($c->empresa->id_user == Auth::user()->id_instituicao, 403);

        $convite = ConviteArp::where('id_colaborador', $c->id)
            ->whereIn('status', ['pendente', 'enviado'])
            ->first();

        if (!$convite) {
            $convite = ConviteArp::create([
                'id_empresa'     => $c->id_empresa,
                'id_colaborador' => $c->id,
                'token'          => ConviteArp::gerarToken(),
                'status'         => 'pendente',
                'expira_em'      => now()->addDays(30),
            ]);
        }

        EnviarConviteArpJob::dispatch($convite);

        return back()->with('success', "✉ E-mail enviado para {$c->nome}.");
    }

    public function linkConvite(int $idColaborador)
    {
        $c = ColaboradorArp::with('conviteAtivo')->findOrFail($idColaborador);
        abort_unless($c->empresa->id_user == Auth::user()->id_instituicao, 403);

        $convite = $c->conviteAtivo ?? ConviteArp::create([
            'id_empresa'     => $c->id_empresa,
            'id_colaborador' => $c->id,
            'token'          => ConviteArp::gerarToken(),
            'status'         => 'pendente',
            'expira_em'      => now()->addDays(30),
        ]);

        return response()->json(['url' => $convite->urlFormulario()]);
    }
}
