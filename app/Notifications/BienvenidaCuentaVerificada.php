<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BienvenidaCuentaVerificada extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $panelUrl = route('dashboard');
        $rol = (string) ($notifiable->rol ?? '');
        $nombre = trim((string) ($notifiable->name ?? '')) ?: 'Hola';

        $mensajesPorRol = [
            'admin' => 'Como administrador, ya puedes gestionar usuarios, vacantes, catálogos, aprobaciones y reportes desde un solo panel.',
            'empresa' => 'Desde este momento puedes publicar solicitudes, dar seguimiento a tus vacantes y revisar el avance de tus candidatos.',
            'candidato' => 'Ya puedes completar tu solicitud, explorar vacantes activas y consultar el estado de tus postulaciones en tiempo real.',
            'interno' => 'Tu acceso ya está listo para revisar y tomar las tareas asignadas, manteniendo el flujo operativo al día.',
        ];

        $accionesPorRol = [
            'admin' => 'Ir al panel de administración',
            'empresa' => 'Ir a mi panel',
            'candidato' => 'Ver mis vacantes',
            'interno' => 'Ver mis tareas',
        ];

        $mensajePrincipal = $mensajesPorRol[$rol] ?? 'Tu acceso ya está listo para comenzar a usar SistemaRH con normalidad.';
        $accionPrincipal = $accionesPorRol[$rol] ?? 'Ir al sistema';

        return (new MailMessage)
            ->subject('Bienvenido a SistemaRH: tu cuenta ya está lista')
            ->greeting("Hola {$nombre}")
            ->line('Tu correo fue verificado correctamente y tu cuenta ya quedó lista para usarse.')
            ->line($mensajePrincipal)
            ->when(
                ($notifiable->estado ?? null) !== 'activo',
                fn (MailMessage $mail) => $mail->line('Tu acceso sigue en revisión administrativa. En cuanto se apruebe, se habilitarán todas las funciones disponibles.'),
            )
            ->action($accionPrincipal, $panelUrl)
            ->line('Te invitamos a entrar cuando quieras. Desde tu panel tendrás acceso directo a las funciones clave de tu perfil.')
            ->line('Si necesitas apoyo o tienes alguna duda, responde a este correo y con gusto te ayudamos.')
            ->salutation('Saludos,');
    }
}
