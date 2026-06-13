<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulaciones', function (Blueprint $table) {
            $table->string('estado', 80)->default('recibida')->change();
        });

        DB::table('postulaciones')->where('estado', 'postulado')->update(['estado' => 'recibida']);
        DB::table('postulaciones')->where('estado', 'seleccionado')->update(['estado' => 'firma_contrato']);

        $now = now();

        DB::table('catalogo_opciones')
            ->where('grupo', 'postulacion_estados')
            ->update([
                'es_sistema' => false,
                'updated_at' => $now,
            ]);

        foreach ($this->estadosPostulacion($now) as $estado) {
            DB::table('catalogo_opciones')->updateOrInsert(
                [
                    'grupo' => 'postulacion_estados',
                    'clave' => $estado['clave'],
                ],
                $estado
            );
        }
    }

    public function down(): void
    {
        DB::table('postulaciones')->whereIn('estado', ['recibida', 'en_revision', 'referencias'])
            ->update(['estado' => 'postulado']);
        DB::table('postulaciones')->whereIn('estado', ['firma_contrato', 'capacitacion'])
            ->update(['estado' => 'seleccionado']);
        DB::table('postulaciones')->where('estado', 'pendiente_proxima_vacante')
            ->update(['estado' => 'rechazado']);

        Schema::table('postulaciones', function (Blueprint $table) {
            $table->string('estado', 80)->default('postulado')->change();
        });
    }

    private function estadosPostulacion($now): array
    {
        return [
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'recibida',
                'valor' => 'Recibida',
                'descripcion' => 'Solicitud recibida por el equipo RH',
                'activo' => true,
                'orden' => 10,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'en_revision',
                'valor' => 'En revisión',
                'descripcion' => 'Candidato en revisión por el equipo RH',
                'activo' => true,
                'orden' => 20,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'referencias',
                'valor' => 'Referencias',
                'descripcion' => 'Se están validando referencias',
                'activo' => true,
                'orden' => 30,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'entrevista',
                'valor' => 'Entrevista',
                'descripcion' => 'Candidato en etapa de entrevista',
                'activo' => true,
                'orden' => 40,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'pendiente_proxima_vacante',
                'valor' => 'Pendiente próxima vacante',
                'descripcion' => 'Candidato reservado para otra oportunidad',
                'activo' => true,
                'orden' => 50,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'firma_contrato',
                'valor' => 'Firma de contrato',
                'descripcion' => 'Candidato en proceso de firma',
                'activo' => true,
                'orden' => 60,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'capacitacion',
                'valor' => 'Capacitación',
                'descripcion' => 'Candidato en capacitación inicial',
                'activo' => true,
                'orden' => 70,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'rechazado',
                'valor' => 'Rechazado',
                'descripcion' => 'Candidato rechazado',
                'activo' => false,
                'orden' => 90,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'retirado',
                'valor' => 'Retirado',
                'descripcion' => 'Candidato retiró su proceso',
                'activo' => false,
                'orden' => 100,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'postulado',
                'valor' => 'Postulado (anterior)',
                'descripcion' => 'Estado anterior conservado por compatibilidad',
                'activo' => false,
                'orden' => 110,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'grupo' => 'postulacion_estados',
                'clave' => 'seleccionado',
                'valor' => 'Seleccionado (anterior)',
                'descripcion' => 'Estado anterior conservado por compatibilidad',
                'activo' => false,
                'orden' => 120,
                'es_sistema' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
};
