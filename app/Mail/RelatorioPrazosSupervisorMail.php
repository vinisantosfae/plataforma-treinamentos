<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RelatorioPrazosSupervisorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, array{colaborador: string, treinamento: string, dataLimite: string, diasRestantes: int, status: string}>  $itens
     */
    public function __construct(
        public ?string $supervisorNome,
        public Collection $itens,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Treinamentos da sua equipe perto do vencimento',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.relatorio-prazos-supervisor');
    }
}
