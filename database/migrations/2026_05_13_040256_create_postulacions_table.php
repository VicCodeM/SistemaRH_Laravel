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
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacante_id')->constrained('vacantes')->onDelete('cascade');
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            
            // Lógica Kanban
            $table->enum('estado', ['postulado', 'entrevista', 'seleccionado', 'rechazado'])->default('postulado');
            
            $table->timestamp('fecha_postulacion')->useCurrent();
            $table->timestamps();
            
            // Un candidato no puede postularse dos veces a la misma vacante
            $table->unique(['vacante_id', 'candidato_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
