<?php

namespace Database\Seeders;

use App\Models\CatalogoServicio;
use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\PersonalExterno;
use App\Models\Postulacion;
use App\Models\ServicioAsignado;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza total de tablas de demostración en orden para evitar fallas de llaves foráneas
        Postulacion::query()->delete();
        ServicioAsignado::query()->delete();
        Vacante::query()->delete();
        PersonalExterno::query()->delete();
        Empresa::query()->delete();
        Candidato::query()->delete();
        CatalogoServicio::query()->delete();
        
        // Eliminar todos los usuarios excepto el Administrador
        User::where('email', '!=', 'admin@sistemarh.com')->delete();

        $admin = User::where('email', 'admin@sistemarh.com')->firstOrFail();

        // 2. Crear exactamente 1 Personal Interno (RH)
        $interno = User::create([
            'name' => 'Interno Demo',
            'email' => 'interno1@sistemarh.com',
            'password' => Hash::make('password'),
            'rol' => 'interno',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        // 3. Crear exactamente 1 Empresa
        $empresaUser = User::create([
            'name' => 'Tech Solutions RH',
            'email' => 'empresa1@sistemarh.com',
            'password' => Hash::make('password'),
            'rol' => 'empresa',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $empresa = Empresa::create([
            'usuario_id' => $empresaUser->id,
            'nombre_empresa' => 'Tech Solutions SA de CV',
            'rfc' => 'TSO860501XX3',
            'telefono' => '8112345678',
            'direccion' => 'Av. Constitución 100, Monterrey, NL',
            'ciudad' => 'Monterrey',
            'estado' => 'activa',
        ]);

        // 4. Crear exactamente 1 Candidato
        $candidatoUser = User::create([
            'name' => 'Ana García',
            'email' => 'ana.garcia@sistemarh.com',
            'password' => Hash::make('password'),
            'rol' => 'candidato',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $candidato = Candidato::create([
            'usuario_id' => $candidatoUser->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'telefono' => '8125551001',
            'celular' => '8125551002',
            'ciudad' => 'Monterrey',
            'puesto_deseado' => 'Desarrolladora Web',
            'experiencia_anios' => 4,
            'escolaridad' => 'ingenieria',
            'habilidades' => 'Laravel, PHP, MySQL, Git, Vue, APIs',
            'solicitud_estado' => 'aprobada',
            'solicitud_enviada_at' => now()->subDays(2),
            'licencia_conducir' => [
                'tiene' => 'no',
                'clase' => '',
                'numero' => '',
                'vigencia' => '',
            ],
            'redes_sociales' => [
                'facebook' => '',
                'twitter' => '',
                'instagram' => '',
                'linkedin' => '',
            ],
            'escolaridad_detallada' => [
                [
                    'nivel' => 'ingenieria',
                    'nombre' => 'Instituto Tecnológico de Monterrey',
                    'anios' => '4',
                    'titulo' => 'Ingeniería en Sistemas Computacionales',
                ],
            ],
            'historial_laboral' => [
                [
                    'empresa' => 'Software Norte',
                    'puesto' => 'Desarrolladora PHP',
                    'jefe' => 'M. Ramírez',
                    'sueldo' => '21000',
                    'desde' => '2021-01-01',
                    'hasta' => '2024-01-01',
                    'motivo' => 'Crecimiento profesional',
                ],
            ],
            'referencias_personales' => [
                [
                    'nombre' => 'Referencia Demo',
                    'telefono' => '5550000000',
                    'ocupacion' => 'Supervisor',
                    'tiempo' => '2 años',
                    'domicilio' => 'Ciudad de México',
                ],
            ],
        ]);

        // 5. Crear Catálogo Maestro de Servicios
        $servicios = [
            [
                'nombre' => 'Reclutamiento ejecutivo',
                'descripcion' => 'Búsqueda de perfiles de gerencia y supervisión con filtros automáticos por estudios y experiencia.',
                'tipo' => 'reclutamiento',
                'nivel_jerarquico' => 'gerencia',
                'para_quien' => 'empresa',
                'activo' => true,
                'orden' => 10,
            ],
            [
                'nombre' => 'Capacitación de inducción',
                'descripcion' => 'Capacitación inicial para nuevos ingresos con seguimiento operativo.',
                'tipo' => 'capacitacion',
                'nivel_jerarquico' => 'operativo',
                'para_quien' => 'ambos',
                'activo' => true,
                'orden' => 20,
            ],
            [
                'nombre' => 'Coaching de liderazgo',
                'descripcion' => 'Acompañamiento para mandos medios y gerencia.',
                'tipo' => 'coaching',
                'nivel_jerarquico' => 'gerencia',
                'para_quien' => 'empresa',
                'activo' => true,
                'orden' => 30,
            ],
        ];

        foreach ($servicios as $servicio) {
            CatalogoServicio::create($servicio);
        }

        // 6. Crear exactamente 1 Vacante
        $vacante = Vacante::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Desarrollador Laravel Senior',
            'tipo_servicio' => 'reclutamiento',
            'descripcion' => 'Buscamos desarrollador para trabajar en proyectos web internos y mantenimiento de aplicaciones.',
            'requerimientos' => 'Laravel, PHP, MySQL, Git, Vue y trabajo en equipo.',
            'nivel_jerarquico' => 'gerencia',
            'nivel_estudios_minimo' => 'ingenieria',
            'area_requerida' => 'Sistemas, desarrollo web, programación',
            'experiencia_minima' => 3,
            'salario_min' => 22000,
            'salario_max' => 36000,
            'ubicacion' => 'Monterrey, NL',
            'tipo_contrato' => 'Tiempo completo',
            'estado' => 'activa',
            'fecha_publicacion' => now()->subDays(2),
        ]);

        // 7. Crear exactamente 1 Postulación
        Postulacion::create([
            'candidato_id' => $candidato->id,
            'vacante_id' => $vacante->id,
            'estado' => 'postulado',
            'fecha_postulacion' => now()->subDays(1),
        ]);

        // 8. Crear exactamente 1 Servicio Asignado
        $servicioInduccion = CatalogoServicio::where('nombre', 'Capacitación de inducción')->first();
        ServicioAsignado::create([
            'servicio_id' => $servicioInduccion->id,
            'asignable_type' => Empresa::class,
            'asignable_id' => $empresa->id,
            'estado' => 'activo',
            'notas' => 'Sesión inicial para nuevos ingresos.',
            'asignado_a' => $interno->id,
            'asignado_por' => $admin->id,
            'fecha_inicio' => now()->subDay(),
        ]);

        $this->command->info('Base de datos limpiada y cargada con un ejemplo único por entidad:');
        $this->command->info('Admin: admin@sistemarh.com / password');
        $this->command->info('Empresa: empresa1@sistemarh.com / password');
        $this->command->info('Candidato: ana.garcia@sistemarh.com / password');
        $this->command->info('Interno: interno1@sistemarh.com / password');
    }
}
