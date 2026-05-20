<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->unsignedInteger('horas_estimadas')->default(0)->after('nivel_jerarquico');
        });
    }

    public function down(): void
    {
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->dropColumn('horas_estimadas');
        });
    }
};
