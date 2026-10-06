<?php

namespace App\Http\Controllers;

use App\Services\AssinaturaVitrineService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Vitrine pública da Assinatura Premium Unyflex (/assinatura) — público órgão público (CNPJ).
 * Sem login e sem middleware de assinante. Toda conversão é pelo WhatsApp (config assinatura_vitrine).
 */
class AssinaturaVitrineController extends Controller
{
    public function __construct(private AssinaturaVitrineService $vitrine)
    {
    }

    public function home()
    {
        return view('vitrine.home', [
            'resumo'     => $this->vitrine->resumo(),
            'carrosseis' => $this->vitrine->carrosseis(),
        ]);
    }

    public function planos()
    {
        return view('vitrine.planos', [
            'resumo'      => $this->vitrine->resumo(),
            'planos'      => $this->vitrine->planos(),
            'recursos'    => $this->vitrine->recursosPlanos(),
            'calculadora' => $this->vitrine->dadosCalculadora(),
        ]);
    }

    public function categorias()
    {
        $categorias = $this->vitrine->categorias();

        return view('vitrine.categorias', [
            'resumo'     => $this->vitrine->resumo(),
            'categorias' => $categorias,
            'destaques'  => $categorias->where('destaque', true)->sortByDesc('cursos')->values(),
        ]);
    }

    public function categoria(Request $request, string $slug)
    {
        $categoria = $this->vitrine->categoria($slug) ?? abort(404);

        $busca  = Str::limit(trim((string) $request->query('busca', '')), 80, '');
        $pagina = max(1, (int) $request->query('page', 1));

        return view('vitrine.categoria', [
            'resumo'    => $this->vitrine->resumo(),
            'categoria' => $categoria,
            'itens'     => $this->vitrine->cursosDaCategoria($categoria, $busca, $pagina),
            'busca'     => $busca,
            'outras'    => $this->vitrine->categorias()->where('slug', '!=', $slug)->sortByDesc('cursos')->take(8)->values(),
        ]);
    }
}
