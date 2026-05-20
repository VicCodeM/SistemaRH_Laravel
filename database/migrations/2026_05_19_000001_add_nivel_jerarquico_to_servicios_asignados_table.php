<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->string('nivel_jerarquico', 50)->nullable()->after('servicio_id');
        });
    }

    public function down(): void
    {
        Schema::table('servicios_asignados', function (Blueprint $table) {
            $table->dropColumn('nivel_jerarquico');
        });
    }
};
