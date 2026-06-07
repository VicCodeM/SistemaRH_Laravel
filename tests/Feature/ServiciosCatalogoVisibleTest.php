<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\CatalogoServicioRecurso;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiciosCatalogoVisibleTest extends TestCase
{
    use RefreshDatabase;

    private function empresaActiva(): array
    {
        $usuario = User::factory()->create([
            'rol' => 'empresa',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $empresa = Empresa::create([
            'usuario_id' => $usuario->id,
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

        return [$usuario, $empresa];
    }

    private function candidatoAprobado(): array
    {
        $usuario = User::factory()->create([
            'rol' => 'candidato',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $candidato = Candidato::create([
            'usuario_id' => $usuario->id,
            'nombre' => $usuario->name,
            'municipio' => 'Monterrey',
            'solicitud_estado' => 'aprobada',
        ]);

        return [$usuario, $candidato];
    }

    private function crearServicio(array $atributos = []): CatalogoServicio
    {
        return CatalogoServicio::create(array_merge([
            'nombre' => 'Servicio de prueba',
            'descripcion' => 'Descripcion del servicio.',
            'tipo' => 'capacitacion',
            'flujo' => 'servicio',
            'nivel_jerarquico' => 'todos',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 10,
        ], $atributos));
    }

    private function crearRecursoPresentacion(CatalogoServicio $catalogo, User $usuario): CatalogoServicioRecurso
    {
        Storage::fake('public');
        $ruta = 'catalogos/' . $catalogo->id . '/recursos/presentacion.png';
        Storage::disk('public')->put($ruta, 'contenido de prueba');

        return CatalogoServicioRecurso::create([
            'catalogo_servicio_id' => $catalogo->id,
            'user_id' => $usuario->id,
            'tipo' => 'presentacion',
            'titulo' => 'Presentacion principal',
            'descripcion' => 'Material principal del servicio.',
            'archivo_path' => $ruta,
            'archivo_original' => 'presentacion.png',
            'mime_type' => 'image/png',
            'tamano_bytes' => 1234,
            'orden' => 1,
        ]);
    }

    public function test_la_empresa_solo_ve_servicios_activos_para_empresa_y_ambos(): void
    {
        [$usuario] = $this->empresaActiva();

        $visibleEmpresa = $this->crearServicio([
            'nombre' => 'Servicio activo empresa',
            'para_quien' => 'empresa',
            'activo' => true,
        ]);

        $visibleAmbos = $this->crearServicio([
            'nombre' => 'Servicio activo ambos',
            'para_quien' => 'ambos',
            'activo' => true,
            'orden' => 20,
        ]);

        $this->crearServicio([
            'nombre' => 'Servicio inactivo empresa',
            'para_quien' => 'empresa',
            'activo' => false,
            'orden' => 30,
        ]);

        $this->crearServicio([
            'nombre' => 'Servicio solo candidato',
            'para_quien' => 'candidato',
            'activo' => true,
            'orden' => 40,
        ]);

        $vacante = $this->crearServicio([
            'nombre' => 'Reclutamiento especializado',
            'tipo' => 'reclutamiento',
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 50,
        ]);

        $response = $this->actingAs($usuario)->get(route('empresa.servicios.index'));

        $response->assertOk();
        $response->assertSee('Servicios disponibles');
        $response->assertSee($visibleEmpresa->nombre);
        $response->assertSee($visibleAmbos->nombre);
        $response->assertSee($vacante->nombre);
        $response->assertDontSee('Servicios solicitados');
        $response->assertDontSee('Servicio inactivo empresa');
        $response->assertDontSee('Servicio solo candidato');
    }

    public function test_el_candidato_solo_ve_servicios_activos_para_candidato_y_nunca_flujos_de_vacante(): void
    {
        [$usuario] = $this->candidatoAprobado();

        $visibleCandidato = $this->crearServicio([
            'nombre' => 'Servicio activo candidato',
            'para_quien' => 'candidato',
            'activo' => true,
        ]);

        $visibleAmbos = $this->crearServicio([
            'nombre' => 'Servicio activo ambos',
            'para_quien' => 'ambos',
            'activo' => true,
            'orden' => 20,
        ]);

        $this->crearServicio([
            'nombre' => 'Servicio inactivo candidato',
            'para_quien' => 'candidato',
            'activo' => false,
            'orden' => 30,
        ]);

        $this->crearServicio([
            'nombre' => 'Servicio solo empresa',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 40,
        ]);

        $this->crearServicio([
            'nombre' => 'Vacante para empresa',
            'tipo' => 'reclutamiento',
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
            'activo' => true,
            'orden' => 50,
        ]);

        $response = $this->actingAs($usuario)->get(route('candidato.servicios.index'));

        $response->assertOk();
        $response->assertSee('Servicios disponibles');
        $response->assertSee($visibleCandidato->nombre);
        $response->assertSee($visibleAmbos->nombre);
        $response->assertDontSee('Mis servicios');
        $response->assertDontSee('Servicio inactivo candidato');
        $response->assertDontSee('Servicio solo empresa');
        $response->assertDontSee('Vacante para empresa');
    }

    public function test_el_detalle_de_servicio_normal_para_empresa_muestra_presentacion_y_formulario(): void
    {
        [$usuario] = $this->empresaActiva();

        $servicio = $this->crearServicio([
            'nombre' => 'Servicio activo para detalle',
            'descripcion' => 'Detalle completo del servicio.',
            'para_quien' => 'empresa',
            'flujo' => 'servicio',
            'nivel_jerarquico' => 'operativo',
        ]);

        $this->crearRecursoPresentacion($servicio, $usuario);

        $response = $this->actingAs($usuario)
            ->get(route('empresa.servicios.crear', ['servicio_id' => $servicio->id]));

        $response->assertOk();
        $response->assertSee('Presentacion del servicio');
        $response->assertSee('Presentacion principal');
        $response->assertSee('Solicitar servicio');
        $response->assertDontSee('Solicitar vacante');
    }

    public function test_el_detalle_de_flujo_vacante_para_empresa_muestra_boton_hacia_vacantes(): void
    {
        [$usuario] = $this->empresaActiva();

        $servicio = $this->crearServicio([
            'nombre' => 'Reclutamiento ejecutivo',
            'tipo' => 'reclutamiento',
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
            'nivel_jerarquico' => 'todos',
        ]);

        $this->crearRecursoPresentacion($servicio, $usuario);

        $response = $this->actingAs($usuario)
            ->get(route('empresa.servicios.crear', ['servicio_id' => $servicio->id]));

        $response->assertOk();
        $response->assertSee('Presentacion del servicio');
        $response->assertSee('Solicitar vacante');
        $response->assertDontSee('Solicitar servicio');
        $response->assertSee(route('empresa.solicitudes.crear', ['catalogo_servicio' => $servicio->id]), false);
    }

    public function test_el_formulario_de_vacante_recibe_el_contexto_del_catalogo(): void
    {
        [$usuario] = $this->empresaActiva();

        $servicio = $this->crearServicio([
            'nombre' => 'Reclutamiento operativo',
            'tipo' => 'reclutamiento',
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('empresa.solicitudes.crear', ['catalogo_servicio' => $servicio->id]));

        $response->assertOk();
        $response->assertSee('Servicio seleccionado');
        $response->assertSee($servicio->nombre);
        $response->assertSee('catalogo_servicio_id', false);
    }

    public function test_el_candidato_no_puede_abrir_un_servicio_de_flujo_vacante(): void
    {
        [$usuario] = $this->candidatoAprobado();

        $servicio = $this->crearServicio([
            'nombre' => 'Vacante oculta',
            'tipo' => 'reclutamiento',
            'flujo' => 'vacante',
            'para_quien' => 'empresa',
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('candidato.servicios.crear', ['servicio_id' => $servicio->id]));

        $response->assertRedirect(route('candidato.servicios.index'));
        $response->assertSessionHas('error', 'Este servicio no esta disponible en este momento.');
    }
}
