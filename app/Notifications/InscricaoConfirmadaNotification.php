<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
//use Illuminate\Contracts\Queue\ShouldQueue;
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

        // Gerar a estrutura SVG do QR Code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrContent);

        // Criamos o objeto MailMessage primeiro para podermos usar o método 'with' e embutir o anexo
        $mailMessage = (new MailMessage)
                    ->subject('Inscrição Confirmada!')
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('A tua inscrição no evento "' . $this->evento->titulo . '" foi realizada com sucesso.')
                    ->line('Data do Evento: ' . Carbon::parse($this->evento->data_evento)->format('d/m/Y H:i'))
                    
                    // --- SEGURANÇA: CÓDIGO EM TEXTO LIMPO ---
                    ->line(new HtmlString('<strong>Código de Validação:</strong> <code style="background:#f4f4f5; padding:4px 8px; border-radius:4px; font-size:16px; color:#1e40af;">' . $this->inscricao->codigo_qr . '</code>'))
                    
                    ->line('Apresenta o código acima ou o QR Code abaixo à entrada do evento para validar a tua presença.');

        // --- SOLUÇÃO DA IMAGEM PARA O GMAIL ---
        // Usamos uma função callback para embutir o SVG diretamente nos dados da mensagem através do CID seguro
        $mailMessage->with(['message' => function ($message) use ($qrCodeSvg, &$imageCid) {
            $imageCid = $message->embedData($qrCodeSvg, 'qrcode.svg', 'image/svg+xml');
        }]);

        // Injetamos a tag de imagem usando a variável $imageCid que o Laravel gerou automaticamente
        $mailMessage->line(new HtmlString('<div style="text-align:center; margin:20px 0;"><img src="' . $imageCid . '" alt="QR Code de Inscrição" width="200" height="200" style="border:1px solid #e4e4e7; padding:10px; background:#fff; border-radius:8px;"/></div>'));

        // --- SOLUÇÃO DO 404 (Garantir a Rota Certa) ---
        // IMPORTANTE: Altera 'eventos.show' abaixo para o nome real que deste à rota no teu web.php (ex: 'evento.show' ou 'eventos.detalhes')
        $mailMessage->action('Ver Detalhes do Evento', route('eventos.show', $this->evento->id))
                    
                    // Mantém também o anexo físico como plano B
                    ->attachData($qrCodeSvg, 'ticket-qrcode.svg', [
                        'mime' => 'image/svg+xml',
                    ]);

        return $mailMessage;
    }
}