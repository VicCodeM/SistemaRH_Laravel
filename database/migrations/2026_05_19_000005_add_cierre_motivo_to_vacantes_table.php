<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->string('cierre_motivo', 500)->nullable()->after('cupos');
            $table->timestamp('fecha_cierre')->nullable()->after('cierre_motivo');
        });
    }

    public function down(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->dropColumn(['cierre_motivo', 'fecha_cierre']);
        });
    }
};
