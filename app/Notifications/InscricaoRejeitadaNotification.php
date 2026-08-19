<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class InscricaoRejeitadaNotification extends Notification
{
    use Queueable;

    protected $evento;
    protected $inscricao;

    public function __construct($evento, $inscricao)
    {
        $this->evento = $evento;
        $this->inscricao = $inscricao;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Comprovativo Rejeitado ⚠️')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('O comprovativo de pagamento que enviaste para o evento "' . $this->evento->titulo . '" não foi aprovado.')
            ->line('Data do Evento: ' . Carbon::parse($this->evento->data_evento)->format('d/m/Y H:i'));

        if ($this->inscricao->observacao_avaliacao) {
            $mail->line(new HtmlString('<strong>Motivo:</strong> ' . e($this->inscricao->observacao_avaliacao)));
        }

        return $mail
            ->line('Podes reenviar um novo comprovativo para tentar novamente, dentro do prazo do evento.')
            ->action('Reenviar Comprovativo', route('eventos.comprovativo', $this->evento->id))
            ->line('Se achas que isto foi um engano, contacta o organizador do evento.')
            ->salutation('Cumprimentos, ' . config('app.name'));
    }
}