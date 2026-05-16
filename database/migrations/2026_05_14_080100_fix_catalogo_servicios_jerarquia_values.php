<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'gerencia' WHERE nivel_jerarquico = 'gerente'");
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'supervision' WHERE nivel_jerarquico = 'supervisor'");
        DB::statement("ALTER TABLE catalogo_servicios MODIFY nivel_jerarquico ENUM('todos','operativo','auxiliar','tecnico','administrativo','analista','supervision','coordinador','gerencia','director','directivo') NOT NULL DEFAULT 'todos'");
    }

    public function down(): void
    {
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'gerente' WHERE nivel_jerarquico = 'gerencia'");
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'supervisor' WHERE nivel_jerarquico = 'supervision'");
        DB::statement("ALTER TABLE catalogo_servicios MODIFY nivel_jerarquico ENUM('todos','operativo','auxiliar','tecnico','administrativo','analista','supervisor','coordinador','gerente','director','directivo') NOT NULL DEFAULT 'todos'");
    }
};
