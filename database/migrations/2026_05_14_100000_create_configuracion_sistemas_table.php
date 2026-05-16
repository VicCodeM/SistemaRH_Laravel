<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistemas', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('grupo')->default('general');
            $table->string('tipo')->default('string');
            $table->text('valor')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        DB::table('configuracion_sistemas')->insert([
            [
                'clave' => 'candidato_requiere_aprobacion',
                'grupo' => 'accesos',
                'tipo' => 'boolean',
                'valor' => '0',
                'descripcion' => 'Si se activa, cada candidato debe ser aprobado antes de completar su solicitud.',
                'activo' => true,
                'orden' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistemas');
    }
};
