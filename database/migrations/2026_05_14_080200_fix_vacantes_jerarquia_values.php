<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE vacantes SET nivel_jerarquico = 'gerencia' WHERE nivel_jerarquico = 'gerente'");
        DB::statement("UPDATE vacantes SET nivel_jerarquico = 'supervision' WHERE nivel_jerarquico = 'supervisor'");
        DB::statement("ALTER TABLE vacantes MODIFY nivel_jerarquico ENUM('operativo','administrativo','analista','supervision','coordinador','gerencia','directivo','director') NOT NULL DEFAULT 'operativo'");
    }

    public function down(): void
    {
        DB::statement("UPDATE vacantes SET nivel_jerarquico = 'gerente' WHERE nivel_jerarquico = 'gerencia'");
        DB::statement("UPDATE vacantes SET nivel_jerarquico = 'supervisor' WHERE nivel_jerarquico = 'supervision'");
        DB::statement("ALTER TABLE vacantes MODIFY nivel_jerarquico ENUM('operativo','administrativo','supervisor','gerente','directivo') NOT NULL DEFAULT 'operativo'");
    }
};
