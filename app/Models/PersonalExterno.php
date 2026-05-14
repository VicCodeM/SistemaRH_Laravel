<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalExterno extends Model
{
    protected $table = 'personal_externo';

    protected $fillable = [
        'nombre', 'apellidos', 'email', 'telefono',
        'especialidad', 'niveles_jerarquicos',
        'empresa_o_razon_social', 'descripcion',
        'disponibilidad', 'cv_path',
    ];

    protected $casts = [
        'niveles_jerarquicos' => 'array',
    ];

    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellidos}";
    }
}
