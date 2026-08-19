<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoasVindasNotification extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo à nossa Plataforma! 🎉')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('A tua conta foi criada com sucesso no sistema de Gestão de Atividades.')
            ->line('Agora já podes inscrever-te nos melhores eventos de Tecnologia e Programação.')
            ->action('Aceder à Minha Conta', route('login'))
            ->line('Se tiveres alguma dúvida, responde diretamente a este email. Estamos aqui para ajudar!')
            ->salutation('Cumprimentos, ' . config('app.name'));
    }
}