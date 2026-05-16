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

    public static function disponibilidades(): array
    {
        return CatalogoOpcion::opciones('disponibilidad_externa', [
            'disponible' => 'Disponible',
            'ocupado' => 'Ocupado',
            'inactivo' => 'Inactivo',
        ]);
    }

    public static function disponibilidadLabel(?string $disponibilidad): string
    {
        return CatalogoOpcion::label('disponibilidad_externa', $disponibilidad);
    }

    public static function disponibilidadBadgeClass(?string $disponibilidad): string
    {
        return match ($disponibilidad) {
            'disponible' => 'badge-green',
            'ocupado' => 'badge-yellow',
            'inactivo' => 'badge-gray',
            default => 'badge-gray',
        };
    }
}
