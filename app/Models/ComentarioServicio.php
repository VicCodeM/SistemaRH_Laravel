<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioServicio extends Model
{
    protected $table = 'comentarios_servicio';

    protected $fillable = [
        'servicio_asignado_id',
        'user_id',
        'mensaje',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(ServicioAsignado::class, 'servicio_asignado_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
