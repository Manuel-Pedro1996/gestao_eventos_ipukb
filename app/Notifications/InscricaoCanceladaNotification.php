<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InscricaoCanceladaNotification extends Notification implements ShouldQueue
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
                    ->subject('Inscrição Cancelada ❌')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('Confirmamos que a tua inscrição no evento "' . $this->evento->titulo . '" foi cancelada.')
                    ->line('Se isto foi um erro, podes voltar a inscrever-te a qualquer momento na nossa plataforma.')
                    
                    // --- CORREÇÃO: Mapeado com a rota correta do teu web.php ---
                    ->action('Ver Outros Eventos', route('eventos.index'));
    }
}