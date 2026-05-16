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
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->foreignId('asignado_a')
                ->nullable()
                ->after('asignable_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('cierre_resumen')->nullable()->after('notas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->dropForeign(['asignado_a']);
            $table->dropColumn(['asignado_a', 'cierre_resumen']);
        });
    }
};
