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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_empresa', 200);
            $table->string('rfc', 20);
            $table->string('telefono', 30);
            $table->string('direccion', 255);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente','activa','rechazada'])->default('pendiente');
            
            // Datos Fiscales y de RH extra
            $table->string('razon_social', 200)->nullable();
            $table->string('nombre_rh', 150)->nullable();
            $table->string('telefono_directo', 30)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('pagina_web', 150)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
