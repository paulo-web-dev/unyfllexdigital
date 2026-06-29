<?php

namespace App\Http\Controllers;

use App\Mail\GuiaEnviado;
use App\Models\LeadGuia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class GuiaLicitacoesController extends Controller
{
    /**
     * >>> AJUSTE AQUI <<<
     * WhatsApp da Unyflex (somente digitos, com 55 + DDD). Ex.: 5511988887777
     */
    private const WHATSAPP = '5511999999999';

    /**
     * Caminho do PDF dentro de storage/app/public.
     * Servido publicamente em /storage/fav/guia-licitacoes-unyflex.pdf
     */
    private const PDF_PATH = 'fav/guia-licitacoes-unyflex.pdf';

    private function whatsMsg(): string
    {
        return rawurlencode('Ola! Acabei de baixar o guia das contratacoes publicas e quero saber mais sobre as Minisseries da Unyflex.');
    }

    /** URL publica final do arquivo (usa APP_URL). */
    private function pdfUrl(): string
    {
        return asset('storage/' . self::PDF_PATH);
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
   // Honeypot anti-spam: campo invisivel. Se vier preenchido, e bot.
if (filled($request->input('lp_hp'))) {
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

        // Deduplica por e-mail: atualiza o contato preservando status/observacoes.
        $lead = LeadGuia::where('email', $data['email'])->first();

        if ($lead) {
            $lead->fill(array_merge($data, $extra));
            $lead->save();
        } else {
            $lead = LeadGuia::create(array_merge($data, $extra));
        }

        // Link assinado do arquivo (sem login, valido permanentemente, e marca o download).
        $link = URL::signedRoute('guia.arquivo', ['lead' => $lead->id]);

        // Envia o e-mail com o guia. Falha de SMTP NAO quebra a experiencia do usuario.
        try {
            Mail::to($lead->email)->send(new GuiaEnviado($lead, $link));
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar e-mail do guia (lead '.$lead->id.'): '.$e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Http::timeout(8)
                ->asJson()
                ->post('https://n8n.unyflex.com.br/webhook/guia-whatsapp', [
                    'nome'     => $lead->nome,
                    'whatsapp' => $lead->whatsapp,
                    'email'    => $lead->email,
                    'cidade'   => $lead->cidade,
                    'cargo'    => $lead->cargo,
                    'lead_id'  => $lead->id,
                ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Falha ao disparar WhatsApp do guia (lead ' . $lead->id . '): ' . $e->getMessage()
            );
        }
        
        $request->session()->put('guia_lead_ok', true);
        $request->session()->put('guia_lead_nome', $lead->nome);
        $request->session()->put('guia_lead_email', $lead->email);

        return redirect()->route('guia.obrigado');
    }

    public function obrigado(Request $request)
    {
        if (! $request->session()->get('guia_lead_ok')) {
            return redirect()->route('guia.landing');
        }

        return view('guia.obrigado', [
            'nome'     => $request->session()->get('guia_lead_nome'),
            'email'    => $request->session()->get('guia_lead_email'),
            'whatsapp' => self::WHATSAPP,
            'whatsMsg' => $this->whatsMsg(),
        ]);
    }

    /**
     * Link do arquivo enviado por e-mail. Protegido por assinatura (middleware 'signed').
     * Marca o download e redireciona para o PDF publico.
     */
    public function arquivo(Request $request)
    {
        $id = $request->query('lead');

        if ($id) {
            LeadGuia::where('id', $id)->where('baixou', false)->update([
                'baixou'     => true,
                'baixado_em' => now(),
            ]);
        }

        return redirect()->away($this->pdfUrl());
    }
}
