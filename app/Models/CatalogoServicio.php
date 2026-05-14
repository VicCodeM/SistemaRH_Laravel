<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoServicio extends Model
{
    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'nombre', 'descripcion', 'tipo', 'nivel_jerarquico',
        'para_quien', 'activo', 'orden',
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
        return [
            'todos'          => 'Todos los niveles',
            'operativo'      => 'Operativo',
            'auxiliar'       => 'Auxiliar / Asistente',
            'tecnico'        => 'Técnico',
            'administrativo' => 'Administrativo / Analista',
            'supervisor'     => 'Supervisor',
            'coordinador'    => 'Coordinador',
            'gerente'        => 'Gerente',
            'director'       => 'Director',
            'directivo'      => 'Directivo / C-Level',
        ];
    }

    public static function tipos(): array
    {
        return [
            'reclutamiento' => 'Reclutamiento',
            'capacitacion'  => 'Capacitación',
            'coaching'      => 'Coaching',
            'evaluacion'    => 'Evaluación / Assessment',
            'outplacement'  => 'Outplacement',
            'nomina'        => 'Nómina y IMSS',
            'consultoria'   => 'Consultoría RH',
            'otro'          => 'Otro',
        ];
    }
}
