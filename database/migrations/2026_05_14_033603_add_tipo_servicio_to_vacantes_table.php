<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->enum('tipo_servicio', [
                'reclutamiento', 'capacitacion', 'coaching',
                'evaluacion', 'outplacement', 'nomina', 'consultoria', 'otro'
            ])->default('reclutamiento')->after('empresa_id');
            $table->text('requerimientos')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->dropColumn(['tipo_servicio', 'requerimientos']);
        });
    }
};
