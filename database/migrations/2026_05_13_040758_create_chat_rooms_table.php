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
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['directo', 'grupal'])->default('directo');
            $table->string('nombre', 180)->nullable();
            
            $table->foreignId('creado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('direct_user_a_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('direct_user_b_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->unique(['tipo', 'direct_user_a_id', 'direct_user_b_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
