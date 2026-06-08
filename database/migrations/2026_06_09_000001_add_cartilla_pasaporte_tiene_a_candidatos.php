<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            if (! Schema::hasColumn('candidatos', 'cartilla_tiene')) {
                $table->string('cartilla_tiene', 3)->nullable()->after('cartilla_militar');
            }
            if (! Schema::hasColumn('candidatos', 'pasaporte_tiene')) {
                $table->string('pasaporte_tiene', 3)->nullable()->after('pasaporte');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            if (Schema::hasColumn('candidatos', 'cartilla_tiene')) {
                $table->dropColumn('cartilla_tiene');
            }
            if (Schema::hasColumn('candidatos', 'pasaporte_tiene')) {
                $table->dropColumn('pasaporte_tiene');
            }
        });
    }
};
