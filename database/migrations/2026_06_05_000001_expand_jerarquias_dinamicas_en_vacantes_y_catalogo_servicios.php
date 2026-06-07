<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('vacantes', function (Blueprint $table) {
            $table->string('nivel_jerarquico', 50)->default('operativo')->change();
        });

        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->string('nivel_jerarquico', 50)->default('todos')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE vacantes SET nivel_jerarquico = 'operativo' WHERE nivel_jerarquico NOT IN ('operativo', 'administrativo', 'analista', 'supervision', 'coordinador', 'gerencia', 'directivo', 'director')");
        DB::statement("UPDATE catalogo_servicios SET nivel_jerarquico = 'todos' WHERE nivel_jerarquico NOT IN ('todos', 'operativo', 'auxiliar', 'tecnico', 'administrativo', 'analista', 'supervisor', 'coordinador', 'gerencia', 'director', 'directivo')");

        Schema::table('vacantes', function (Blueprint $table) {
            $table->enum('nivel_jerarquico', ['operativo', 'administrativo', 'analista', 'supervision', 'coordinador', 'gerencia', 'directivo', 'director'])->default('operativo')->change();
        });

        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->enum('nivel_jerarquico', ['todos', 'operativo', 'auxiliar', 'tecnico', 'administrativo', 'analista', 'supervisor', 'coordinador', 'gerencia', 'director', 'directivo'])->default('todos')->change();
        });
    }
};
