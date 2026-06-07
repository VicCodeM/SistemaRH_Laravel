<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->get($this->resetPasswordUrl($notification->token, $user->email));

            $response->assertOk();

            return true;
        });
    }

    public function test_reset_password_screen_rejects_tampered_email(): void
    {
        $user = User::factory()->create();

        $url = $this->resetPasswordUrl('token-falso', $user->email);
        $tamperedUrl = str_replace(urlencode($user->email), urlencode('otro-correo@ejemplo.com'), $url);

        $this->get($tamperedUrl)
            ->assertForbidden()
            ->assertSee('El enlace de recuperación no es válido o ya expiró.')
            ->assertDontSee('class="sidebar"');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    protected function resetPasswordUrl(string $token, string $email): string
    {
        return URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes((int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')),
            [
                'token' => $token,
                'email' => $email,
            ]
        );
    }
}
