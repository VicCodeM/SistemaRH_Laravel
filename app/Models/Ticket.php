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
        'sla_due_at' => 'datetime',
        'resuelto_at' => 'datetime',
    ];

    public static function categorias(): array
    {
        return CatalogoOpcion::opciones('ticket_categorias', [
            'soporte_tecnico' => 'Soporte técnico',
            'vacante' => 'Vacante',
            'capacitacion' => 'Capacitación',
            'seguimiento' => 'Seguimiento',
            'reclutamiento' => 'Reclutamiento',
            'otro' => 'Otro',
        ]);
    }

    public static function prioridades(): array
    {
        return CatalogoOpcion::opciones('ticket_prioridades', [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ]);
    }

    public static function estados(): array
    {
        return CatalogoOpcion::opciones('ticket_estados', [
            'abierto' => 'Abierto',
            'en_proceso' => 'En proceso',
            'resuelto' => 'Resuelto',
            'cerrado' => 'Cerrado',
        ]);
    }

    public static function categoriaLabel(?string $categoria): string
    {
        return self::categorias()[$categoria] ?? ucfirst(str_replace('_', ' ', (string) $categoria));
    }

    public static function prioridadLabel(?string $prioridad): string
    {
        return self::prioridades()[$prioridad] ?? ucfirst((string) $prioridad);
    }

    public static function prioridadBadgeClass(?string $prioridad): string
    {
        return match ($prioridad) {
            'baja' => 'badge-gray',
            'media' => 'badge-blue',
            'alta' => 'badge-yellow',
            'urgente' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public static function estadoLabel(?string $estado): string
    {
        return self::estados()[$estado] ?? ucfirst(str_replace('_', ' ', (string) $estado));
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'abierto' => 'badge-yellow',
            'en_proceso' => 'badge-blue',
            'resuelto' => 'badge-green',
            'cerrado' => 'badge-gray',
            default => 'badge-gray',
        };
    }

    public function estaVencido(): bool
    {
        return (bool) $this->sla_due_at
            && $this->sla_due_at->isPast()
            && ! in_array($this->estado, ['resuelto', 'cerrado'], true);
    }

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
