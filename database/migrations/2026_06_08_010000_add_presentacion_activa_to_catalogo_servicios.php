<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->boolean('presentacion_activa')
                ->default(false)
                ->after('activo');
        });

        if (! Schema::hasTable('catalogo_servicio_recursos')) {
            return;
        }

        $idsConRecursos = DB::table('catalogo_servicio_recursos')
            ->select('catalogo_servicio_id')
            ->distinct()
            ->pluck('catalogo_servicio_id')
            ->filter()
            ->values();

        if ($idsConRecursos->isEmpty()) {
            return;
        }

        DB::table('catalogo_servicios')
            ->whereIn('id', $idsConRecursos)
            ->update(['presentacion_activa' => true]);
    }

    public function down(): void
    {
        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->dropColumn('presentacion_activa');
        });
    }
};
