<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PresencaConfirmadaNotification extends Notification
{
    use Queueable;

    protected $evento;

    public function __construct($evento)
    {
        $this->evento = $evento;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Presença Confirmada! ✅')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('O teu check-in foi registado com sucesso no evento "' . $this->evento->titulo . '".')
                    ->line('Obrigado pela tua participação ativa na nossa comunidade.')
                    ->line('Esperamos que o conteúdo tenha sido valioso para o teu crescimento profissional!');
    }
}