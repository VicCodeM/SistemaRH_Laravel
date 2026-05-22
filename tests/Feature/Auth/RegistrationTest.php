<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'acepta_terminos' => '1',
        ]);

        $this->assertAuthenticated();
        // El registro público crea un candidato y lo lleva a completar su solicitud.
        $response->assertRedirect(route('candidato.solicitud', absolute: false));
    }

    public function test_registration_requires_accepting_terms(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'sinacepta@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            // sin acepta_terminos
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('acepta_terminos');
    }
}
