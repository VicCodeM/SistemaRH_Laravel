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
        $emailsDemo = [
            'juan@test.com',
            'maria@test.com',
            'carlos@test.com',
            'empresa@test.com',
            'candidato1@sistemarh.com',
            'candidato2@sistemarh.com',
            'candidato3@sistemarh.com',
            'empresa1@sistemarh.com',
            'empresa2@sistemarh.com',
            'interno@sistemarh.com',
            'interno1@sistemarh.com',
            'ana.garcia@sistemarh.com',
            'luis.martinez@sistemarh.com',
            'sofia.ruiz@sistemarh.com',
            'jorge.lopez@sistemarh.com',
            'maria.ramos@sistemarh.com',
        ];

        User::whereIn('email', $emailsDemo)->get()->each(fn (User $user) => $user->delete());
        CatalogoServicio::query()->delete();
        PersonalExterno::query()->delete();

        $admin = User::where('email', 'admin@sistemarh.com')->firstOrFail();

        $interno = User::updateOrCreate(
            ['email' => 'interno1@sistemarh.com'],
            [
                'name' => 'Interno Demo',
                'password' => Hash::make('password'),
                'rol' => 'interno',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        $empresa1User = User::updateOrCreate(
            ['email' => 'empresa1@sistemarh.com'],
            [
                'name' => 'Tech Solutions RH',
                'password' => Hash::make('password'),
                'rol' => 'empresa',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );
        $empresa1 = Empresa::updateOrCreate(
            ['usuario_id' => $empresa1User->id],
            [
                'nombre_empresa' => 'Tech Solutions SA de CV',
                'rfc' => 'TSO860501XX3',
                'telefono' => '8112345678',
                'direccion' => 'Av. Constitución 100, Monterrey, NL',
                'ciudad' => 'Monterrey',
                'estado' => 'activa',
            ]
        );

        $empresa2User = User::updateOrCreate(
            ['email' => 'empresa2@sistemarh.com'],
            [
                'name' => 'Grupo Industrial MX',
                'password' => Hash::make('password'),
                'rol' => 'empresa',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );
        $empresa2 = Empresa::updateOrCreate(
            ['usuario_id' => $empresa2User->id],
            [
                'nombre_empresa' => 'Grupo Industrial MX SA de CV',
                'rfc' => 'GIM900312AB9',
                'telefono' => '5512345678',
                'direccion' => 'Insurgentes Sur 1602, CDMX',
                'ciudad' => 'Ciudad de México',
                'estado' => 'pendiente',
            ]
        );

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
            [
                'nombre' => 'Evaluación de candidatos',
                'descripcion' => 'Diagnóstico y evaluación para perfiles operativos y administrativos.',
                'tipo' => 'evaluacion',
                'nivel_jerarquico' => 'supervision',
                'para_quien' => 'ambos',
                'activo' => true,
                'orden' => 40,
            ],
        ];

        foreach ($servicios as $servicio) {
            CatalogoServicio::updateOrCreate(
                ['nombre' => $servicio['nombre']],
                $servicio
            );
        }

        $vacante1 = Vacante::updateOrCreate(
            ['empresa_id' => $empresa1->id, 'titulo' => 'Desarrollador Laravel Senior'],
            [
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
                'fecha_publicacion' => now()->subDays(5),
            ]
        );

        $vacante2 = Vacante::updateOrCreate(
            ['empresa_id' => $empresa1->id, 'titulo' => 'Auxiliar de soporte técnico'],
            [
                'tipo_servicio' => 'reclutamiento',
                'descripcion' => 'Atención a usuarios, mantenimiento básico y control de equipos.',
                'requerimientos' => 'Atención al cliente, hardware, software básico y disponibilidad de horario.',
                'nivel_jerarquico' => 'operativo',
                'nivel_estudios_minimo' => 'preparatoria',
                'area_requerida' => 'Soporte técnico, sistemas, hardware',
                'experiencia_minima' => 1,
                'salario_min' => 9000,
                'salario_max' => 13000,
                'ubicacion' => 'Monterrey, NL',
                'tipo_contrato' => 'Tiempo completo',
                'estado' => 'activa',
                'fecha_publicacion' => now()->subDays(3),
            ]
        );

        $vacante3 = Vacante::updateOrCreate(
            ['empresa_id' => $empresa2->id, 'titulo' => 'Capacitación en seguridad industrial'],
            [
                'tipo_servicio' => 'capacitacion',
                'descripcion' => 'Capacitación interna para seguridad, prevención y operación segura.',
                'requerimientos' => 'Seguridad industrial, prevención, brigadas y control de riesgos.',
                'nivel_jerarquico' => 'operativo',
                'nivel_estudios_minimo' => 'secundaria',
                'area_requerida' => 'Seguridad industrial, prevención, brigadas',
                'experiencia_minima' => 0,
                'salario_min' => 0,
                'salario_max' => 0,
                'ubicacion' => 'Ciudad de México',
                'tipo_contrato' => 'Proyecto',
                'estado' => 'activa',
                'fecha_publicacion' => now()->subDays(2),
            ]
        );

        $vacante4 = Vacante::updateOrCreate(
            ['empresa_id' => $empresa2->id, 'titulo' => 'Consultoría de RH'],
            [
                'tipo_servicio' => 'consultoria',
                'descripcion' => 'Análisis de procesos de recursos humanos y capacitación para mandos medios.',
                'requerimientos' => 'Recursos humanos, reclutamiento, procesos y capacitación.',
                'nivel_jerarquico' => 'gerencia',
                'nivel_estudios_minimo' => 'licenciatura',
                'area_requerida' => 'Recursos humanos, administración, capacitación',
                'experiencia_minima' => 2,
                'salario_min' => 18000,
                'salario_max' => 25000,
                'ubicacion' => 'Ciudad de México',
                'tipo_contrato' => 'Proyecto',
                'estado' => 'pendiente',
                'fecha_publicacion' => now()->subDay(),
            ]
        );

        $candidatos = [
            [
                'email' => 'ana.garcia@sistemarh.com',
                'nombre' => 'Ana',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'solicitud_estado' => 'aprobada',
                'telefono' => '8125551001',
                'celular' => '8125551002',
                'ciudad' => 'Monterrey',
                'escolaridad' => 'ingenieria',
                'experiencia_anios' => 4,
                'puesto_deseado' => 'Desarrolladora web',
                'habilidades' => 'Laravel, PHP, MySQL, Git, Vue, APIs',
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
            ],
            [
                'email' => 'luis.martinez@sistemarh.com',
                'nombre' => 'Luis',
                'apellido_paterno' => 'Martínez',
                'apellido_materno' => 'Reyes',
                'solicitud_estado' => 'aprobada',
                'telefono' => '8115552001',
                'celular' => '8115552002',
                'ciudad' => 'Monterrey',
                'escolaridad' => 'preparatoria',
                'experiencia_anios' => 2,
                'puesto_deseado' => 'Soporte técnico',
                'habilidades' => 'Soporte a usuarios, cableado, hardware, inventarios',
                'escolaridad_detallada' => [
                    [
                        'nivel' => 'preparatoria',
                        'nombre' => 'Conalep Monterrey',
                        'anios' => '3',
                        'titulo' => 'Técnico en soporte y redes',
                    ],
                ],
                'historial_laboral' => [
                    [
                        'empresa' => 'Help Desk SA',
                        'puesto' => 'Técnico de soporte',
                        'jefe' => 'A. Torres',
                        'sueldo' => '10500',
                        'desde' => '2022-02-01',
                        'hasta' => '2025-01-01',
                        'motivo' => 'Mejor oportunidad',
                    ],
                ],
            ],
            [
                'email' => 'sofia.ruiz@sistemarh.com',
                'nombre' => 'Sofía',
                'apellido_paterno' => 'Ruiz',
                'apellido_materno' => 'Navarro',
                'solicitud_estado' => 'aprobada',
                'telefono' => '5525553001',
                'celular' => '5525553002',
                'ciudad' => 'Ciudad de México',
                'escolaridad' => 'licenciatura',
                'experiencia_anios' => 3,
                'puesto_deseado' => 'Analista de RH',
                'habilidades' => 'Reclutamiento, capacitación, entrevistas, clima laboral',
                'escolaridad_detallada' => [
                    [
                        'nivel' => 'licenciatura',
                        'nombre' => 'Universidad Nacional Autónoma de México',
                        'anios' => '4',
                        'titulo' => 'Licenciatura en Recursos Humanos',
                    ],
                ],
                'historial_laboral' => [
                    [
                        'empresa' => 'RH Integral',
                        'puesto' => 'Analista de selección',
                        'jefe' => 'L. Pérez',
                        'sueldo' => '15000',
                        'desde' => '2020-03-01',
                        'hasta' => '2024-03-01',
                        'motivo' => 'Cambio de residencia',
                    ],
                ],
            ],
            [
                'email' => 'jorge.lopez@sistemarh.com',
                'nombre' => 'Jorge',
                'apellido_paterno' => 'López',
                'apellido_materno' => 'Castillo',
                'solicitud_estado' => 'aprobada',
                'telefono' => '3315554001',
                'celular' => '3315554002',
                'ciudad' => 'Guadalajara',
                'escolaridad' => 'secundaria',
                'experiencia_anios' => 1,
                'puesto_deseado' => 'Auxiliar de almacén',
                'habilidades' => 'Inventarios, orden, carga ligera, limpieza',
                'escolaridad_detallada' => [
                    [
                        'nivel' => 'secundaria',
                        'nombre' => 'Secundaria Técnica 45',
                        'anios' => '3',
                        'titulo' => 'Secundaria terminada',
                    ],
                ],
                'historial_laboral' => [
                    [
                        'empresa' => 'Almacenes Jalisco',
                        'puesto' => 'Auxiliar general',
                        'jefe' => 'R. Hernández',
                        'sueldo' => '8500',
                        'desde' => '2023-01-01',
                        'hasta' => '2024-12-31',
                        'motivo' => 'Búsqueda de crecimiento',
                    ],
                ],
            ],
            [
                'email' => 'maria.ramos@sistemarh.com',
                'nombre' => 'María',
                'apellido_paterno' => 'Ramos',
                'apellido_materno' => 'Silva',
                'solicitud_estado' => 'enviada',
                'telefono' => '2285555001',
                'celular' => '2285555002',
                'ciudad' => 'Puebla',
                'escolaridad' => 'licenciatura',
                'experiencia_anios' => 1,
                'puesto_deseado' => 'Capacitación en salud',
                'habilidades' => 'Atención al paciente, comunicación, primeros auxilios',
                'escolaridad_detallada' => [
                    [
                        'nivel' => 'licenciatura',
                        'nombre' => 'Universidad de Puebla',
                        'anios' => '4',
                        'titulo' => 'Licenciatura en Enfermería',
                    ],
                ],
                'historial_laboral' => [
                    [
                        'empresa' => 'Clínica Sur',
                        'puesto' => 'Enfermera auxiliar',
                        'jefe' => 'D. Gómez',
                        'sueldo' => '12000',
                        'desde' => '2023-06-01',
                        'hasta' => '2024-06-01',
                        'motivo' => 'Cambio de proyecto',
                    ],
                ],
            ],
        ];

        $candidatosModelos = [];

        foreach ($candidatos as $dato) {
            $user = User::updateOrCreate(
                ['email' => $dato['email']],
                [
                    'name' => trim($dato['nombre'] . ' ' . $dato['apellido_paterno']),
                    'password' => Hash::make('password'),
                    'rol' => 'candidato',
                    'estado' => 'activo',
                    'email_verified_at' => now(),
                ]
            );

            $candidato = Candidato::updateOrCreate(
                ['usuario_id' => $user->id],
                [
                    'nombre' => $dato['nombre'],
                    'apellido_paterno' => $dato['apellido_paterno'],
                    'apellido_materno' => $dato['apellido_materno'],
                    'telefono' => $dato['telefono'],
                    'celular' => $dato['celular'],
                    'ciudad' => $dato['ciudad'],
                    'puesto_deseado' => $dato['puesto_deseado'],
                    'experiencia_anios' => $dato['experiencia_anios'],
                    'escolaridad' => $dato['escolaridad'],
                    'habilidades' => $dato['habilidades'],
                    'solicitud_estado' => $dato['solicitud_estado'],
                    'solicitud_enviada_at' => $dato['solicitud_estado'] === 'borrador' ? null : now()->subDays(2),
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
                    'escolaridad_detallada' => $dato['escolaridad_detallada'],
                    'historial_laboral' => $dato['historial_laboral'],
                    'referencias_personales' => [
                        [
                            'nombre' => 'Referencia Demo',
                            'telefono' => '5550000000',
                            'ocupacion' => 'Supervisor',
                            'tiempo' => '2 años',
                            'domicilio' => 'Ciudad de México',
                        ],
                    ],
                ]
            );

            $candidatosModelos[$dato['email']] = $candidato;
        }

        Postulacion::updateOrCreate(
            ['candidato_id' => $candidatosModelos['ana.garcia@sistemarh.com']->id, 'vacante_id' => $vacante1->id],
            ['estado' => 'postulado', 'fecha_postulacion' => now()->subDays(3)]
        );

        Postulacion::updateOrCreate(
            ['candidato_id' => $candidatosModelos['luis.martinez@sistemarh.com']->id, 'vacante_id' => $vacante2->id],
            ['estado' => 'entrevista', 'fecha_postulacion' => now()->subDays(2)]
        );

        Postulacion::updateOrCreate(
            ['candidato_id' => $candidatosModelos['sofia.ruiz@sistemarh.com']->id, 'vacante_id' => $vacante4->id],
            ['estado' => 'seleccionado', 'fecha_postulacion' => now()->subDays(5)]
        );

        Postulacion::updateOrCreate(
            ['candidato_id' => $candidatosModelos['jorge.lopez@sistemarh.com']->id, 'vacante_id' => $vacante3->id],
            ['estado' => 'rechazado', 'fecha_postulacion' => now()->subDays(1)]
        );

        ServicioAsignado::updateOrCreate(
            [
                'servicio_id' => CatalogoServicio::where('nombre', 'Capacitación de inducción')->value('id'),
                'asignable_type' => Empresa::class,
                'asignable_id' => $empresa1->id,
            ],
            [
                'estado' => 'activo',
                'notas' => 'Sesión inicial para nuevos ingresos.',
                'asignado_a' => $interno->id,
                'asignado_por' => $admin->id,
                'fecha_inicio' => now()->subDay(),
            ]
        );

        ServicioAsignado::updateOrCreate(
            [
                'servicio_id' => CatalogoServicio::where('nombre', 'Evaluación de candidatos')->value('id'),
                'asignable_type' => Candidato::class,
                'asignable_id' => $candidatosModelos['ana.garcia@sistemarh.com']->id,
            ],
            [
                'estado' => 'en_proceso',
                'notas' => 'Evaluación técnica para la vacante senior.',
                'asignado_a' => $interno->id,
                'asignado_por' => $admin->id,
                'fecha_inicio' => now()->subHours(12),
            ]
        );

        ServicioAsignado::updateOrCreate(
            [
                'servicio_id' => CatalogoServicio::where('nombre', 'Coaching de liderazgo')->value('id'),
                'asignable_type' => Empresa::class,
                'asignable_id' => $empresa2->id,
            ],
            [
                'estado' => 'completado',
                'notas' => 'Sesión cerrada con plan de seguimiento.',
                'cierre_resumen' => 'Se entregó plan de acción y seguimiento de 30 días.',
                'asignado_a' => $interno->id,
                'asignado_por' => $admin->id,
                'fecha_inicio' => now()->subDays(8),
                'fecha_fin' => now()->subDays(2),
            ]
        );

        $this->command->info('Datos de demo actualizados con perfiles y solicitudes controladas.');
        $this->command->info('Candidatos: ana.garcia@sistemarh.com, luis.martinez@sistemarh.com, sofia.ruiz@sistemarh.com, jorge.lopez@sistemarh.com, maria.ramos@sistemarh.com');
        $this->command->info('Empresa 1: empresa1@sistemarh.com / password');
        $this->command->info('Empresa 2: empresa2@sistemarh.com / password');
        $this->command->info('Interno: interno1@sistemarh.com / password');
    }
}
