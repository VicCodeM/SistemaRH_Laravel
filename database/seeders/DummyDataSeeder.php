<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Candidato;
use App\Models\Postulacion;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Usuarios
        $user1 = User::firstOrCreate(['email' => 'juan@test.com'], ['name' => 'Juan Perez', 'password' => Hash::make('12345678')]);
        $user2 = User::firstOrCreate(['email' => 'maria@test.com'], ['name' => 'Maria Garcia', 'password' => Hash::make('12345678')]);
        $user3 = User::firstOrCreate(['email' => 'carlos@test.com'], ['name' => 'Carlos Lopez', 'password' => Hash::make('12345678')]);

        // Candidatos
        $c1 = Candidato::firstOrCreate(['usuario_id' => $user1->id], ['nombre' => 'Juan', 'apellido_paterno' => 'Perez', 'solicitud_estado' => 'enviada']);
        $c2 = Candidato::firstOrCreate(['usuario_id' => $user2->id], ['nombre' => 'Maria', 'apellido_paterno' => 'Garcia', 'solicitud_estado' => 'enviada']);
        $c3 = Candidato::firstOrCreate(['usuario_id' => $user3->id], ['nombre' => 'Carlos', 'apellido_paterno' => 'Lopez', 'solicitud_estado' => 'enviada']);

        // Empresa y Vacante
        DB::table('empresas')->insertOrIgnore(['id' => 1, 'nombre_empresa' => 'Empresa Falsa', 'usuario_id' => 1]);
        DB::table('vacantes')->insertOrIgnore(['id' => 1, 'empresa_id' => 1, 'titulo' => 'Desarrollador Web Dummy']);

        // Postulaciones
        Postulacion::firstOrCreate(['vacante_id' => 1, 'candidato_id' => $c1->id], ['estado' => 'postulado']);
        Postulacion::firstOrCreate(['vacante_id' => 1, 'candidato_id' => $c2->id], ['estado' => 'entrevista']);
        Postulacion::firstOrCreate(['vacante_id' => 1, 'candidato_id' => $c3->id], ['estado' => 'seleccionado']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
