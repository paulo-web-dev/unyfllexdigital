<?php

namespace App\Http\Controllers;

use App\Models\PanelCertificate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/**
 * Validação PÚBLICA do certificado por painel (sem autenticação).
 *
 * O "Código de autenticidade" impresso no rodapé do certificado é o
 * `panel_certificates.token`. A página exibe apenas o que o certificado
 * imprime (aluno, título, carga horária, data de conclusão) — nada além.
 */
class CertificadoController extends Controller
{
    public function validar(Request $request, ?string $token = null)
    {
        // Código vindo do formulário (?codigo=) → URL canônica /certificado/validar/{token}.
        if ($token === null) {
            $codigo = preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('codigo', ''));
            if ($codigo === '') {
                return view('pages.certificado-validar', ['token' => null, 'cert' => null]);
            }

            return redirect()->route('certificado.validar.token', substr($codigo, 0, 40));
        }

        $cert = null;
        try {
            $cert = PanelCertificate::where('token', $token)->first();
        } catch (QueryException $e) {
            // Tabela panel_certificates ainda não criada — trata como não encontrado.
        }

        // A collation da tabela é case-insensitive; o código é case-sensitive.
        if ($cert && ! hash_equals((string) $cert->token, $token)) {
            $cert = null;
        }

        return response()->view('pages.certificado-validar', compact('token', 'cert'), $cert ? 200 : 404);
    }
}
