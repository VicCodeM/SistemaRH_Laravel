<?php

namespace App\Notifications;

use App\Models\ServicioAsignado;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al solicitante (empresa o candidato) cuando se completa su pedido.
 */
class ServicioCompletado extends Notification
{
    use Queueable;

    public function __construct(public ServicioAsignado $servicio)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nombre = $this->servicio->servicio?->nombre ?? 'Tu pedido';
        $quien  = $this->servicio->asignadoA?->name ?? 'el responsable';

        // El link cambia según el rol del solicitante
        $url = $notifiable->rol === 'empresa'
            ? route('empresa.servicios.index')
            : route('candidato.servicios.index');

        $mail = (new MailMessage)
            ->subject("Tu pedido se completó: {$nombre}")
            ->greeting("Hola {$notifiable->name}")
            ->line("Tu pedido **{$nombre}** fue completado por **{$quien}**.");

        if ($this->servicio->cierre_resumen) {
            $mail->line("Resumen final:")
                 ->line($this->servicio->cierre_resumen);
        }

        return $mail
            ->action('Ver detalle', $url)
            ->line('Gracias por confiar en nuestro equipo.');
    }
}
