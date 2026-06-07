<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CatalogoServicio;
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
        Postulacion::query()->delete();
        ServicioAsignado::query()->delete();
        Vacante::query()->delete();
        PersonalExterno::query()->delete();
        Empresa::query()->delete();
        Candidato::query()->delete();
        CatalogoServicio::query()->delete();
        User::where('email', '!=', 'admin@sistemarh.com')->delete();

        $admin = User::updateOrCreate(
            ['email' => 'admin@sistemarh.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'rol' => 'admin',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        $internos = collect([
            [
                'name' => 'Laura Medina',
                'email' => 'interno1@sistemarh.com',
                'nivel_jerarquico' => 'supervision',
                'departamento' => 'Reclutamiento',
            ],
            [
                'name' => 'Marco Solis',
                'email' => 'interno2@sistemarh.com',
                'nivel_jerarquico' => 'gerencia',
                'departamento' => 'Operacion RH',
            ],
        ])->map(function (array $interno) {
            return User::create([
                'name' => $interno['name'],
                'email' => $interno['email'],
                'password' => Hash::make('password'),
                'rol' => 'interno',
                'estado' => 'activo',
                'email_verified_at' => now(),
                'nivel_jerarquico' => $interno['nivel_jerarquico'],
                'departamento' => $interno['departamento'],
                'disponibilidad' => 'disponible',
            ]);
        });

        $empresas = collect([
            [
                'usuario' => [
                    'name' => 'Tech Solutions RH',
                    'email' => 'empresa1@sistemarh.com',
                    'estado' => 'activo',
                ],
                'empresa' => [
                    'nombre_empresa' => 'Tech Solutions SA de CV',
                    'razon_social' => 'Tech Solutions SA de CV',
                    'rfc' => 'TSO860501XX3',
                    'telefono' => '8112345678',
                    'direccion' => 'Av. Constitucion 100, Monterrey, NL',
                    'descripcion' => 'Desarrollo de software y soporte empresarial.',
                    'estado' => 'activa',
                    'nombre_rh' => 'Claudia Reyes',
                    'telefono_directo' => '8112345600',
                    'ciudad' => 'Monterrey',
                    'municipio' => 'Monterrey',
                    'codigo_postal' => '64000',
                    'pagina_web' => 'https://techsolutionsrh.test',
                ],
            ],
            [
                'usuario' => [
                    'name' => 'Moto Extreme RH',
                    'email' => 'empresa2@sistemarh.com',
                    'estado' => 'activo',
                ],
                'empresa' => [
                    'nombre_empresa' => 'Moto Extreme SA de CV',
                    'razon_social' => 'Moto Extreme SA de CV',
                    'rfc' => 'MEX900210AB1',
                    'telefono' => '6561112233',
                    'direccion' => 'Blvd. Ejercito Nacional 4500, Juarez, CHH',
                    'descripcion' => 'Comercializacion y servicio automotriz.',
                    'estado' => 'activa',
                    'nombre_rh' => 'Victor Salinas',
                    'telefono_directo' => '6561112200',
                    'ciudad' => 'Juarez',
                    'municipio' => 'Juarez',
                    'codigo_postal' => '32310',
                    'pagina_web' => 'https://motoextreme.test',
                ],
            ],
            [
                'usuario' => [
                    'name' => 'Nova Capital Humano',
                    'email' => 'empresa3@sistemarh.com',
                    'estado' => 'pendiente',
                ],
                'empresa' => [
                    'nombre_empresa' => 'Nova Capital Humano',
                    'razon_social' => 'Nova Capital Humano SA de CV',
                    'rfc' => 'NCH950101QW8',
                    'telefono' => '6143334455',
                    'direccion' => 'Av. Universidad 800, Chihuahua, CHH',
                    'descripcion' => 'Servicios administrativos y consultoria organizacional.',
                    'estado' => 'pendiente',
                    'nombre_rh' => 'Daniela Perez',
                    'telefono_directo' => '6143334400',
                    'ciudad' => 'Chihuahua',
                    'municipio' => 'Chihuahua',
                    'codigo_postal' => '31125',
                    'pagina_web' => 'https://novach.test',
                ],
            ],
        ])->map(function (array $empresaData) {
            $usuario = User::create([
                'name' => $empresaData['usuario']['name'],
                'email' => $empresaData['usuario']['email'],
                'password' => Hash::make('password'),
                'rol' => 'empresa',
                'estado' => $empresaData['usuario']['estado'],
                'email_verified_at' => now(),
            ]);

            return Empresa::create([
                'usuario_id' => $usuario->id,
                ...$empresaData['empresa'],
            ]);
        });

        $candidatos = collect([
            [
                'usuario' => ['name' => 'Ana Garcia', 'email' => 'ana.garcia@sistemarh.com'],
                'candidato' => [
                    'nombre' => 'Ana',
                    'apellido_paterno' => 'Garcia',
                    'apellido_materno' => 'Lopez',
                    'edad' => 29,
                    'sexo' => 'F',
                    'fecha_nacimiento' => '1996-03-14',
                    'lugar_nacimiento' => 'Monterrey',
                    'nacionalidad' => 'Mexicana',
                    'telefono' => '8125551001',
                    'celular' => '8125551002',
                    'domicilio' => 'Col. Cumbres 123',
                    'colonia' => 'Cumbres',
                    'codigo_postal' => '64610',
                    'municipio' => 'Monterrey',
                    'ciudad' => 'Monterrey',
                    'peso' => '58 kg',
                    'estatura' => '1.65 m',
                    'vive_con' => 'familia',
                    'estado_civil' => 'soltera',
                    'dependientes' => '0',
                    'curp' => 'GALA960314MNLRPN01',
                    'nore_seguro_social' => '12345678901',
                    'rfc' => 'GALA960314AB1',
                    'afore' => 'XXI Banorte',
                    'experiencia_anios' => 4,
                    'puesto_deseado' => 'Desarrolladora Laravel',
                    'habilidades' => 'Laravel, PHP, SQL, Git, APIs REST',
                    'escolaridad' => 'ingenieria',
                    'sueldo_deseado' => 26000,
                    'solicitud_estado' => 'aprobada',
                    'solicitud_enviada_at' => now()->subDays(3),
                ],
            ],
            [
                'usuario' => ['name' => 'Jose Ramirez', 'email' => 'jose.ramirez@sistemarh.com'],
                'candidato' => [
                    'nombre' => 'Jose',
                    'apellido_paterno' => 'Ramirez',
                    'apellido_materno' => 'Soto',
                    'edad' => 34,
                    'sexo' => 'M',
                    'fecha_nacimiento' => '1991-08-22',
                    'lugar_nacimiento' => 'Juarez',
                    'nacionalidad' => 'Mexicana',
                    'telefono' => '6562223344',
                    'celular' => '6562223300',
                    'domicilio' => 'Av. Lerdo 450',
                    'colonia' => 'Centro',
                    'codigo_postal' => '32000',
                    'municipio' => 'Juarez',
                    'ciudad' => 'Juarez',
                    'peso' => '78 kg',
                    'estatura' => '1.76 m',
                    'vive_con' => 'pareja',
                    'estado_civil' => 'casado',
                    'dependientes' => '2',
                    'curp' => 'RASJ910822HCHMTS04',
                    'nore_seguro_social' => '10987654321',
                    'rfc' => 'RASJ910822MN3',
                    'afore' => 'Profuturo',
                    'experiencia_anios' => 8,
                    'puesto_deseado' => 'Supervisor de mantenimiento',
                    'habilidades' => 'Mantenimiento, liderazgo, seguridad industrial',
                    'escolaridad' => 'tecnico',
                    'sueldo_deseado' => 22000,
                    'solicitud_estado' => 'aprobada',
                    'solicitud_enviada_at' => now()->subDays(5),
                ],
            ],
            [
                'usuario' => ['name' => 'Lucia Torres', 'email' => 'lucia.torres@sistemarh.com'],
                'candidato' => [
                    'nombre' => 'Lucia',
                    'apellido_paterno' => 'Torres',
                    'apellido_materno' => 'Molina',
                    'edad' => 26,
                    'sexo' => 'F',
                    'fecha_nacimiento' => '1999-01-09',
                    'lugar_nacimiento' => 'Chihuahua',
                    'nacionalidad' => 'Mexicana',
                    'telefono' => '6148884400',
                    'celular' => '6148884411',
                    'domicilio' => 'Priv. Mirador 45',
                    'colonia' => 'Campestre',
                    'codigo_postal' => '31213',
                    'municipio' => 'Chihuahua',
                    'ciudad' => 'Chihuahua',
                    'peso' => '62 kg',
                    'estatura' => '1.68 m',
                    'vive_con' => 'familia',
                    'estado_civil' => 'soltera',
                    'dependientes' => '1',
                    'curp' => 'TOML990109MCHRLC08',
                    'nore_seguro_social' => '22334455667',
                    'rfc' => 'TOML990109PL0',
                    'afore' => 'Sura',
                    'experiencia_anios' => 3,
                    'puesto_deseado' => 'Analista contable',
                    'habilidades' => 'Excel, conciliaciones, reportes administrativos',
                    'escolaridad' => 'licenciatura',
                    'sueldo_deseado' => 18000,
                    'solicitud_estado' => 'enviada',
                    'solicitud_enviada_at' => now()->subDay(),
                ],
            ],
        ])->map(function (array $candidatoData) {
            $usuario = User::create([
                'name' => $candidatoData['usuario']['name'],
                'email' => $candidatoData['usuario']['email'],
                'password' => Hash::make('password'),
                'rol' => 'candidato',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]);

            return Candidato::create([
                'usuario_id' => $usuario->id,
                ...$candidatoData['candidato'],
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
                        'nivel' => $candidatoData['candidato']['escolaridad'],
                        'nombre' => 'Institucion demo',
                        'anios' => '4',
                        'titulo' => 'Programa relacionado',
                    ],
                ],
                'historial_laboral' => [
                    [
                        'empresa' => 'Empresa anterior demo',
                        'puesto' => $candidatoData['candidato']['puesto_deseado'],
                        'jefe' => 'Supervisor demo',
                        'sueldo' => '18000',
                        'desde' => '2022-01-01',
                        'hasta' => '2025-01-01',
                        'motivo' => 'Crecimiento profesional',
                    ],
                ],
                'referencias_personales' => [
                    [
                        'nombre' => 'Referencia demo',
                        'telefono' => '5550000000',
                        'ocupacion' => 'Supervisor',
                        'tiempo' => '2 anos',
                        'domicilio' => 'Ciudad demo',
                    ],
                ],
            ]);
        });

        $servicios = collect([
            [
                'nombre' => 'Reclutamiento operativo',
                'descripcion' => 'Busqueda y filtro inicial de personal operativo.',
                'tipo' => 'reclutamiento',
                'flujo' => 'vacante',
                'nivel_jerarquico' => 'operativo',
                'para_quien' => 'empresa',
                'activo' => true,
                'orden' => 10,
            ],
            [
                'nombre' => 'Reclutamiento especializado',
                'descripcion' => 'Busqueda de perfiles tecnicos y administrativos.',
                'tipo' => 'reclutamiento',
                'flujo' => 'vacante',
                'nivel_jerarquico' => 'supervision',
                'para_quien' => 'empresa',
                'activo' => true,
                'orden' => 20,
            ],
            [
                'nombre' => 'Capacitacion de induccion',
                'descripcion' => 'Induccion para personal de nuevo ingreso.',
                'tipo' => 'capacitacion',
                'flujo' => 'servicio',
                'nivel_jerarquico' => 'todos',
                'para_quien' => 'ambos',
                'activo' => true,
                'orden' => 30,
            ],
            [
                'nombre' => 'Coaching de liderazgo',
                'descripcion' => 'Acompanamiento para jefaturas y gerencias.',
                'tipo' => 'coaching',
                'flujo' => 'servicio',
                'nivel_jerarquico' => 'gerencia',
                'para_quien' => 'empresa',
                'activo' => true,
                'orden' => 40,
            ],
        ])->map(fn (array $servicio) => CatalogoServicio::create($servicio));

        $vacantes = collect([
            [
                'empresa' => $empresas[0],
                'titulo' => 'Desarrollador Laravel Senior',
                'tipo_servicio' => 'reclutamiento',
                'descripcion' => 'Proyecto interno con Laravel, mantenimiento y nuevas integraciones.',
                'requerimientos' => 'PHP, Laravel, SQL, Git y trabajo colaborativo.',
                'nivel_jerarquico' => 'gerencia',
                'nivel_estudios_minimo' => 'ingenieria',
                'area_requerida' => 'Sistemas, desarrollo web, programacion',
                'experiencia_minima' => 4,
                'salario_min' => 28000,
                'salario_max' => 42000,
                'ingresos_ofrecidos' => 'Sueldo base, bono trimestral y prestaciones superiores.',
                'prestaciones' => 'IMSS, Infonavit, vacaciones, fondo de ahorro y home office parcial.',
                'ubicacion' => 'Monterrey, NL',
                'tipo_contrato' => 'Tiempo completo',
                'cupos' => 2,
                'estado' => 'activa',
                'fecha_publicacion' => now()->subDays(4),
            ],
            [
                'empresa' => $empresas[1],
                'titulo' => 'Supervisor de taller',
                'tipo_servicio' => 'reclutamiento',
                'descripcion' => 'Coordinar personal tecnico y control operativo del taller.',
                'requerimientos' => 'Liderazgo, mantenimiento preventivo y reporteo.',
                'nivel_jerarquico' => 'supervision',
                'nivel_estudios_minimo' => 'tecnico',
                'area_requerida' => 'Mantenimiento industrial',
                'experiencia_minima' => 5,
                'salario_min' => 22000,
                'salario_max' => 30000,
                'ingresos_ofrecidos' => 'Sueldo base y bono por productividad.',
                'prestaciones' => 'IMSS, transporte, comedor y descanso fijo.',
                'ubicacion' => 'Juarez, CHH',
                'tipo_contrato' => 'Tiempo completo',
                'cupos' => 1,
                'estado' => 'activa',
                'fecha_publicacion' => now()->subDays(2),
            ],
            [
                'empresa' => $empresas[0],
                'titulo' => 'Analista contable',
                'tipo_servicio' => 'reclutamiento',
                'descripcion' => 'Apoyo administrativo y conciliacion de reportes financieros.',
                'requerimientos' => 'Excel, conciliaciones y seguimiento administrativo.',
                'nivel_jerarquico' => 'operativo',
                'nivel_estudios_minimo' => 'licenciatura',
                'area_requerida' => 'Contabilidad',
                'experiencia_minima' => 2,
                'salario_min' => 16000,
                'salario_max' => 22000,
                'ingresos_ofrecidos' => 'Sueldo base y pago quincenal.',
                'prestaciones' => 'IMSS, vacaciones y utilidades.',
                'ubicacion' => 'Monterrey, NL',
                'tipo_contrato' => 'Tiempo completo',
                'cupos' => 1,
                'estado' => 'pendiente',
                'fecha_publicacion' => now()->subDay(),
            ],
        ])->map(function (array $vacanteData) {
            $empresa = $vacanteData['empresa'];
            unset($vacanteData['empresa']);

            return Vacante::create([
                'empresa_id' => $empresa->id,
                ...$vacanteData,
            ]);
        });

        Postulacion::create([
            'candidato_id' => $candidatos[0]->id,
            'vacante_id' => $vacantes[0]->id,
            'estado' => 'entrevista',
            'fecha_postulacion' => now()->subDays(2),
        ]);

        Postulacion::create([
            'candidato_id' => $candidatos[1]->id,
            'vacante_id' => $vacantes[1]->id,
            'estado' => 'postulado',
            'fecha_postulacion' => now()->subDay(),
        ]);

        Postulacion::create([
            'candidato_id' => $candidatos[2]->id,
            'vacante_id' => $vacantes[2]->id,
            'estado' => 'postulado',
            'fecha_postulacion' => now()->subHours(12),
        ]);

        ServicioAsignado::create([
            'servicio_id' => $servicios[2]->id,
            'nivel_jerarquico' => 'todos',
            'asignable_type' => Empresa::class,
            'asignable_id' => $empresas[0]->id,
            'estado' => 'activo',
            'notas' => 'Capacitacion inicial para personal de nuevo ingreso.',
            'asignado_a' => $internos[0]->id,
            'asignado_por' => $admin->id,
            'solicitado_por' => $empresas[0]->usuario_id,
            'fecha_inicio' => now()->subDay(),
        ]);

        ServicioAsignado::create([
            'servicio_id' => $servicios[3]->id,
            'nivel_jerarquico' => 'gerencia',
            'asignable_type' => Empresa::class,
            'asignable_id' => $empresas[1]->id,
            'estado' => 'pendiente',
            'notas' => 'Pendiente de agendar primera sesion.',
            'asignado_a' => $internos[1]->id,
            'asignado_por' => $admin->id,
            'solicitado_por' => $empresas[1]->usuario_id,
            'fecha_inicio' => now()->addDays(1),
        ]);

        $this->command->info('Datos demo cargados correctamente.');
        $this->command->info('Admin: admin@sistemarh.com / password');
        $this->command->info('Empresa 1: empresa1@sistemarh.com / password');
        $this->command->info('Empresa 2: empresa2@sistemarh.com / password');
        $this->command->info('Candidato: ana.garcia@sistemarh.com / password');
        $this->command->info('Interno: interno1@sistemarh.com / password');
    }
}
