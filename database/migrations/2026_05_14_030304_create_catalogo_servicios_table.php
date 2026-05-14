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
        Schema::create('catalogo_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', [
                'reclutamiento', 'capacitacion', 'coaching',
                'evaluacion', 'outplacement', 'nomina', 'consultoria', 'otro'
            ])->default('reclutamiento');
            $table->enum('nivel_jerarquico', [
                'todos', 'operativo', 'auxiliar', 'tecnico',
                'administrativo', 'analista', 'supervisor',
                'coordinador', 'gerente', 'director', 'directivo'
            ])->default('todos');
            $table->enum('para_quien', ['empresa', 'candidato', 'ambos'])->default('empresa');
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_servicios');
    }
};
