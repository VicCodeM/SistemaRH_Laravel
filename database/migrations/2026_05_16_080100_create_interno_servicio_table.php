<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interno_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('catalogo_servicios')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interno_servicio');
    }
};
