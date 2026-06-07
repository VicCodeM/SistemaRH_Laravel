<?php

namespace Tests\Feature;

use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\ServicioRecurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicioRecursosTest extends TestCase
{
    use RefreshDatabase;

    private function crearServicioDeEmpresa(): array
    {
        $admin = User::factory()->create([
            'rol' => 'admin',
            'estado' => 'activo',
        ]);

        $empresaUsuario = User::factory()->create([
            'rol' => 'empresa',
            'estado' => 'activo',
        ]);

        $empresa = Empresa::create([
            'usuario_id' => $empresaUsuario->id,
            'nombre_empresa' => 'Tech Solutions SA de CV',
            'razon_social' => 'Tech Solutions SA de CV',
            'rfc' => 'TSO860501XX3',
            'telefono' => '8112345678',
            'direccion' => 'Av. Constitucion 100, Monterrey, NL',
            'descripcion' => 'Empresa de prueba',
            'estado' => 'activa',
            'nombre_rh' => 'Claudia Reyes',
            'telefono_directo' => '8112345600',
            'ciudad' => 'Monterrey',
            'municipio' => 'Monterrey',
            'codigo_postal' => '64000',
            'pagina_web' => 'https://techsolutions.test',
        ]);

        $servicio = CatalogoServicio::create([
            'nombre' => 'Capacitacion de induccion',
            'descripcion' => 'Presentacion general del proceso de induccion.',
            'tipo' => 'capacitacion',
            'nivel_jerarquico' => 'operativo',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 10,
        ]);

        $pedido = ServicioAsignado::create([
            'servicio_id' => $servicio->id,
            'asignable_type' => Empresa::class,
            'asignable_id' => $empresa->id,
            'estado' => 'activo',
            'notas' => 'Capacitacion inicial para el equipo.',
            'asignado_por' => $admin->id,
            'solicitado_por' => $empresaUsuario->id,
        ]);

        return [$admin, $empresaUsuario, $empresa, $pedido];
    }

    public function test_admin_puede_subir_un_recurso_al_servicio(): void
    {
        Storage::fake('public');

        [$admin, , , $pedido] = $this->crearServicioDeEmpresa();

        $response = $this->actingAs($admin)
            ->from(route('admin.tareas.show', $pedido))
            ->post(route('pedidos.recursos.store', $pedido), [
                'titulo' => 'Presentacion de induccion',
                'tipo' => 'presentacion',
                'descripcion' => 'Archivo principal para revisar el contenido.',
                'archivo' => UploadedFile::fake()->create('induccion.pdf', 180, 'application/pdf'),
            ]);

        $response->assertRedirect(route('admin.tareas.show', $pedido));
        $response->assertSessionHas('success', 'Archivo agregado correctamente.');

        $recurso = ServicioRecurso::firstOrFail();

        $this->assertSame($pedido->id, $recurso->servicio_asignado_id);
        $this->assertSame('presentacion', $recurso->tipo);
        $this->assertSame('Presentacion de induccion', $recurso->titulo);
        Storage::disk('public')->assertExists($recurso->archivo_path);
    }

    public function test_la_empresa_ve_el_bloque_de_recursos_en_el_detalle(): void
    {
        Storage::fake('public');

        [$admin, $empresaUsuario, , $pedido] = $this->crearServicioDeEmpresa();

        $this->actingAs($admin)
            ->from(route('admin.tareas.show', $pedido))
            ->post(route('pedidos.recursos.store', $pedido), [
                'titulo' => 'Presentacion de induccion',
                'tipo' => 'presentacion',
                'descripcion' => 'Archivo principal para revisar el contenido.',
                'archivo' => UploadedFile::fake()->create('induccion.pdf', 180, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.tareas.show', $pedido));

        $response = $this->actingAs($empresaUsuario)
            ->get(route('empresa.servicios.ver', $pedido));

        $response->assertOk();
        $response->assertSee('Archivos y presentaciones');
        $response->assertSee('Presentacion destacada');
        $response->assertSee('Presentacion de induccion');
        $response->assertDontSee('Guardar recurso');
        $response->assertDontSee('Descargar archivo');
    }

    public function test_una_empresa_no_puede_subir_recursos_manualmente(): void
    {
        Storage::fake('public');

        [, $empresaUsuario, , $pedido] = $this->crearServicioDeEmpresa();

        $response = $this->actingAs($empresaUsuario)
            ->post(route('pedidos.recursos.store', $pedido), [
                'titulo' => 'Intento no autorizado',
                'tipo' => 'archivo',
                'archivo' => UploadedFile::fake()->create('archivo.pdf', 120, 'application/pdf'),
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('servicio_recursos', [
            'titulo' => 'Intento no autorizado',
        ]);
    }
}
