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
        'ubicacion',
        'tipo_contrato',
        'estado',
        'fecha_publicacion',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'salario_min' => 'decimal:2',
        'salario_max' => 'decimal:2',
        'experiencia_minima' => 'integer',
    ];

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

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }
}
