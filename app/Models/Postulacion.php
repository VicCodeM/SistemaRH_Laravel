<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    protected $fillable = [
        'candidato_id',
        'vacante_id',
        'estado',
        'fecha_postulacion',
        'asignacion_forzada',
        'motivo_asignacion',
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
        'asignacion_forzada' => 'boolean',
    ];

    public static function estadosProceso(): array
    {
        return CatalogoOpcion::opciones('postulacion_estados', [
            'postulado' => 'En revisión',
            'entrevista' => 'Ya entrevistado',
            'seleccionado' => 'Seleccionado',
            'rechazado' => 'Rechazado',
            'retirado' => 'Retirado',
        ]);
    }

    public static function estadosActivos(): array
    {
        return ['postulado', 'entrevista', 'seleccionado'];
    }

    public static function estadosInactivos(): array
    {
        return ['rechazado', 'retirado'];
    }

    public static function estadoLabel(?string $estado): string
    {
        return self::estadosProceso()[$estado] ?? ucfirst((string) $estado);
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'postulado' => 'badge-blue',
            'entrevista' => 'badge-yellow',
            'seleccionado' => 'badge-green',
            'rechazado' => 'badge-red',
            'retirado' => 'badge-gray',
            default => 'badge-gray',
        };
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    public function vacante()
    {
        return $this->belongsTo(Vacante::class);
    }
}
