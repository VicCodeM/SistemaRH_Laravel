<?php

use App\Models\CatalogoOpcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->string('nivel_estudios_minimo', 50)->nullable()->after('nivel_jerarquico');
            $table->string('area_requerida', 150)->nullable()->after('nivel_estudios_minimo');
            $table->unsignedInteger('experiencia_minima')->nullable()->after('area_requerida');
        });

        Schema::table('postulaciones', function (Blueprint $table) {
            $table->boolean('asignacion_forzada')->default(false)->after('estado');
            $table->text('motivo_asignacion')->nullable()->after('asignacion_forzada');
        });

        CatalogoOpcion::seedDefaults();
    }

    public function down(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->dropColumn(['asignacion_forzada', 'motivo_asignacion']);
        });

        Schema::table('vacantes', function (Blueprint $table) {
            $table->dropColumn(['nivel_estudios_minimo', 'area_requerida', 'experiencia_minima']);
        });
    }
};
