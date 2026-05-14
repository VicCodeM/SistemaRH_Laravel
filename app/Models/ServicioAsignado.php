<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ServicioAsignado extends Model
{
    protected $table = 'servicios_asignados';

    protected $fillable = [
        'servicio_id', 'asignable_type', 'asignable_id',
        'estado', 'notas', 'asignado_por', 'fecha_inicio', 'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'servicio_id');
    }

    public function asignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
