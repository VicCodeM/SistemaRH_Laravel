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
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('asunto');
            $table->timestamp('sla_due_at')->nullable()->after('estado');
            $table->timestamp('resuelto_at')->nullable()->after('sla_due_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'sla_due_at', 'resuelto_at']);
        });
    }
};
