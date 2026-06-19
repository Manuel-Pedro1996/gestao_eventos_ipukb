<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class InscricaoConfirmadaNotification extends Notification
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
        $qrContent = json_encode([
            'inscricao_id' => $this->inscricao->id,
            'user_id' => $notifiable->id,
            'evento_id' => $this->evento->id
        ]);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrContent);

        return (new MailMessage)
                    ->subject('Inscrição Confirmada! 🎉')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('A tua inscrição no evento "' . $this->evento->titulo . '" foi realizada com sucesso.')
                    ->line('Data do Evento: ' . Carbon::parse($this->evento->data_evento)->format('d/m/Y H:i'))
                    
                    ->line(new HtmlString('<strong>Código de Validação:</strong> <code style="background:#f4f4f5; padding:4px 8px; border-radius:4px; font-size:16px; color:#1e40af;">' . $this->inscricao->codigo_qr . '</code>'))
                    
                    ->line('Apresenta o código acima ou o QR Code em anexo à entrada do evento para validar a tua presença.')
                    ->action('Ver Detalhes do Evento', route('eventos.index')) // Rota segura e garantida do index
                    ->attachData($qrCodeSvg, 'ticket-qrcode.svg', [
                        'mime' => 'image/svg+xml',
                    ]);
    }
}