<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CatalogoServicio extends Model
{
    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'nivel_jerarquico',
        'para_quien',
        'flujo',
        'activo',
        'presentacion_activa',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'presentacion_activa' => 'boolean',
    ];

    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    public function scopeVisiblesParaRol(Builder $query, string $rol, bool $soloActivos = true): void
    {
        if ($soloActivos) {
            $query->activos();
        }

        match ($rol) {
            'empresa' => $query->whereIn('para_quien', ['empresa', 'ambos']),
            'candidato' => $query
                ->whereIn('para_quien', ['candidato', 'ambos'])
                ->where('flujo', 'servicio'),
            default => $query,
        };
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(ServicioAsignado::class, 'servicio_id');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(CatalogoServicioRecurso::class, 'catalogo_servicio_id')
            ->with('subidoPor')
            ->orderBy('tipo', 'desc')
            ->orderBy('orden')
            ->orderBy('created_at');
    }

    public function tienePresentacionActiva(): bool
    {
        return (bool) ($this->presentacion_activa ?? false);
    }

    public static function tieneTablaRecursos(): bool
    {
        static $tieneTabla = null;

        if ($tieneTabla !== null) {
            return $tieneTabla;
        }

        return $tieneTabla = Schema::hasTable('catalogo_servicio_recursos');
    }

    public function tieneSolicitudesActivas(): bool
    {
        return $this->asignaciones()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->exists();
    }

    public function tieneSolicitudesRelacionadas(): bool
    {
        return $this->asignaciones()->exists();
    }

    public function puedeDesactivarse(): bool
    {
        return ! $this->tieneSolicitudesActivas();
    }

    public static function flujos(): array
    {
        return [
            'servicio' => 'Servicio normal',
            'vacante' => 'Solicitud de vacante',
        ];
    }

    public function flujoLabel(): string
    {
        return self::flujos()[$this->flujo ?? 'servicio'] ?? 'Servicio normal';
    }

    public function esFlujoVacante(): bool
    {
        return ($this->flujo ?? 'servicio') === 'vacante';
    }

    public function usaNivelJerarquicoPara(string $rol): bool
    {
        return $rol === 'empresa' && ! $this->esFlujoVacante();
    }

    public static function nivelesJerarquicos(): array
    {
        return CatalogoOpcion::opciones('niveles_jerarquicos', [
            'todos' => 'Todos los niveles',
            'operativo' => 'Operativo',
            'supervision' => "Supervisi\u{00F3}n",
            'gerencia' => 'Gerencia',
            'direccion' => "Direcci\u{00F3}n",
        ]);
    }

    public static function nivelesJerarquicosCompatibles(): array
    {
        $niveles = self::nivelesJerarquicos();

        $fallback = [
            'todos' => 'Todos los niveles',
            'operativo' => 'Operativo',
            'analista' => 'Analista',
            'supervision' => "Supervisi\u{00F3}n",
            'gerencia' => 'Gerencia',
            'direccion' => "Direcci\u{00F3}n",
        ];

        $aliases = [
            'auxiliar' => 'Operativo',
            'tecnico' => 'Operativo',
            'administrativo' => 'Operativo',
            'supervisor' => "Supervisi\u{00F3}n",
            'coordinador' => "Supervisi\u{00F3}n",
            'gerente' => 'Gerencia',
            'director' => "Direcci\u{00F3}n",
            'directivo' => "Direcci\u{00F3}n",
        ];

        foreach (array_merge($fallback, $aliases) as $clave => $label) {
            $niveles[$clave] ??= $label;
        }

        return $niveles;
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

        $nivel = Str::of($nivel)->ascii()->lower()->trim()->value();

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
            self::nivelesJerarquicosCompatibles()[$nivelNormalizado] ?? ucfirst(str_replace('_', ' ', (string) $nivelNormalizado))
        );
    }

    public static function tipos(): array
    {
        return CatalogoOpcion::opciones('tipos_servicio', [
            'reclutamiento' => 'Reclutamiento',
            'capacitacion' => "Capacitaci\u{00F3}n",
            'coaching' => 'Coaching',
            'evaluacion' => "Evaluaci\u{00F3}n / Assessment",
            'outplacement' => 'Outplacement',
            'nomina' => "N\u{00F3}mina y IMSS",
            'consultoria' => "Consultor\u{00ED}a RH",
            'otro' => 'Otro',
        ]);
    }
}
