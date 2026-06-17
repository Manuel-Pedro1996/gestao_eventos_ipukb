<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class InscricaoConfirmadaNotification extends Notification implements ShouldQueue
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

        // Gerar a estrutura SVG do QR Code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrContent);

        // Converter o SVG para Base64 para poder injetar direto no HTML do e-mail
        $base64Svg = base64_encode($qrCodeSvg);

        return (new MailMessage)
                    ->subject('Inscrição Confirmada!')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('A tua inscrição no evento "' . $this->evento->titulo . '" foi realizada com sucesso.')
                    ->line('Data do Evento: ' . Carbon::parse($this->evento->data_evento)->format('d/m/Y H:i'))
                    
                    // --- SEGURANÇA: CÓDIGO EM TEXTO LIMPO ---
                    ->line(new HtmlString('<strong>Código de Validação:</strong> <code style="background:#f4f4f5; padding:4px 8px; border-radius:4px; font-size:16px; color:#1e40af;">' . $this->inscricao->codigo_qr . '</code>'))
                    
                    ->line('Apresenta o código acima ou o QR Code abaixo à entrada do evento para validar a tua presença.')
                    
                    // --- EXIBIÇÃO VISUAL DO QR CODE DIRECTO NO CORPO ---
                    ->line(new HtmlString('<div style="text-align:center; margin:20px 0;"><img src="data:image/svg+xml;base64,' . $base64Svg . '" alt="QR Code de Inscrição" width="200" height="200" style="border:1px solid #e4e4e7; padding:10px; background:#fff; border-radius:8px;"/></div>'))
                    
                    ->action('Ver Detalhes do Evento', url('/eventos/' . $this->evento->id))
                    
                    // Mantém também o anexo como plano B
                    ->attachData($qrCodeSvg, 'ticket-qrcode.svg', [
                        'mime' => 'image/svg+xml',
                    ]);
    }
}