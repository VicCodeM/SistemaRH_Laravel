<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::rename('servicios_asignados', 'servicios_asignados_old');

            Schema::create('servicios_asignados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('servicio_id')->constrained('catalogo_servicios')->onDelete('cascade');
                $table->string('asignable_type');
                $table->unsignedBigInteger('asignable_id');
                $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
                $table->string('estado', 20)->default('pendiente');
                $table->text('notas')->nullable();
                $table->text('cierre_resumen')->nullable();
                $table->foreignId('asignado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('fecha_inicio')->nullable();
                $table->timestamp('fecha_fin')->nullable();
                $table->foreignId('solicitado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('vacante_id')->nullable()->constrained('vacantes')->nullOnDelete();
                $table->timestamps();
                $table->index(['asignable_type', 'asignable_id']);
            });

            $cols = 'id, servicio_id, asignable_type, asignable_id, asignado_a, estado, notas, cierre_resumen, asignado_por, fecha_inicio, fecha_fin, NULL, NULL, created_at, updated_at';
            DB::statement("INSERT INTO servicios_asignados ({$cols}) SELECT {$cols} FROM servicios_asignados_old");

            Schema::drop('servicios_asignados_old');
        } else {
            Schema::table('servicios_asignados', function (Blueprint $table) {
                $table->string('estado', 20)->default('pendiente')->change();
                $table->foreignId('solicitado_por')->nullable()->after('asignado_por')->constrained('users')->nullOnDelete();
                $table->foreignId('vacante_id')->nullable()->after('solicitado_por')->constrained('vacantes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::rename('servicios_asignados', 'servicios_asignados_old');

            Schema::create('servicios_asignados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('servicio_id')->constrained('catalogo_servicios')->onDelete('cascade');
                $table->string('asignable_type');
                $table->unsignedBigInteger('asignable_id');
                $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('estado', ['activo', 'en_proceso', 'completado', 'cancelado'])->default('activo');
                $table->text('notas')->nullable();
                $table->text('cierre_resumen')->nullable();
                $table->foreignId('asignado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('fecha_inicio')->nullable();
                $table->timestamp('fecha_fin')->nullable();
                $table->timestamps();
                $table->index(['asignable_type', 'asignable_id']);
            });

            $cols = 'id, servicio_id, asignable_type, asignable_id, asignado_a, estado, notas, cierre_resumen, asignado_por, fecha_inicio, fecha_fin, created_at, updated_at';
            DB::statement("INSERT INTO servicios_asignados ({$cols}) SELECT {$cols} FROM servicios_asignados_old");

            Schema::drop('servicios_asignados_old');
        } else {
            Schema::table('servicios_asignados', function (Blueprint $table) {
                $table->dropForeign(['solicitado_por']);
                $table->dropForeign(['vacante_id']);
                $table->dropColumn(['solicitado_por', 'vacante_id']);
                $table->enum('estado', ['activo', 'en_proceso', 'completado', 'cancelado'])->default('activo')->change();
            });
        }
    }
};
