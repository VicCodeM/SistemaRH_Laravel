<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->text('ingresos_ofrecidos')->nullable();
            $table->text('prestaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vacantes', function (Blueprint $table) {
            $table->dropColumn(['ingresos_ofrecidos', 'prestaciones']);
        });
    }
};
