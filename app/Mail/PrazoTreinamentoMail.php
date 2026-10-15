<?php

namespace App\Mail;

use App\Models\AtribuicaoTreinamento;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrazoTreinamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AtribuicaoTreinamento $atribuicao,
        public int $diasRestantes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "O treinamento {$this->atribuicao->treinamento->titulo} vence em breve",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prazo-treinamento',
            with: [
                'nome' => $this->atribuicao->pessoa->nome,
                'treinamento' => $this->atribuicao->treinamento->titulo,
                'dataLimite' => Carbon::parse($this->atribuicao->data_limite)->format('d/m/Y'),
            ],
        );
    }
}
