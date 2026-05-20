<?php

namespace App\Notifications;

use App\Models\ServicioAsignado;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica a un interno cuando se le asigna un nuevo pedido de servicio.
 */
class ServicioAsignadoAlInterno extends Notification
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
        $nombre   = $this->servicio->servicio?->nombre ?? 'Pedido de servicio';
        $solicita = $this->servicio->asignableNombre();
        $horas    = (int) $this->servicio->horas_estimadas;

        $mail = (new MailMessage)
            ->subject("Te asignaron: {$nombre}")
            ->greeting("Hola {$notifiable->name}")
            ->line("Tienes un nuevo pedido asignado: **{$nombre}**.")
            ->line("Solicitado por: **{$solicita}**");

        if ($horas > 0) {
            $mail->line("Horas estimadas: {$horas} h");
        }

        return $mail
            ->action('Ver mi tarea', route('interno.tareas.index'))
            ->line('Entra al sistema para iniciar el trabajo cuando estés listo.');
    }
}
