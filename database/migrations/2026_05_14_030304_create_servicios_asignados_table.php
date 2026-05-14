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
        Schema::create('servicios_asignados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('catalogo_servicios')->onDelete('cascade');
            // Polimórfico: puede asignarse a empresa o candidato
            $table->string('asignable_type'); // App\Models\Empresa | App\Models\Candidato
            $table->unsignedBigInteger('asignable_id');
            $table->enum('estado', ['activo', 'en_proceso', 'completado', 'cancelado'])->default('activo');
            $table->text('notas')->nullable();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();

            $table->index(['asignable_type', 'asignable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_asignados');
    }
};
