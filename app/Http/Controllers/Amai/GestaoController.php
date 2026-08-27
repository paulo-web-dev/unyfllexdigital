<?php

namespace App\Http\Controllers\Amai;

use App\Http\Controllers\Controller;
use App\Models\AmaiVinculo;
use App\Services\AmaiService;
use Illuminate\Http\Request;

/**
 * Área de gestão AMAI (master e pontos focais). Separada do admin da Unyflex.
 * Toda rota passa por 'amai.papel'; o vínculo do ator chega em $request->attributes['amaiVinculo'].
 *
 * Regra de visibilidade: ponto focal só enxerga usuários com parent_user_id = ele.
 * Qualquer alvo fora do escopo devolve 404 (não revela existência).
 */
class GestaoController extends Controller
{
    public function __construct(private AmaiService $amai) {}

    // ── Lista de usuários ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $eu = $this->ator($request);

        if ($eu->isMaster()) {
            $municipio = $request->query('municipio');
            $municipio = in_array($municipio, AmaiService::MUNICIPIOS, true) ? $municipio : null;
            $usuarios  = $this->amai->todosUsuarios($municipio);
            $focais    = $this->amai->pontosFocais();
            $vagas     = null;
        } else {
            $municipio = $eu->municipio;
            $usuarios  = $this->amai->usuariosDe($eu);
            $focais    = collect();
            $vagas     = $this->amai->vagas($eu);
        }

        $uso = $this->amai->resumoUso($usuarios->pluck('user_id'));

        return view('amai.index', compact('eu', 'usuarios', 'focais', 'vagas', 'uso', 'municipio'));
    }

    // ── Novo usuário ─────────────────────────────────────────────────────
    public function novoUsuario(Request $request)
    {
        $eu    = $this->ator($request);
        $focal = $this->focalAlvo($request, $eu, (int) $request->query('focal'));

        return view('amai.usuario-form', [
            'eu'     => $eu,
            'focal'  => $focal,
            'focais' => $eu->isMaster() ? $this->amai->pontosFocais() : collect([$focal]),
            'vagas'  => $focal ? $this->amai->vagas($focal) : null,
        ]);
    }

    public function salvarUsuario(Request $request)
    {
        $eu = $this->ator($request);

        $dados = $request->validate([
            'focal_id' => ['required', 'integer'],
            'nome'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:180'],
            'cpf'      => ['required', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/'],
            'cargo'    => ['nullable', 'string', 'max:120'],
        ], [
            'cpf.regex' => 'CPF inválido (11 dígitos).',
        ]);

        $focal = $this->focalAlvo($request, $eu, (int) $dados['focal_id']);
        abort_if(! $focal, 404);

        $vinculo = $this->amai->cadastrarUsuario($request->user(), $eu, $focal, $dados);

        return redirect()->route('amai.index', $eu->isMaster() ? ['municipio' => $focal->municipio] : [])
            ->with('success', "Usuário {$vinculo->user->name} cadastrado. Senha inicial: o CPF (só números).");
    }

    public function removerUsuario(Request $request, int $id)
    {
        $eu   = $this->ator($request);
        $alvo = $this->usuarioNoEscopo($eu, $id);

        $this->amai->removerUsuario($request->user(), $alvo);

        return back()->with('success', "Acesso de {$alvo->user->name} encerrado. A vaga foi liberada.");
    }

    public function historico(Request $request, int $id)
    {
        $eu   = $this->ator($request);
        $alvo = $this->usuarioNoEscopo($eu, $id, incluirRemovidos: true);

        return view('amai.historico', ['eu' => $eu, 'alvo' => $alvo] + $this->amai->historico($alvo));
    }

    // ── Pontos focais (master) ───────────────────────────────────────────
    public function focais(Request $request)
    {
        $eu = $this->ator($request);
        abort_if(! $eu->isMaster(), 404);

        $focais   = $this->amai->pontosFocais();
        $ocupados = $focais->pluck('municipio')->all();

        return view('amai.focais', [
            'eu'         => $eu,
            'focais'     => $focais,
            'livres'     => array_values(array_diff(AmaiService::MUNICIPIOS, $ocupados)),
            'uso'        => $this->amai->resumoUso($focais->pluck('user_id')),
        ]);
    }

    public function salvarFocal(Request $request)
    {
        $eu = $this->ator($request);
        abort_if(! $eu->isMaster(), 404);

        $dados = $request->validate([
            'municipio' => ['required', 'string', 'in:' . implode(',', AmaiService::MUNICIPIOS)],
            'nome'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:180'],
            'cpf'       => ['required', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/'],
            'cargo'     => ['nullable', 'string', 'max:120'],
        ], ['cpf.regex' => 'CPF inválido (11 dígitos).']);

        $v = $this->amai->cadastrarPontoFocal($request->user(), $dados);

        return redirect()->route('amai.focais')->with('success', "Ponto focal de {$v->municipio} cadastrado: {$v->user->name}. Senha inicial: o CPF (só números).");
    }

    public function cota(Request $request, int $id)
    {
        $eu = $this->ator($request);
        abort_if(! $eu->isMaster(), 404);

        $focal = AmaiVinculo::ativos()->pontosFocais()->whereKey($id)->first();
        abort_if(! $focal, 404);

        $dados = $request->validate(['cota' => ['required', 'integer', 'min:0', 'max:500']]);
        $this->amai->alterarCota($focal, (int) $dados['cota']);

        return back()->with('success', "Cota de {$focal->municipio} ajustada para {$dados['cota']} vagas.");
    }

    public function removerFocal(Request $request, int $id)
    {
        $eu = $this->ator($request);
        abort_if(! $eu->isMaster(), 404);

        $focal = AmaiVinculo::ativos()->pontosFocais()->whereKey($id)->first();
        abort_if(! $focal, 404);

        $this->amai->removerPontoFocal($request->user(), $focal);

        return back()->with('success', "Ponto focal de {$focal->municipio} removido. Os usuários dele continuam ativos.");
    }

    // ── Helpers de escopo ────────────────────────────────────────────────
    private function ator(Request $request): AmaiVinculo
    {
        $v = $request->attributes->get('amaiVinculo');
        abort_if(! $v instanceof AmaiVinculo, 404);
        return $v;
    }

    /** Ponto focal alvo de um cadastro: master escolhe; ponto focal é sempre ele mesmo. */
    private function focalAlvo(Request $request, AmaiVinculo $eu, int $focalId): ?AmaiVinculo
    {
        if ($eu->isPontoFocal()) {
            return $eu;
        }
        if (! $focalId) {
            return null;
        }
        return AmaiVinculo::ativos()->pontosFocais()->whereKey($focalId)->with('user')->first();
    }

    /** Usuário dentro do escopo do ator (master: todos; ponto focal: só os dele). 404 fora do escopo. */
    private function usuarioNoEscopo(AmaiVinculo $eu, int $id, bool $incluirRemovidos = false): AmaiVinculo
    {
        $q = AmaiVinculo::usuarios()->whereKey($id)->with('user');
        if (! $incluirRemovidos) {
            $q->ativos();
        }
        if ($eu->isPontoFocal()) {
            $q->where('parent_user_id', $eu->user_id);
        }
        $alvo = $q->first();
        abort_if(! $alvo, 404);
        return $alvo;
    }
}
