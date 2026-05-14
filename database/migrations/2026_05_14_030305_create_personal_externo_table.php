<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_externo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellidos', 150);
            $table->string('email')->unique();
            $table->string('telefono', 20)->nullable();
            $table->enum('especialidad', [
                'reclutamiento', 'capacitacion', 'coaching',
                'evaluacion', 'outplacement', 'nomina', 'consultoria', 'otro'
            ]);
            // Niveles en los que puede colaborar (puede ser múltiple, se guarda como JSON)
            $table->json('niveles_jerarquicos');
            $table->string('empresa_o_razon_social', 200)->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('disponibilidad', ['disponible', 'ocupado', 'inactivo'])->default('disponible');
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_externo');
    }
};
