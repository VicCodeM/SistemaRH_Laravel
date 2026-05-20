<?php

use App\Models\CatalogoOpcion;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // upsert: agrega los nuevos grupos sin tocar los existentes
        CatalogoOpcion::seedDefaults();
    }

    public function down(): void
    {
        CatalogoOpcion::whereIn('grupo', ['areas_carreras', 'tipos_contrato', 'sectores_empresa'])->delete();
    }
};
