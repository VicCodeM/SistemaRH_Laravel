<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\ConfiguracionSistema;
use App\Models\User;
use App\Services\AccesoMunicipioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccesoMunicipioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'estado' => 'activo']);
    }

    public function test_admin_puede_configurar_uno_o_varios_municipios(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('admin.configuracion', ['tab' => 'parametros']))
            ->post(route('admin.configuracion.parametros.guardar'), [
                'candidato_requiere_aprobacion' => '0',
                'acceso_municipios_todos' => '0',
                'acceso_municipios_permitidos' => "Monterrey\nGuadalupe\nMonterrey\nApodaca",
            ]);

        $response->assertRedirect(route('admin.configuracion', ['tab' => 'parametros']));

        $this->assertFalse(ConfiguracionSistema::boolean(AccesoMunicipioService::CLAVE_TODOS, true));
        $this->assertSame(
            ['Monterrey', 'Guadalupe', 'Apodaca'],
            ConfiguracionSistema::arreglo(AccesoMunicipioService::CLAVE_PERMITIDOS)
        );
    }

    public function test_login_se_bloquea_si_el_municipio_no_esta_permitido(): void
    {
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_TODOS, false, ['grupo' => 'accesos', 'tipo' => 'boolean']);
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_PERMITIDOS, ['Guadalupe'], ['grupo' => 'accesos', 'tipo' => 'json']);

        $user = User::factory()->create([
            'rol' => 'candidato',
            'password' => Hash::make('password'),
        ]);

        Candidato::create([
            'usuario_id' => $user->id,
            'nombre' => $user->name,
            'municipio' => 'Monterrey',
            'solicitud_estado' => 'borrador',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('email', $user->email);
    }

    public function test_candidato_no_puede_registrarse_con_un_municipio_no_permitido(): void
    {
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_TODOS, false, ['grupo' => 'accesos', 'tipo' => 'boolean']);
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_PERMITIDOS, ['Guadalupe'], ['grupo' => 'accesos', 'tipo' => 'json']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'candidato-bloqueado@example.com',
            'municipio' => 'Monterrey',
            'password' => 'password',
            'password_confirmation' => 'password',
            'acepta_terminos' => '1',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('municipio');
        $this->assertDatabaseMissing('users', [
            'email' => 'candidato-bloqueado@example.com',
        ]);
    }

    public function test_usuario_que_ya_entro_es_bloqueado_si_su_municipio_deja_de_estar_permitido(): void
    {
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_TODOS, true, ['grupo' => 'accesos', 'tipo' => 'boolean']);

        $user = User::factory()->create([
            'rol' => 'candidato',
            'password' => Hash::make('password'),
        ]);

        Candidato::create([
            'usuario_id' => $user->id,
            'nombre' => $user->name,
            'municipio' => 'Monterrey',
            'solicitud_estado' => 'borrador',
        ]);

        $this->actingAs($user);

        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_TODOS, false, ['grupo' => 'accesos', 'tipo' => 'boolean']);
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_PERMITIDOS, ['Guadalupe'], ['grupo' => 'accesos', 'tipo' => 'json']);

        $this->actingAs($user)
            ->get(route('candidato.dashboard'))
            ->assertForbidden();
    }
}
