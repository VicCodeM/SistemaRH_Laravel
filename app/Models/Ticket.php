<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'empresa_id', 'asignado_a', 'asunto', 'descripcion', 'categoria',
        'prioridad', 'estado', 'sla_due_at', 'resuelto_at',
    ];

    protected $casts = [
        'sla_due_at'  => 'datetime',
        'resuelto_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }
}
