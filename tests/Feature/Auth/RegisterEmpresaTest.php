<?php

namespace Tests\Feature\Auth;

use App\Models\ConfiguracionSistema;
use App\Services\AccesoMunicipioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterEmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_no_puede_registrarse_con_un_municipio_no_permitido(): void
    {
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_TODOS, false, ['grupo' => 'accesos', 'tipo' => 'boolean']);
        ConfiguracionSistema::guardar(AccesoMunicipioService::CLAVE_PERMITIDOS, ['Guadalupe'], ['grupo' => 'accesos', 'tipo' => 'json']);

        $this->post(route('register.empresa'), [
            'name' => 'Responsable RH',
            'email' => 'empresa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('register.empresa', ['step' => 2]));

        $this->post(route('register.empresa'), [
            'step' => 2,
            'nombre_empresa' => 'Empresa Demo',
            'razon_social' => 'Empresa Demo SA de CV',
            'rfc' => 'DEM010101AA1',
            'giro_o_industria' => 'Servicios',
        ])->assertRedirect(route('register.empresa', ['step' => 3]));

        $response = $this->post(route('register.empresa'), [
            'step' => 3,
            'telefono' => '8180000000',
            'direccion' => 'Calle 1',
            'ciudad' => 'Monterrey',
            'municipio' => 'Monterrey',
            'codigo_postal' => '64000',
            'acepta_terminos' => '1',
        ]);

        $response->assertRedirect(route('register.empresa', ['step' => 3]));
        $response->assertSessionHas('error', 'Tu municipio no está autorizado para registrar la empresa.');

        $this->assertDatabaseMissing('users', [
            'email' => 'empresa@example.com',
        ]);

        $this->assertDatabaseMissing('empresas', [
            'rfc' => 'DEM010101AA1',
        ]);
    }
}
