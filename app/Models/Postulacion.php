<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    public const ESTADO_INICIAL = 'recibida';

    private const ESTADOS_INACTIVOS_BASE = [
        'pendiente_proxima_vacante',
        'rechazado',
        'retirado',
    ];

    private const ESTADOS_OCUPAN_CUPO = [
        'seleccionado',
        'firma_contrato',
        'capacitacion',
    ];

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
            'recibida' => 'Recibida',
            'en_revision' => 'En revisión',
            'referencias' => 'Referencias',
            'entrevista' => 'Entrevista',
            'pendiente_proxima_vacante' => 'Pendiente próxima vacante',
            'firma_contrato' => 'Firma de contrato',
            'capacitacion' => 'Capacitación',
            'rechazado' => 'Rechazado',
            'retirado' => 'Retirado',
            'postulado' => 'Postulado',
        ]);
    }

    public static function estadoInicial(): string
    {
        $estados = self::estadosProceso();

        if (array_key_exists(self::ESTADO_INICIAL, $estados)) {
            return self::ESTADO_INICIAL;
        }

        return array_key_first($estados) ?? 'postulado';
    }

    public static function estadosActivos(): array
    {
        $estados = array_keys(self::estadosProceso());
        $activos = array_values(array_diff($estados, self::estadosInactivos()));

        return $activos ?: [self::estadoInicial()];
    }

    public static function estadosInactivos(): array
    {
        return self::ESTADOS_INACTIVOS_BASE;
    }

    public static function estadosOcupanCupo(): array
    {
        return self::ESTADOS_OCUPAN_CUPO;
    }

    public static function estadoOcupaCupo(?string $estado): bool
    {
        return in_array($estado, self::estadosOcupanCupo(), true);
    }

    public static function puedeEliminarPorCandidato(?string $estado): bool
    {
        return $estado === self::estadoInicial() || $estado === 'postulado';
    }

    public static function estadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('postulacion_estados', $estado, ucfirst(str_replace('_', ' ', (string) $estado)));
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'recibida', 'postulado' => 'badge-blue',
            'en_revision', 'referencias', 'entrevista' => 'badge-yellow',
            'pendiente_proxima_vacante' => 'badge-orange',
            'firma_contrato', 'capacitacion', 'seleccionado' => 'badge-green',
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
