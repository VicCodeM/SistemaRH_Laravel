<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresAt = now()->addMinutes((int) config(
            'auth.passwords.' . config('auth.defaults.passwords') . '.expire'
        ));

        $url = URL::temporarySignedRoute('password.reset', $expiresAt, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $nombre = trim((string) ($notifiable->name ?? ''));

        return (new MailMessage)
            ->subject('Restablece tu contraseña de SistemaRH')
            ->greeting($nombre !== '' ? "Hola {$nombre}" : 'Hola')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Crear nueva contraseña', $url)
            ->line('Este enlace expira en ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minutos.')
            ->line('Si no solicitaste este cambio, puedes ignorar este correo.')
            ->salutation('Saludos,');
    }
}
