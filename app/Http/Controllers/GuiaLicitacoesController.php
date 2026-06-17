<?php

namespace App\Http\Controllers;

use App\Models\LeadGuia;
use Illuminate\Http\Request;

class GuiaLicitacoesController extends Controller
{
    /**
     * >>> AJUSTE AQUI <<<
     * WhatsApp da Unyflex (somente digitos, com 55 + DDD). Ex.: 5511988887777
     */
    private const WHATSAPP = '5511999999999';

    /**
     * Caminho do PDF (isca) dentro de storage/app/public.
     * Ele e servido publicamente em /storage/fav/guia-licitacoes-unyflex.pdf
     */
    private const PDF_PATH          = 'fav/guia-licitacoes-unyflex.pdf';
    private const PDF_NOME_DOWNLOAD = 'Guia-Contratacoes-Publicas-Lei-14133-Unyflex.pdf';

    private function whatsMsg(): string
    {
        return rawurlencode('Ola! Acabei de baixar o guia das contratacoes publicas e quero saber mais sobre as Minisseries da Unyflex.');
    }

    public function landing(Request $request)
    {
        $utm = [
            'utm_source'   => $request->query('utm_source'),
            'utm_medium'   => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content'  => $request->query('utm_content'),
            'utm_term'     => $request->query('utm_term'),
        ];

        return view('guia.landing', [
            'utm'      => $utm,
            'whatsapp' => self::WHATSAPP,
            'whatsMsg' => $this->whatsMsg(),
        ]);
    }

    public function store(Request $request)
    {
        // Honeypot anti-spam: campo invisivel "website". Se vier preenchido, e bot.
        if (filled($request->input('website'))) {
            return redirect()->route('guia.landing');
        }

        $data = $request->validate([
            'nome'     => ['required', 'string', 'min:3', 'max:150'],
            'email'    => ['required', 'email', 'max:150'],
            'whatsapp' => ['required', 'string', 'min:10', 'max:25'],
            'cidade'   => ['required', 'string', 'max:120'],
            'cargo'    => ['required', 'string', 'max:120'],
        ], [
            'nome.required'     => 'Informe seu nome.',
            'nome.min'          => 'Digite seu nome completo.',
            'email.required'    => 'Informe seu e-mail.',
            'email.email'       => 'Digite um e-mail valido.',
            'whatsapp.required' => 'Informe seu WhatsApp.',
            'whatsapp.min'      => 'Digite um WhatsApp valido com DDD.',
            'cidade.required'   => 'Informe sua cidade.',
            'cargo.required'    => 'Informe seu cargo.',
        ]);

        $extra = [
            'origem'       => 'lp-guia-licitacoes',
            'utm_source'   => $request->input('utm_source'),
            'utm_medium'   => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'utm_content'  => $request->input('utm_content'),
            'utm_term'     => $request->input('utm_term'),
            'ip'           => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 255),
        ];

        // Deduplica por e-mail: se ja existe, atualiza o contato mas PRESERVA status e observacoes.
        $lead = LeadGuia::where('email', $data['email'])->first();

        if ($lead) {
            $lead->fill(array_merge($data, $extra));
            $lead->save();
        } else {
            $lead = LeadGuia::create(array_merge($data, $extra));
        }

        $request->session()->put('guia_lead_ok', true);
        $request->session()->put('guia_lead_id', $lead->id);
        $request->session()->put('guia_lead_nome', $lead->nome);

        return redirect()->route('guia.obrigado');
    }

    public function obrigado(Request $request)
    {
        if (! $request->session()->get('guia_lead_ok')) {
            return redirect()->route('guia.landing');
        }

        return view('guia.obrigado', [
            'nome'     => $request->session()->get('guia_lead_nome'),
            'whatsapp' => self::WHATSAPP,
            'whatsMsg' => $this->whatsMsg(),
        ]);
    }

    public function download(Request $request)
    {
        if (! $request->session()->get('guia_lead_ok')) {
            return redirect()->route('guia.landing');
        }

        // Marca o download no lead (controle de quem realmente baixou).
        $id = $request->session()->get('guia_lead_id');
        if ($id) {
            LeadGuia::where('id', $id)->where('baixou', false)->update([
                'baixou'     => true,
                'baixado_em' => now(),
            ]);
        }

        $caminho = storage_path('app/public/' . self::PDF_PATH);

        if (! is_file($caminho)) {
            abort(404, 'Arquivo do guia nao encontrado.');
        }

        return response()->download($caminho, self::PDF_NOME_DOWNLOAD);
    }
}
