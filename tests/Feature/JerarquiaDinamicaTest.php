<?php

namespace Tests\Feature;

use App\Models\CatalogoOpcion;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JerarquiaDinamicaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'estado' => 'activo']);
    }

    private function empresaActiva(): Empresa
    {
        $usuario = User::factory()->create(['rol' => 'empresa', 'estado' => 'activo']);

        return Empresa::create([
            'usuario_id' => $usuario->id,
            'nombre_empresa' => 'Empresa Residente SA de CV',
            'rfc' => 'ERS260605AA1',
            'telefono' => '8112345678',
            'direccion' => 'Av. Principal 123',
            'descripcion' => 'Empresa de prueba',
            'estado' => 'activa',
            'razon_social' => 'Empresa Residente SA de CV',
            'nombre_rh' => 'Victor',
            'telefono_directo' => '8112345678',
            'ciudad' => 'Monterrey',
            'municipio' => 'Monterrey',
            'codigo_postal' => '64000',
            'pagina_web' => 'https://example.com',
        ]);
    }

    private function crearOpcionResidente(): void
    {
        CatalogoOpcion::create([
            'grupo' => 'niveles_jerarquicos',
            'clave' => 'residente',
            'valor' => 'Residente',
            'descripcion' => 'Jerarquia nueva para vacantes y servicios',
            'activo' => true,
            'orden' => 15,
            'es_sistema' => false,
        ]);
    }

    public function test_la_jerarquia_residente_aparece_en_el_formulario_y_permite_crear_vacante(): void
    {
        $this->crearOpcionResidente();
        $empresa = $this->empresaActiva();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.vacantes.crear'));

        $response->assertOk();
        $response->assertSee('Residente');
        $response->assertSee($empresa->nombre_empresa);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.vacantes.guardar'), [
                'empresa_id' => $empresa->id,
                'titulo' => 'Vacante residente',
                'nivel_jerarquico' => 'residente',
            ]);

        $response->assertRedirect(route('admin.vacantes'));
        $response->assertSessionHas('success', 'Vacante creada y activada.');

        $this->assertDatabaseHas('vacantes', [
            'empresa_id' => $empresa->id,
            'titulo' => 'Vacante residente',
            'nivel_jerarquico' => 'residente',
            'estado' => 'activa',
        ]);
    }

    public function test_el_admin_puede_crear_una_vacante_como_pendiente_desde_el_formulario(): void
    {
        $this->crearOpcionResidente();
        $empresa = $this->empresaActiva();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.vacantes.guardar'), [
                'empresa_id' => $empresa->id,
                'titulo' => 'Vacante pendiente',
                'nivel_jerarquico' => 'residente',
                'estado' => 'pendiente',
            ]);

        $response->assertRedirect(route('admin.vacantes'));
        $response->assertSessionHas('success', 'Vacante creada como pendiente.');

        $this->assertDatabaseHas('vacantes', [
            'empresa_id' => $empresa->id,
            'titulo' => 'Vacante pendiente',
            'nivel_jerarquico' => 'residente',
            'estado' => 'pendiente',
        ]);
    }

    public function test_el_catalogo_de_servicios_acepta_una_jerarquia_dinamica_nueva(): void
    {
        $this->crearOpcionResidente();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.catalogo.store'), [
                'nombre' => 'Coaching residente',
                'descripcion' => 'Servicio de prueba',
                'tipo' => 'coaching',
                'flujo' => 'servicio',
                'nivel_jerarquico' => 'residente',
                'para_quien' => 'empresa',
                'activo' => 1,
                'orden' => 99,
            ]);

        $servicio = CatalogoServicio::where('nombre', 'Coaching residente')->firstOrFail();

        $response->assertRedirect(route('admin.catalogo.edit', $servicio));
        $response->assertSessionHas('success', 'Servicio creado correctamente. Ahora puedes agregar su presentacion.');

        $this->assertDatabaseHas('catalogo_servicios', [
            'nombre' => 'Coaching residente',
            'nivel_jerarquico' => 'residente',
        ]);
    }
}
