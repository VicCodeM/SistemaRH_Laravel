<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacante extends Model
{
    protected $fillable = [
        'empresa_id', 'tipo_servicio', 'titulo', 'descripcion', 'requerimientos',
        'nivel_jerarquico', 'salario_min', 'salario_max', 'ubicacion', 'tipo_contrato',
        'estado', 'fecha_publicacion'
    ];

    public static function tiposServicio(): array
    {
        return [
            'reclutamiento' => 'Reclutamiento de personal',
            'capacitacion'  => 'Capacitación',
            'coaching'      => 'Coaching ejecutivo',
            'evaluacion'    => 'Evaluación / Assessment',
            'outplacement'  => 'Outplacement',
            'nomina'        => 'Nómina y IMSS',
            'consultoria'   => 'Consultoría RH',
            'otro'          => 'Otro',
        ];
    }

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'salario_min' => 'decimal:2',
        'salario_max' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }
}
