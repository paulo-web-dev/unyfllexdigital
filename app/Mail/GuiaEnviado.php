<?php

namespace App\Mail;

use App\Models\LeadGuia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GuiaEnviado extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomeLead;
    public string $link;

    public function __construct(LeadGuia $lead, string $link)
    {
        // So o primeiro nome, pra saudacao
        $this->nomeLead = (string) Str::of($lead->nome)->trim()->explode(' ')->first();
        $this->link     = $link;
    }

    public function build()
    {
        return $this->subject('Seu guia das contratacoes publicas chegou')
                    ->view('emails.guia')
                    ->text('emails.guia_text');
    }
}
