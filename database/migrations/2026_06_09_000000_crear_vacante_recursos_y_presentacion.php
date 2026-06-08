<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vacantes', 'presentacion_activa')) {
            Schema::table('vacantes', function (Blueprint $table) {
                $table->boolean('presentacion_activa')->default(false)->after('estado');
            });
        }

        if (! Schema::hasTable('vacante_recursos')) {
            Schema::create('vacante_recursos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo', 30)->default('presentacion');
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->string('archivo_path');
                $table->string('thumb_path')->nullable();
                $table->string('archivo_original');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('tamano_bytes')->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->timestamps();

                $table->index(['vacante_id', 'tipo', 'orden']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vacante_recursos');

        if (Schema::hasColumn('vacantes', 'presentacion_activa')) {
            Schema::table('vacantes', function (Blueprint $table) {
                $table->dropColumn('presentacion_activa');
            });
        }
    }
};
