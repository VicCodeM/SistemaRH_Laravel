<?php

namespace Tests\Feature;

use App\Models\CatalogoServicio;
use App\Models\CatalogoOpcion;
use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'estado' => 'activo']);
    }

    public function test_el_indice_de_catalogos_acepta_el_tab_legacy_opciones_sin_error(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.catalogos.index', ['tab' => 'opciones']));

        $response->assertOk();
        $response->assertSee('Personalizar el sistema');
    }

    public function test_el_formulario_de_catalogos_regresa_a_un_tab_valido_al_cancelar(): void
    {
        $catalogo = CatalogoOpcion::create([
            'grupo' => 'niveles_estudios',
            'clave' => 'universidad',
            'valor' => 'Universidad',
            'descripcion' => null,
            'activo' => true,
            'orden' => 10,
            'es_sistema' => false,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.catalogos.edit', $catalogo));

        $response->assertOk();
        $response->assertSee(route('admin.catalogos.index', ['tab' => 'vacantes']));
        $response->assertDontSee('tab=opciones');
    }

    public function test_el_indice_de_catalogos_muestra_el_tab_de_servicios_sin_error(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.catalogos.index', ['tab' => 'servicios']));

        $response->assertOk();
        $response->assertSee('Personalizar el sistema');
    }

    public function test_no_permite_desactivar_un_servicio_con_pedidos_activos_o_en_proceso(): void
    {
        $servicio = CatalogoServicio::create([
            'nombre' => 'Servicio con uso',
            'descripcion' => 'Servicio de prueba',
            'tipo' => 'coaching',
            'nivel_jerarquico' => 'operativo',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 10,
        ]);

        $solicitante = User::factory()->create(['rol' => 'empresa', 'estado' => 'activo']);
        $interno = User::factory()->create(['rol' => 'interno', 'estado' => 'activo']);

        ServicioAsignado::create([
            'servicio_id' => $servicio->id,
            'asignable_type' => User::class,
            'asignable_id' => $solicitante->id,
            'estado' => 'en_proceso',
            'asignado_a' => $interno->id,
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.catalogo.index'))
            ->patch(route('admin.catalogo.toggle', $servicio));

        $response->assertRedirect(route('admin.catalogo.index'));
        $response->assertSessionHas('error', 'No se puede desactivar este servicio porque ya tiene pedidos activos o en proceso.');

        $this->assertDatabaseHas('catalogo_servicios', [
            'id' => $servicio->id,
            'activo' => 1,
        ]);
    }

    public function test_si_permite_desactivar_un_servicio_sin_pedidos_activos(): void
    {
        $servicio = CatalogoServicio::create([
            'nombre' => 'Servicio libre',
            'descripcion' => 'Sin uso',
            'tipo' => 'coaching',
            'nivel_jerarquico' => 'operativo',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 20,
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.catalogo.index'))
            ->patch(route('admin.catalogo.toggle', $servicio));

        $response->assertRedirect(route('admin.catalogo.index'));
        $response->assertSessionHas('success', 'Servicio desactivado.');

        $this->assertDatabaseHas('catalogo_servicios', [
            'id' => $servicio->id,
            'activo' => 0,
        ]);
    }

    public function test_un_servicio_de_flujo_vacante_se_guarda_como_empresa_y_nivel_todos(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.catalogo.store'), [
                'nombre' => 'Reclutamiento ejecutivo',
                'descripcion' => 'Flujo de vacante desde catalogo',
                'tipo' => 'reclutamiento',
                'flujo' => 'vacante',
                'nivel_jerarquico' => 'operativo',
                'para_quien' => 'ambos',
                'activo' => 1,
                'orden' => 30,
            ]);

        $servicio = CatalogoServicio::where('nombre', 'Reclutamiento ejecutivo')->firstOrFail();

        $response->assertRedirect(route('admin.catalogo.edit', $servicio));

        $this->assertDatabaseHas('catalogo_servicios', [
            'id' => $servicio->id,
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
            'nivel_jerarquico' => 'todos',
            'activo' => 1,
        ]);
    }
}
