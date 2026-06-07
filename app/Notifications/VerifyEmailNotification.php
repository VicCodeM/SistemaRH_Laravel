<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $nombre = trim((string) ($notifiable->name ?? ''));

        return (new MailMessage)
            ->subject('Verifica tu correo electrónico para continuar en SistemaRH')
            ->greeting($nombre !== '' ? "Hola {$nombre}" : 'Hola')
            ->line('Para proteger tu cuenta y completar tu registro, solo falta confirmar tu correo electrónico.')
            ->action('Verificar correo electrónico', $verificationUrl)
            ->line('Este enlace expira en ' . config('auth.verification.expire', 60) . ' minutos.')
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos,');
    }
}
