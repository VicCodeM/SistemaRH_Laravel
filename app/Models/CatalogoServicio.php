<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoServicio extends Model
{
    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'nivel_jerarquico',
        'para_quien',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(ServicioAsignado::class, 'servicio_id');
    }

    public static function nivelesJerarquicos(): array
    {
        return CatalogoOpcion::opciones('niveles_jerarquicos', [
            'todos' => 'Todos los niveles',
            'operativo' => 'Operativo',
            'supervision' => 'Supervisión',
            'gerencia' => 'Gerencia',
            'direccion' => 'Dirección',
        ]);
    }

    public static function nivelesJerarquicosCompatibles(): array
    {
        return [
            'todos' => 'Todos los niveles',
            'operativo' => 'Operativo',
            'auxiliar' => 'Operativo',
            'tecnico' => 'Operativo',
            'administrativo' => 'Operativo',
            'supervision' => 'Supervisión',
            'supervisor' => 'Supervisión',
            'coordinador' => 'Supervisión',
            'gerencia' => 'Gerencia',
            'gerente' => 'Gerencia',
            'direccion' => 'Dirección',
            'director' => 'Dirección',
            'directivo' => 'Dirección',
        ];
    }

    public static function nivelesJerarquicosFormulario(bool $incluirTodos = false): array
    {
        $niveles = self::nivelesJerarquicos();

        if ($incluirTodos) {
            return $niveles;
        }

        return array_filter(
            $niveles,
            fn ($label, $key) => $key !== 'todos',
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function normalizarNivelJerarquico(?string $nivel): ?string
    {
        if ($nivel === null || $nivel === '') {
            return $nivel;
        }

        $nivel = strtolower(trim($nivel));

        return match ($nivel) {
            'todos' => 'todos',
            'operativo', 'auxiliar', 'tecnico', 'administrativo' => 'operativo',
            'supervision', 'supervisor', 'coordinador' => 'supervision',
            'gerencia', 'gerente' => 'gerencia',
            'direccion', 'director', 'directivo' => 'direccion',
            default => $nivel,
        };
    }

    public static function nivelJerarquicoLabel(?string $nivel): string
    {
        $nivelNormalizado = self::normalizarNivelJerarquico($nivel);

        return CatalogoOpcion::label(
            'niveles_jerarquicos',
            $nivelNormalizado,
            self::nivelesJerarquicosCompatibles()[$nivel] ?? ucfirst(str_replace('_', ' ', (string) $nivel))
        );
    }

    public static function tipos(): array
    {
        return CatalogoOpcion::opciones('tipos_servicio', [
            'reclutamiento' => 'Reclutamiento',
            'capacitacion' => 'Capacitación',
            'coaching' => 'Coaching',
            'evaluacion' => 'Evaluación / Assessment',
            'outplacement' => 'Outplacement',
            'nomina' => 'Nómina y IMSS',
            'consultoria' => 'Consultoría RH',
            'otro' => 'Otro',
        ]);
    }
}
