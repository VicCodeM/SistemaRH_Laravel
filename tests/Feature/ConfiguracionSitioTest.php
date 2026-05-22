<?php

namespace Tests\Feature;

use App\Models\ConfiguracionSistema;
use App\Models\User;
use App\Services\SitioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfiguracionSitioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'estado' => 'activo']);
    }

    public function test_admin_puede_guardar_la_configuracion_del_sitio(): void
    {
        $datos = [
            'sitio_nombre'           => 'Mi Empresa RH',
            'sitio_subtitulo'        => 'Talento sin límites',
            'sitio_descripcion'      => 'La mejor plataforma de talento',
            'landing_hero_titulo'    => "Encuentra\ntu equipo",
            'landing_hero_acento'    => 'sin complicarte',
            'privacidad_contenido'   => 'Respetamos tu privacidad.',
            'terminos_contenido'     => 'Estos son los términos.',
        ];

        $response = $this->actingAs($this->admin())
            ->post(route('admin.configuracion.sitio.guardar'), $datos);

        $response->assertRedirect(route('admin.configuracion', ['tab' => 'sitio']));

        $this->assertSame('Mi Empresa RH', ConfiguracionSistema::texto('sitio_nombre'));
        $this->assertSame('Talento sin límites', ConfiguracionSistema::texto('sitio_subtitulo'));
        $this->assertSame('La mejor plataforma de talento', ConfiguracionSistema::texto('sitio_descripcion'));
        $this->assertSame("Encuentra\ntu equipo", ConfiguracionSistema::texto('landing_hero_titulo'));
        $this->assertSame('Respetamos tu privacidad.', ConfiguracionSistema::texto('privacidad_contenido'));
    }

    public function test_partir_marca_separa_el_acento_en_azul(): void
    {
        // Sin espacio: corta en la transición minúscula→Mayúscula
        $this->assertSame(['base' => 'Sistema', 'acento' => 'RH'], SitioService::partirMarca('SistemaRH'));
        // Con espacio: la última palabra es el acento
        $this->assertSame(['base' => 'Mi Empresa ', 'acento' => 'RH'], SitioService::partirMarca('Mi Empresa RH'));
        // Un solo bloque sin transición: todo base, acento vacío
        $this->assertSame(['base' => 'Acme', 'acento' => ''], SitioService::partirMarca('Acme'));
    }

    public function test_un_no_admin_no_puede_guardar_la_configuracion(): void
    {
        $candidato = User::factory()->create(['rol' => 'candidato', 'estado' => 'activo']);

        $response = $this->actingAs($candidato)
            ->post(route('admin.configuracion.sitio.guardar'), [
                'sitio_nombre'        => 'Hackeado',
                'landing_hero_titulo' => 'x',
            ]);

        $response->assertForbidden();
        $this->assertNull(ConfiguracionSistema::texto('sitio_nombre'));
    }

    public function test_el_nombre_y_titulo_son_obligatorios(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.configuracion.sitio.guardar'), [
                'sitio_nombre'        => '',
                'landing_hero_titulo' => '',
            ]);

        $response->assertSessionHasErrors(['sitio_nombre', 'landing_hero_titulo']);
    }

    public function test_admin_puede_subir_un_favicon(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.configuracion.sitio.guardar'), [
                'sitio_nombre'        => 'Con Favicon',
                'landing_hero_titulo' => 'Hola',
                'favicon'             => UploadedFile::fake()->image('logo.png', 64, 64),
            ]);

        $response->assertRedirect();

        $ruta = ConfiguracionSistema::texto('sitio_favicon');
        $this->assertNotEmpty($ruta);
        Storage::disk('public')->assertExists($ruta);
    }

    public function test_pagina_de_privacidad_es_publica_y_muestra_el_contenido(): void
    {
        ConfiguracionSistema::guardar('privacidad_contenido', 'Texto de privacidad de prueba', [
            'grupo' => 'sitio',
            'tipo'  => 'string',
        ]);

        $response = $this->get(route('paginas.privacidad'));

        $response->assertOk();
        $response->assertSee('Políticas de privacidad');
        $response->assertSee('Texto de privacidad de prueba');
    }

    public function test_pagina_de_terminos_es_publica(): void
    {
        $response = $this->get(route('paginas.terminos'));

        $response->assertOk();
        $response->assertSee('Términos del servicio');
    }
}
