<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->text('notas_internas')->nullable()->after('cierre_motivo');
        });
    }

    public function down(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->dropColumn('notas_internas');
        });
    }
};
