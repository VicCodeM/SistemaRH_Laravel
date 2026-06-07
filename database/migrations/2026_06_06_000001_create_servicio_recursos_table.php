<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_asignado_id')->constrained('servicios_asignados')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 30)->default('archivo');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('archivo_path');
            $table->string('archivo_original');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['servicio_asignado_id', 'tipo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_recursos');
    }
};
