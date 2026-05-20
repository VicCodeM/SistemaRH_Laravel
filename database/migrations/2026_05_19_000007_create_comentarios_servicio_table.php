<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentarios_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servicio_asignado_id');
            $table->unsignedBigInteger('user_id');
            $table->text('mensaje');
            $table->timestamps();

            $table->index('servicio_asignado_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comentarios_servicio');
    }
};
