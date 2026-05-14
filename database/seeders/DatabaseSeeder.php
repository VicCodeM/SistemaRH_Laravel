<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin
        User::firstOrCreate(['email' => 'admin@sistemarh.com'], [
            'name'     => 'Administrador',
            'password' => Hash::make('password'),
            'rol'      => 'admin',
            'estado'   => 'activo',
            'email_verified_at' => now(),
        ]);

        // Empresas
        $empresa1User = User::firstOrCreate(['email' => 'empresa1@sistemarh.com'], [
            'name'     => 'Tech Solutions SA',
            'password' => Hash::make('password'),
            'rol'      => 'empresa',
            'estado'   => 'activo',
            'email_verified_at' => now(),
        ]);
        $empresa1 = Empresa::firstOrCreate(['usuario_id' => $empresa1User->id], [
            'nombre_empresa' => 'Tech Solutions SA de CV',
            'rfc'            => 'TSO860501XX3',
            'telefono'       => '8112345678',
            'direccion'      => 'Av. Constitución 100, Monterrey NL',
            'ciudad'         => 'Monterrey',
            'estado'         => 'activa',
        ]);

        $empresa2User = User::firstOrCreate(['email' => 'empresa2@sistemarh.com'], [
            'name'     => 'Grupo Industrial MX',
            'password' => Hash::make('password'),
            'rol'      => 'empresa',
            'estado'   => 'activo',
            'email_verified_at' => now(),
        ]);
        $empresa2 = Empresa::firstOrCreate(['usuario_id' => $empresa2User->id], [
            'nombre_empresa' => 'Grupo Industrial MX SA',
            'rfc'            => 'GIM900312AB9',
            'telefono'       => '5512345678',
            'direccion'      => 'Insurgentes Sur 1602, CDMX',
            'ciudad'         => 'CDMX',
            'estado'         => 'pendiente',
        ]);

        // Vacantes
        $v1 = Vacante::firstOrCreate(
            ['empresa_id' => $empresa1->id, 'titulo' => 'Desarrollador PHP Senior'],
            [
                'descripcion'      => 'Buscamos desarrollador PHP con experiencia en Laravel. Trabajo remoto disponible.',
                'nivel_jerarquico' => 'administrativo',
                'salario_min'      => 20000,
                'salario_max'      => 35000,
                'ubicacion'        => 'Monterrey NL / Remoto',
                'tipo_contrato'    => 'Tiempo completo',
                'estado'           => 'activa',
                'fecha_publicacion' => now()->subDays(5),
            ]
        );

        $v2 = Vacante::firstOrCreate(
            ['empresa_id' => $empresa1->id, 'titulo' => 'Analista de Recursos Humanos'],
            [
                'descripcion'      => 'Responsable de reclutamiento, selección y gestión de personal.',
                'nivel_jerarquico' => 'administrativo',
                'salario_min'      => 12000,
                'salario_max'      => 18000,
                'ubicacion'        => 'Monterrey NL',
                'tipo_contrato'    => 'Tiempo completo',
                'estado'           => 'activa',
                'fecha_publicacion' => now()->subDays(3),
            ]
        );

        $v3 = Vacante::firstOrCreate(
            ['empresa_id' => $empresa2->id, 'titulo' => 'Operador de Producción'],
            [
                'descripcion'      => 'Operación de maquinaria en línea de producción. Turno matutino.',
                'nivel_jerarquico' => 'operativo',
                'salario_min'      => 8000,
                'salario_max'      => 11000,
                'ubicacion'        => 'CDMX',
                'tipo_contrato'    => 'Planta',
                'estado'           => 'pendiente',
                'fecha_publicacion' => now()->subDays(1),
            ]
        );

        // Candidatos
        for ($i = 1; $i <= 3; $i++) {
            $cUser = User::firstOrCreate(['email' => "candidato{$i}@sistemarh.com"], [
                'name'     => "Candidato Demo {$i}",
                'password' => Hash::make('password'),
                'rol'      => 'candidato',
                'estado'   => 'activo',
                'email_verified_at' => now(),
            ]);
            Candidato::firstOrCreate(['usuario_id' => $cUser->id], [
                'nombre'             => 'Candidato',
                'apellido_paterno'   => "Demo",
                'apellido_materno'   => "Número{$i}",
                'telefono'           => "55100000{$i}0",
                'celular'            => "55100000{$i}1",
                'curp'               => "CAND{$i}00101HDFLNS0{$i}",
                'puesto_deseado'     => $i === 1 ? 'Desarrollador' : ($i === 2 ? 'Analista RH' : 'Operador'),
                'experiencia_anios'  => $i * 2,
                'solicitud_estado'   => $i === 1 ? 'aprobada' : ($i === 2 ? 'enviada' : 'borrador'),
                'solicitud_enviada_at' => $i <= 2 ? now()->subHours($i * 3) : null,
            ]);
        }

        // Una postulación de ejemplo
        $candidato1 = Candidato::whereHas('usuario', fn ($q) => $q->where('email', 'candidato1@sistemarh.com'))->first();
        if ($candidato1) {
            Postulacion::firstOrCreate(
                ['candidato_id' => $candidato1->id, 'vacante_id' => $v1->id],
                ['estado' => 'postulado', 'fecha_postulacion' => now()->subDays(2)]
            );
        }

        $this->command->info('✓ Datos de demo creados:');
        $this->command->info('  Admin:      admin@sistemarh.com / password');
        $this->command->info('  Empresa 1:  empresa1@sistemarh.com / password');
        $this->command->info('  Empresa 2:  empresa2@sistemarh.com / password');
        $this->command->info('  Candidato:  candidato1@sistemarh.com / password');
    }
}
