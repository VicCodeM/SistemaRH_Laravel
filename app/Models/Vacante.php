<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vacante extends Model
{
    protected $fillable = [
        'empresa_id',
        'tipo_servicio',
        'titulo',
        'descripcion',
        'requerimientos',
        'nivel_jerarquico',
        'nivel_estudios_minimo',
        'area_requerida',
        'experiencia_minima',
        'salario_min',
        'salario_max',
        'ingresos_ofrecidos',
        'prestaciones',
        'ubicacion',
        'tipo_contrato',
        'cupos',
        'cierre_motivo',
        'fecha_cierre',
        'notas_internas',
        'estado',
        'presentacion_activa',
        'fecha_publicacion',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'salario_min' => 'decimal:2',
        'salario_max' => 'decimal:2',
        'experiencia_minima' => 'integer',
        'cupos' => 'integer',
        'fecha_cierre' => 'datetime',
        'presentacion_activa' => 'boolean',
    ];

    public static function tieneTablaRecursos(): bool
    {
        static $tieneTabla = null;

        if ($tieneTabla !== null) {
            return $tieneTabla;
        }

        return $tieneTabla = \Illuminate\Support\Facades\Schema::hasTable('vacante_recursos');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(VacanteRecurso::class, 'vacante_id');
    }

    /**
     * Candidatos ya seleccionados (cupos ocupados).
     * Cuenta postulaciones en estado 'seleccionado' (los retirados/rechazados no cuentan).
     */
    public function cuposCubiertos(): int
    {
        return $this->postulaciones()
            ->where('estado', 'seleccionado')
            ->count();
    }

    public function cuposLibres(): int
    {
        return max(0, ($this->cupos ?? 1) - $this->cuposCubiertos());
    }

    public function estaLlena(): bool
    {
        return $this->cuposLibres() <= 0;
    }

    public function puedeRecibirMasCandidatos(): bool
    {
        return ! $this->estaLlena() && in_array($this->estado, ['pendiente', 'activa'], true);
    }

    public static function tiposServicio(): array
    {
        return CatalogoOpcion::opciones('tipos_servicio', [
            'reclutamiento' => 'Reclutamiento de personal',
            'capacitacion' => 'Capacitación',
            'coaching' => 'Coaching ejecutivo',
            'evaluacion' => 'Evaluación / Assessment',
            'outplacement' => 'Outplacement',
            'nomina' => 'Nómina y IMSS',
            'consultoria' => 'Consultoría RH',
            'otro' => 'Otro',
        ]);
    }

    public static function estados(): array
    {
        return CatalogoOpcion::opciones('vacante_estados', [
            'pendiente' => 'Pendiente',
            'activa' => 'Activa',
            'cerrada' => 'Cerrada',
            'rechazada' => 'Rechazada',
        ]);
    }

    public static function nivelesEstudios(): array
    {
        return CatalogoOpcion::opciones('niveles_estudios', [
            'sin_estudios' => 'Sin estudios',
            'primaria' => 'Primaria',
            'secundaria' => 'Secundaria',
            'preparatoria' => 'Preparatoria / Bachillerato',
            'tecnico' => 'Técnico',
            'licenciatura' => 'Licenciatura',
            'ingenieria' => 'Ingeniería',
            'maestria' => 'Maestría',
            'doctorado' => 'Doctorado',
        ]);
    }

    public static function nivelesEstudiosOrdenados(): array
    {
        return array_keys(self::nivelesEstudios());
    }

    public static function nivelesEstudiosDesde(?string $nivel): array
    {
        $nivel = self::normalizarNivelEstudios($nivel);

        if ($nivel === null) {
            return [];
        }

        $niveles = self::nivelesEstudiosOrdenados();
        $indice = array_search($nivel, $niveles, true);

        return $indice === false ? [] : array_slice($niveles, $indice);
    }

    public static function normalizarNivelEstudios(?string $nivel): ?string
    {
        if ($nivel === null || $nivel === '') {
            return null;
        }

        $nivel = Str::of($nivel)->ascii()->lower()->trim()->value();

        return match (true) {
            Str::contains($nivel, 'doctor') => 'doctorado',
            Str::contains($nivel, 'maestr') => 'maestria',
            Str::contains($nivel, 'ingenier') => 'ingenieria',
            Str::contains($nivel, 'licenci') => 'licenciatura',
            Str::contains($nivel, 'bachiller') => 'preparatoria',
            Str::contains($nivel, 'preparator') => 'preparatoria',
            Str::contains($nivel, 'tecnic') => 'tecnico',
            Str::contains($nivel, 'secund') => 'secundaria',
            Str::contains($nivel, 'primari') => 'primaria',
            Str::contains($nivel, 'sin estudios') => 'sin_estudios',
            default => array_key_exists($nivel, self::nivelesEstudios()) ? $nivel : null,
        };
    }

    public static function nivelEstudiosLabel(?string $nivel): string
    {
        $nivel = self::normalizarNivelEstudios($nivel);

        return CatalogoOpcion::label('niveles_estudios', $nivel, 'Sin definir');
    }

    public static function nivelEstudiosScore(?string $nivel): int
    {
        return match (self::normalizarNivelEstudios($nivel)) {
            'sin_estudios' => 0,
            'primaria' => 1,
            'secundaria' => 2,
            'preparatoria' => 3,
            'tecnico' => 4,
            'licenciatura', 'ingenieria' => 5,
            'maestria' => 6,
            'doctorado' => 7,
            default => 0,
        };
    }

    public static function estadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('vacante_estados', $estado);
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'badge-yellow',
            'activa' => 'badge-green',
            'cerrada' => 'badge-gray',
            'rechazada' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function salarioFormateado(): ?string
    {
        if ($this->salario_min === null && $this->salario_max === null) {
            return null;
        }

        $min = $this->salario_min !== null
            ? '$' . number_format((float) $this->salario_min, 0)
            : null;

        $max = $this->salario_max !== null
            ? '$' . number_format((float) $this->salario_max, 0)
            : null;

        return match (true) {
            $min !== null && $max !== null => "{$min} - {$max} MXN",
            $min !== null => "Desde {$min} MXN",
            $max !== null => "Hasta {$max} MXN",
            default => null,
        };
    }

    /**
     * Resumen de compensación para mostrar en vistas y modales.
     *
     * @return array<string, string>
     */
    public function compensacionDetalles(): array
    {
        $detalles = [];

        if ($salario = $this->salarioFormateado()) {
            $detalles['Sueldo'] = $salario;
        }

        if ($this->ingresos_ofrecidos) {
            $detalles['Ingresos ofrecidos'] = $this->ingresos_ofrecidos;
        }

        if ($this->prestaciones) {
            $detalles['Prestaciones'] = $this->prestaciones;
        }

        return $detalles;
    }

    public function requisitoResumen(): string
    {
        $partes = [];

        if ($this->nivel_estudios_minimo) {
            $partes[] = 'Estudios: ' . self::nivelEstudiosLabel($this->nivel_estudios_minimo);
        }

        if ($this->area_requerida) {
            $partes[] = 'Área: ' . $this->area_requerida;
        }

        if ($this->experiencia_minima !== null) {
            $partes[] = 'Experiencia: ' . $this->experiencia_minima . ' año(s)';
        }

        return $partes ? implode(' · ', $partes) : 'Sin requisitos estructurados';
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function serviciosAsignados(): HasMany
    {
        return $this->hasMany(ServicioAsignado::class, 'vacante_id');
    }

    public function esReclutamiento(): bool
    {
        return $this->tipo_servicio === 'reclutamiento';
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }
}
