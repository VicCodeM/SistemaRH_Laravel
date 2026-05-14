<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidato extends Model
{
    protected $fillable = [
        'usuario_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'edad', 'sexo',
        'fecha_nacimiento', 'lugar_nacimiento', 'nacionalidad', 'telefono', 'celular',
        'domicilio', 'colonia', 'codigo_postal', 'municipio', 'ciudad', 'peso', 'estatura',
        'vive_con', 'estado_civil', 'dependientes', 'curp', 'nore_seguro_social', 'rfc',
        'afore', 'cartilla_militar', 'pasaporte', 'experiencia_anios', 'puesto_deseado',
        'habilidades', 'escolaridad', 'sueldo_deseado', 'sueldo_aprobado', 'fecha_contratacion',
        'cv_path', 'solicitud_estado', 'solicitud_enviada_at', 'solicitud_revisada_at',
        'solicitud_revision_admin_id',
        'licencia_conducir', 'redes_sociales', 'estado_salud', 'datos_personales',
        'datos_familiares', 'escolaridad_detallada', 'conocimientos_generales',
        'historial_laboral', 'referencias_personales', 'datos_generales', 'datos_economicos'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_contratacion' => 'date',
        'solicitud_enviada_at' => 'datetime',
        'solicitud_revisada_at' => 'datetime',
        'licencia_conducir' => 'array',
        'redes_sociales' => 'array',
        'estado_salud' => 'array',
        'datos_personales' => 'array',
        'datos_familiares' => 'array',
        'escolaridad_detallada' => 'array',
        'conocimientos_generales' => 'array',
        'historial_laboral' => 'array',
        'referencias_personales' => 'array',
        'datos_generales' => 'array',
        'datos_economicos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }
}
