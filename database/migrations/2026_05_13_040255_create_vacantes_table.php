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
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            
            // Jerarquía (¡Nueva lógica!)
            $table->enum('nivel_jerarquico', ['operativo', 'administrativo', 'supervisor', 'gerente', 'directivo'])->default('operativo');
            
            $table->decimal('salario_min', 10, 2)->nullable();
            $table->decimal('salario_max', 10, 2)->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->string('tipo_contrato', 80)->nullable();
            $table->enum('estado', ['activa', 'pendiente', 'cerrada'])->default('activa');
            
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};
