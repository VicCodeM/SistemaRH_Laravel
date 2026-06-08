<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Se amplia para poder guardar varias areas/ramos separados por coma.
        DB::statement('ALTER TABLE vacantes MODIFY area_requerida VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE vacantes MODIFY area_requerida VARCHAR(150) NULL');
    }
};
