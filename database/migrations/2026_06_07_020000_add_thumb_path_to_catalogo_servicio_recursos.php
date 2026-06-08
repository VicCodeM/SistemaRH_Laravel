<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogo_servicio_recursos', function (Blueprint $table) {
            $table->string('thumb_path')->nullable()->after('archivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('catalogo_servicio_recursos', function (Blueprint $table) {
            $table->dropColumn('thumb_path');
        });
    }
};
