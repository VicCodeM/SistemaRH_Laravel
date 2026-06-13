<?php

namespace App\Notifications;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al candidato cuando su postulacion llega a un estado final relevante.
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
        $estadoLabel = Postulacion::estadoLabel($this->postulacion->estado);
        $esBuena = Postulacion::estadoOcupaCupo($this->postulacion->estado);
        $nombre = trim((string) ($notifiable->name ?? ''));

        $mail = (new MailMessage)
            ->subject($esBuena
                ? "Buenas noticias sobre tu proceso para {$titulo}"
                : "Actualización sobre tu postulacion a {$titulo}")
            ->greeting($nombre !== '' ? "Hola {$nombre}" : 'Hola');

        if ($esBuena) {
            $mail->line("Tenemos buenas noticias: tu proceso con **{$empresa}** para **{$titulo}** avanzó a **{$estadoLabel}**.")
                ->line('En breve podrían contactarte para continuar con los siguientes pasos.')
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
