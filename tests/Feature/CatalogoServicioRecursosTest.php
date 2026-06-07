<?php

namespace Tests\Feature;

use App\Models\CatalogoServicio;
use App\Models\CatalogoServicioRecurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogoServicioRecursosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'rol' => 'admin',
            'estado' => 'activo',
        ]);
    }

    private function catalogoBase(): CatalogoServicio
    {
        return CatalogoServicio::create([
            'nombre' => 'Servicio base',
            'descripcion' => 'Servicio de prueba.',
            'tipo' => 'capacitacion',
            'flujo' => 'servicio',
            'nivel_jerarquico' => 'operativo',
            'para_quien' => 'ambos',
            'activo' => true,
            'orden' => 1,
        ]);
    }

    public function test_el_admin_puede_agregar_una_diapositiva_de_imagen_al_catalogo_del_servicio(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $catalogo = $this->catalogoBase();

        $response = $this->actingAs($admin)
            ->from(route('admin.catalogo.edit', $catalogo))
            ->post(route('admin.catalogo.recursos.store', $catalogo), [
                'titulo' => 'Presentacion comercial',
                'tipo' => 'presentacion',
                'modo_carga' => 'archivo',
                'archivos' => [
                    UploadedFile::fake()->image('portada.png', 1200, 800),
                ],
            ]);

        $response->assertRedirect(route('admin.catalogo.edit', $catalogo));
        $response->assertSessionHas('success', 'Diapositiva agregada correctamente.');

        $recurso = CatalogoServicioRecurso::firstOrFail();

        $this->assertSame($catalogo->id, $recurso->catalogo_servicio_id);
        $this->assertSame('presentacion', $recurso->tipo);
        $this->assertSame('Presentacion comercial', $recurso->titulo);
        Storage::disk('public')->assertExists($recurso->archivo_path);
    }

    public function test_el_admin_puede_usar_el_nombre_del_archivo_si_no_escribe_titulo_base(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $catalogo = $this->catalogoBase();

        $response = $this->actingAs($admin)
            ->from(route('admin.catalogo.edit', $catalogo))
            ->post(route('admin.catalogo.recursos.store', $catalogo), [
                'tipo' => 'presentacion',
                'modo_carga' => 'archivo',
                'archivos' => [
                    UploadedFile::fake()->image('equipo-rh.png', 1200, 800),
                ],
                'orden' => 3,
            ]);

        $response->assertRedirect(route('admin.catalogo.edit', $catalogo));
        $response->assertSessionHas('success', 'Diapositiva agregada correctamente.');

        $recurso = CatalogoServicioRecurso::firstOrFail();

        $this->assertSame(3, $recurso->orden);
        $this->assertSame('Equipo Rh', $recurso->titulo);
        $this->assertStringStartsWith('image/', (string) $recurso->mime_type);
    }

    public function test_el_admin_puede_subir_varias_imagenes_en_una_sola_carga(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $catalogo = $this->catalogoBase();

        $response = $this->actingAs($admin)
            ->from(route('admin.catalogo.edit', $catalogo))
            ->post(route('admin.catalogo.recursos.store', $catalogo), [
                'titulo' => 'Secuencia visual',
                'tipo' => 'presentacion',
                'modo_carga' => 'archivo',
                'descripcion' => 'Laminas de apoyo.',
                'orden' => 4,
                'archivos' => [
                    UploadedFile::fake()->image('portada.png', 1200, 800),
                    UploadedFile::fake()->image('temario.jpg', 1200, 800),
                ],
            ]);

        $response->assertRedirect(route('admin.catalogo.edit', $catalogo));
        $response->assertSessionHas('success', '2 diapositivas agregadas correctamente.');

        $this->assertDatabaseCount('catalogo_servicio_recursos', 2);

        $recursos = CatalogoServicioRecurso::query()
            ->orderBy('orden')
            ->get();

        $this->assertSame('Secuencia visual 1', $recursos[0]->titulo);
        $this->assertSame('Secuencia visual 2', $recursos[1]->titulo);
        $this->assertSame(4, $recursos[0]->orden);
        $this->assertSame(5, $recursos[1]->orden);

        Storage::disk('public')->assertExists($recursos[0]->archivo_path);
        Storage::disk('public')->assertExists($recursos[1]->archivo_path);
    }

    public function test_el_admin_puede_editar_una_diapositiva_existente_y_cambiar_su_orden(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $catalogo = $this->catalogoBase();

        $recurso = CatalogoServicioRecurso::create([
            'catalogo_servicio_id' => $catalogo->id,
            'user_id' => $admin->id,
            'tipo' => 'presentacion',
            'titulo' => 'Portada inicial',
            'descripcion' => 'Version inicial.',
            'archivo_path' => 'catalogos/' . $catalogo->id . '/recursos/inicial.png',
            'archivo_original' => 'inicial.png',
            'mime_type' => 'image/png',
            'tamano_bytes' => 1024,
            'orden' => 1,
        ]);

        Storage::disk('public')->put($recurso->archivo_path, 'imagen');

        $response = $this->actingAs($admin)
            ->from(route('admin.catalogo.edit', $catalogo))
            ->patch(route('admin.catalogo.recursos.update', $recurso), [
                'titulo' => 'Portada final',
                'tipo' => 'presentacion',
                'modo_carga' => 'archivo',
                'descripcion' => 'Texto final de apoyo.',
                'orden' => 7,
            ]);

        $response->assertRedirect(route('admin.catalogo.edit', $catalogo));
        $response->assertSessionHas('success', 'Diapositiva actualizada correctamente.');

        $recurso->refresh();

        $this->assertSame('Portada final', $recurso->titulo);
        $this->assertSame('presentacion', $recurso->tipo);
        $this->assertSame(7, $recurso->orden);
        $this->assertSame('Texto final de apoyo.', $recurso->descripcion);
    }
}
