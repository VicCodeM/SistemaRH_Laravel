<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ServicioAsignado extends Model
{
    protected $table = 'servicios_asignados';

    protected $fillable = [
        'servicio_id', 'nivel_jerarquico', 'horas_estimadas',
        'asignable_type', 'asignable_id',
        'estado', 'notas', 'cierre_resumen',
        'asignado_a', 'asignado_por',
        'fecha_inicio', 'fecha_fin',
        'solicitado_por', 'vacante_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    /**
     * Etiquetas de los estados. SIEMPRE incluye los 5 estados base.
     * Si en BD se personaliza un label, se usa el de BD.
     */
    public static function estados(): array
    {
        $base = [
            'pendiente'   => 'Pendiente',
            'activo'      => 'Activo',
            'en_proceso'  => 'En proceso',
            'completado'  => 'Completado',
            'cancelado'   => 'Cancelado',
        ];

        return array_merge($base, CatalogoOpcion::opciones('servicio_asignado_estados', []));
    }

    public static function estadosKanban(): array
    {
        return [
            'pendiente'  => 'Pendiente',
            'activo'     => 'Activo',
            'en_proceso' => 'En proceso',
            'completado' => 'Completado',
        ];
    }

    public static function estadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('servicio_asignado_estados', $estado);
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'pendiente'  => 'badge-orange',
            'activo'     => 'badge-blue',
            'en_proceso' => 'badge-yellow',
            'completado' => 'badge-green',
            'cancelado'  => 'badge-gray',
            default      => 'badge-gray',
        };
    }

    public static function asignableTipoLabel(?string $tipo): string
    {
        return match ($tipo) {
            Empresa::class   => 'Empresa',
            Candidato::class => 'Candidato',
            User::class      => 'Personal interno',
            default          => ucfirst(class_basename((string) $tipo)),
        };
    }

    public function asignableNombre(): string
    {
        return match (true) {
            $this->asignable instanceof Empresa   => $this->asignable->nombre_empresa,
            $this->asignable instanceof Candidato => $this->asignable->nombreCompleto(),
            $this->asignable instanceof User      => $this->asignable->name,
            default                               => 'Sin asignar',
        };
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

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class, 'vacante_id');
    }

    public function comentarios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ComentarioServicio::class, 'servicio_asignado_id')
            ->with('autor')
            ->latest();
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function estaVigente(): bool
    {
        return in_array($this->estado, ['activo', 'en_proceso', 'pendiente'], true);
    }

    public function puedeAsignar(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function puedeIniciar(): bool
    {
        return $this->estado === 'activo';
    }

    public function puedeCompletar(): bool
    {
        return $this->estado === 'en_proceso';
    }
}
