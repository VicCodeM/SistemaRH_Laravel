<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->string('flujo', 20)
                ->default('servicio')
                ->after('para_quien');
        });
    }

    public function down(): void
    {
        Schema::table('catalogo_servicios', function (Blueprint $table) {
            $table->dropColumn('flujo');
        });
    }
};
