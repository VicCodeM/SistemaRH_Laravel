<?php

namespace App\Notifications;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al candidato cuando su postulación cambia a 'seleccionado' o 'rechazado'.
 */
class PostulacionCambioEstado extends Notification
{
    use Queueable;

    public function __construct(public Postulacion $postulacion)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vacante  = $this->postulacion->vacante;
        $empresa  = $vacante?->empresa?->nombre_empresa ?? 'la empresa';
        $titulo   = $vacante?->titulo ?? 'la vacante';
        $esBuena  = $this->postulacion->estado === 'seleccionado';

        $mail = (new MailMessage)
            ->subject($esBuena
                ? "¡Felicidades! Fuiste seleccionado para {$titulo}"
                : "Actualización sobre tu postulación a {$titulo}");

        if ($esBuena) {
            $mail->greeting("¡Hola {$notifiable->name}!")
                ->line("Tenemos buenas noticias. **{$empresa}** te seleccionó para el puesto de **{$titulo}**.")
                ->line('El siguiente paso es esperar a que la empresa te contacte para coordinar.')
                ->action('Ver mi postulación', route('candidato.postulaciones'))
                ->line('Mucho éxito en esta nueva etapa.');
        } else {
            $mail->greeting("Hola {$notifiable->name}")
                ->line("Queremos avisarte que **{$empresa}** decidió no continuar con tu postulación a **{$titulo}** en esta ocasión.")
                ->line('No te desanimes. Sigue explorando otras vacantes activas que pueden ser para ti.')
                ->action('Ver vacantes disponibles', route('candidato.vacantes'));
        }

        return $mail;
    }
}
