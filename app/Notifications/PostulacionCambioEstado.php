<?php

namespace App\Notifications;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al candidato cuando su postulacion cambia a seleccionado o rechazado.
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
        $vacante = $this->postulacion->vacante;
        $empresa = $vacante?->empresa?->nombre_empresa ?? 'la empresa';
        $titulo = $vacante?->titulo ?? 'la vacante';
        $esBuena = $this->postulacion->estado === 'seleccionado';
        $nombre = trim((string) ($notifiable->name ?? ''));

        $mail = (new MailMessage)
            ->subject($esBuena
                ? "¡Buenas noticias! Fuiste seleccionado para {$titulo}"
                : "Actualización sobre tu postulacion a {$titulo}")
            ->greeting($nombre !== '' ? "Hola {$nombre}" : 'Hola');

        if ($esBuena) {
            $mail->line("Tenemos buenas noticias: **{$empresa}** te seleccionó para el puesto de **{$titulo}**.")
                ->line('En breve podrían contactarte para continuar con el proceso.')
                ->action('Ver mi postulacion', route('candidato.postulaciones'))
                ->line('Gracias por confiar en SistemaRH para tu búsqueda laboral.')
                ->line('Te deseamos mucho éxito en esta nueva etapa.');
        } else {
            $mail->line("Te informamos que **{$empresa}** decidió no continuar con tu postulacion a **{$titulo}** en esta ocasión.")
                ->line('Sabemos que cada proceso es importante, así que te invitamos a seguir explorando nuevas vacantes activas.')
                ->action('Ver vacantes disponibles', route('candidato.vacantes'))
                ->line('Tu postulacion queda registrada en el sistema para futuras consultas.')
                ->line('Te deseamos éxito en tus próximas oportunidades.');
        }

        return $mail->salutation('Saludos,');
    }
}
