<?php

namespace Database\Seeders;

use App\Models\CatalogoOpcion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        CatalogoOpcion::seedDefaults();

        User::updateOrCreate(
            ['email' => 'admin@sistemarh.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'rol' => 'admin',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Datos base listos: admin@sistemarh.com / password');
    }
}
