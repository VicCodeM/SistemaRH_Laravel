<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diapositiva/imagen de la presentacion de una Vacante.
 * Reutiliza toda la logica de imagenes de CatalogoServicioRecurso
 * (url, thumbUrl, esImagen, tamanoHumano, etc.) pero apunta a su propia tabla.
 */
class VacanteRecurso extends CatalogoServicioRecurso
{
    protected $table = 'vacante_recursos';

    protected $fillable = [
        'vacante_id',
        'user_id',
        'tipo',
        'titulo',
        'descripcion',
        'archivo_path',
        'thumb_path',
        'archivo_original',
        'mime_type',
        'tamano_bytes',
        'orden',
    ];

    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class, 'vacante_id');
    }
}
