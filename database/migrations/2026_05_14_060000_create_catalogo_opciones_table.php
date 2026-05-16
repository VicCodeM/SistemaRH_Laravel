<?php

use App\Models\CatalogoOpcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_opciones', function (Blueprint $table) {
            $table->id();
            $table->string('grupo', 100);
            $table->string('clave', 100);
            $table->string('valor', 150);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('es_sistema')->default(false);
            $table->timestamps();

            $table->unique(['grupo', 'clave']);
            $table->index(['grupo', 'activo', 'orden']);
        });

        DB::table('catalogo_opciones')->insert(CatalogoOpcion::defaults());
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_opciones');
    }
};
