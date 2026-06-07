<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        config()->set('auth.require_email_verification', false);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'municipio' => 'Monterrey',
            'password' => 'password',
            'password_confirmation' => 'password',
            'acepta_terminos' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('candidato.solicitud', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_new_users_receive_verification_email_when_required(): void
    {
        config()->set('auth.require_email_verification', true);
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'verify@example.com',
            'municipio' => 'Monterrey',
            'password' => 'password',
            'password_confirmation' => 'password',
            'acepta_terminos' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $response->assertSessionHas('status', 'Te enviamos un correo para verificar tu cuenta.');

        $user = User::where('email', 'verify@example.com')->firstOrFail();
        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_registration_requires_accepting_terms(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'sinacepta@example.com',
            'municipio' => 'Monterrey',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('acepta_terminos');
    }
}
