<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE catalogo_servicios MODIFY nivel_jerarquico ENUM('todos','operativo','auxiliar','tecnico','administrativo','analista','supervisor','coordinador','gerencia','director','directivo') NOT NULL DEFAULT 'todos'");
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'gerencia' WHERE nivel_jerarquico = 'gerente'");
    }

    public function down(): void
    {
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'gerente' WHERE nivel_jerarquico = 'gerencia'");
        DB::statement("ALTER TABLE catalogo_servicios MODIFY nivel_jerarquico ENUM('todos','operativo','auxiliar','tecnico','administrativo','analista','supervisor','coordinador','gerente','director','directivo') NOT NULL DEFAULT 'todos'");
    }
};
