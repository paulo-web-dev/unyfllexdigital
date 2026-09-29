<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso interno (secretaria/coordenação) a cada cadastro na estrutura AMAI.
 * Recebe só valores prontos para exibir — nada de model, para o conteúdo ser o do momento do cadastro.
 */
class AmaiNovoCadastro extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{nome:string,email:string,telefone:string,municipio:string,papel:string,
     *              cadastrado_por:string,data_hora:string,recadastro:bool} $d
     */
    public function __construct(public array $d) {}

    public function build()
    {
        $tipo = $this->d['papel'] === 'Ponto focal' ? 'ponto focal' : 'usuário';

        return $this->subject(sprintf('[AMAI] %s %s — %s: %s',
                        $this->d['recadastro'] ? 'Recadastro de' : 'Novo', $tipo, $this->d['municipio'], $this->d['nome']))
                    ->view('emails.amai-novo-cadastro')
                    ->text('emails.amai-novo-cadastro_text');
    }
}
