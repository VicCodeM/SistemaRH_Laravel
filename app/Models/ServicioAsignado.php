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
        'estado', 'notas', 'cierre_resumen', 'asignado_a', 'asignado_por', 'fecha_inicio', 'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public static function estados(): array
    {
        return CatalogoOpcion::opciones('servicio_asignado_estados', [
            'activo'      => 'Activo',
            'en_proceso'  => 'En proceso',
            'completado'  => 'Completado',
            'cancelado'   => 'Cancelado',
        ]);
    }

    public static function estadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('servicio_asignado_estados', $estado);
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'activo' => 'badge-blue',
            'en_proceso' => 'badge-yellow',
            'completado' => 'badge-green',
            'cancelado' => 'badge-gray',
            default => 'badge-gray',
        };
    }

    public static function asignableTipoLabel(?string $tipo): string
    {
        return match ($tipo) {
            Empresa::class => 'Empresa',
            Candidato::class => 'Candidato',
            default => ucfirst(class_basename((string) $tipo)),
        };
    }

    public function asignableNombre(): string
    {
        if ($this->asignable instanceof Empresa) {
            return $this->asignable->nombre_empresa;
        }

        if ($this->asignable instanceof Candidato) {
            return $this->asignable->nombreCompleto();
        }

        return 'Sin asignar';
    }

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

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function estaVigente(): bool
    {
        return in_array($this->estado, ['activo', 'en_proceso'], true);
    }
}
