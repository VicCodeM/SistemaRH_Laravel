<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_email_with_spaces_and_uppercase(): void
    {
        $user = User::factory()->create([
            'email' => 'victor@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => '  VICTOR@EXAMPLE.COM  ',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unverified_users_are_redirected_to_verification_notice_when_required(): void
    {
        config()->set('auth.require_email_verification', true);

        $user = User::factory()->unverified()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $this->get(route('dashboard', absolute: false))
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_login_con_sesiones_activas_solicita_confirmacion(): void
    {
        config()->set('session.driver', 'database');
        config()->set('session.table', 'sessions');

        $user = User::factory()->create([
            'remember_token' => 'token-viejo',
        ]);

        DB::table('sessions')->insert([
            'id' => 'sesion-antigua',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'payload' => 'old-session-payload',
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('sesiones.confirmar-cierre', absolute: false));
        $this->assertDatabaseHas('sessions', [
            'id' => 'sesion-antigua',
        ]);
        $this->assertSame('token-viejo', $user->fresh()->remember_token);

        $this->get(route('sesiones.confirmar-cierre', absolute: false))
            ->assertOk()
            ->assertSee('127.0.0.1')
            ->assertSee('Chrome')
            ->assertSee('Windows');
    }

    public function test_usuario_puede_cerrar_las_otras_sesiones_desde_la_confirmacion(): void
    {
        config()->set('session.driver', 'database');
        config()->set('session.table', 'sessions');

        $user = User::factory()->create([
            'remember_token' => 'token-viejo',
        ]);

        DB::table('sessions')->insert([
            'id' => 'sesion-antigua',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'payload' => 'old-session-payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ])->assertRedirect(route('sesiones.confirmar-cierre', absolute: false));

        $response = $this->post(route('sesiones.cerrar-otras', absolute: false));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseMissing('sessions', [
            'id' => 'sesion-antigua',
        ]);
        $this->assertNotSame('token-viejo', $user->fresh()->remember_token);
    }
}
