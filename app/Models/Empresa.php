<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = [
        'usuario_id', 'nombre_empresa', 'rfc', 'telefono', 'direccion',
        'descripcion', 'estado', 'razon_social', 'nombre_rh',
        'telefono_directo', 'ciudad', 'municipio', 'codigo_postal', 'pagina_web'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public static function estados(): array
    {
        return CatalogoOpcion::opciones('empresa_estados', [
            'pendiente' => 'Pendiente',
            'activa' => 'Activa',
            'rechazada' => 'Rechazada',
            'suspendida' => 'Suspendida',
        ]);
    }

    public static function estadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('empresa_estados', $estado);
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'badge-yellow',
            'activa' => 'badge-green',
            'rechazada' => 'badge-red',
            'suspendida' => 'badge-gray',
            default => 'badge-gray',
        };
    }
}
