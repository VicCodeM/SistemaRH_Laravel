<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('carga_trabajo_horas')->default(0)->comment('Horas asignadas actualmente');
            $table->integer('capacidad_maxima_horas')->default(40)->comment('Horas disponibles/semana');
            $table->string('nivel_jerarquico')->nullable()->comment('operativo|supervision|gerencia|direccion');
            $table->string('departamento')->nullable()->comment('Área: RH, Finanzas, IT, etc');
            $table->enum('disponibilidad', ['disponible', 'de_licencia', 'fuera'])->default('disponible');
            $table->timestamp('disponible_desde')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'carga_trabajo_horas',
                'capacidad_maxima_horas',
                'nivel_jerarquico',
                'departamento',
                'disponibilidad',
                'disponible_desde',
            ]);
        });
    }
};
