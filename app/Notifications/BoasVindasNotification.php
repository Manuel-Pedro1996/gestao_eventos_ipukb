<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class BoasVindasNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->subject('Bem-vindo à nossa Plataforma!')
                    ->line('A tua conta foi criada com sucesso no sistema de Gestão de Atividades.')
                    ->line('Agora já podes inscrever-te nos melhores eventos de Tecnologia e Programação.')
                    
                    // --- CORREÇÃO: Usar o nome correto da rota de Login ---
                    ->action('Aceder à Minha Conta', route('login'))
                    
                    ->line('Se tiveres alguma dúvida, responde diretamente a este email. Estamos aqui para ajudar!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
