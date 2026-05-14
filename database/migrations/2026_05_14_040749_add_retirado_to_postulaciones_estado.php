<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE postulaciones MODIFY COLUMN estado ENUM('postulado','entrevista','seleccionado','rechazado','retirado') NOT NULL DEFAULT 'postulado'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE postulaciones MODIFY COLUMN estado ENUM('postulado','entrevista','seleccionado','rechazado') NOT NULL DEFAULT 'postulado'");
    }
};
