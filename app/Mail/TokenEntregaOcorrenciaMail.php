<?php

namespace App\Mail;

use App\Models\Ocorrencia;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TokenEntregaOcorrenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $linkNegacao;
    public $ocorrencia;
    public $linkConfirmacao;
    public $usuarioReceptor;

    public function __construct(Usuario $usuarioReceptor, Ocorrencia $ocorrencia, string $token)
    {
        $this->ocorrencia = $ocorrencia;
        $this->usuarioReceptor = $usuarioReceptor;
        // O link aponta para a rota do teu frontend ou backend que valida o token
        $this->linkConfirmacao = config('app.url') . ":8000/recebimento-confirmado/$token";
        $this->linkNegacao = config('app.url') . ":8000/recebimento-negado/$token";
    }

    public function build()
    {
        return $this->subject('Confirmação de Recebimento - Achados e Perdidos')
            ->view('email_envio_usuario_confirmar_recebimento');
    }
}
