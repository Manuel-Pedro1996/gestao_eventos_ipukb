<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class VerificarEmailNotification extends VerifyEmailBase
{
    use Queueable;

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirme o seu Endereço de E-mail')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Por favor, clica no botão abaixo para verificar o teu endereço de e-mail e ativar a tua conta.')
            ->action('Confirmar Endereço de E-mail', $verificationUrl)
            ->line('Se não criaste uma conta no sistema, nenhuma ação adicional é necessária.')
            ->salutation('Cumprimentos, ' . config('app.name'));
    }
}