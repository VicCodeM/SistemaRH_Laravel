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
        'fecha_postulacion'
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    public function vacante()
    {
        return $this->belongsTo(Vacante::class);
    }
}
