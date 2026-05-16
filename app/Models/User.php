<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_sub', 'avatar_url', 'sexo', 'rol', 'estado'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function candidato(): HasOne
    {
        return $this->hasOne(Candidato::class, 'usuario_id');
    }

    public function empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'usuario_id');
    }

    public function serviciosAsignados(): HasMany
    {
        return $this->hasMany(ServicioAsignado::class, 'asignado_a');
    }

    public function serviciosCreados(): HasMany
    {
        return $this->hasMany(ServicioAsignado::class, 'asignado_por');
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esEmpresa(): bool
    {
        return $this->rol === 'empresa';
    }

    public function esCandidato(): bool
    {
        return $this->rol === 'candidato';
    }

    public function esInterno(): bool
    {
        return $this->rol === 'interno';
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public static function estados(): array
    {
        return [
            'activo' => 'Activo',
            'pendiente' => 'Pendiente',
            'bloqueado' => 'Bloqueado',
        ];
    }

    public static function estadoLabel(?string $estado): string
    {
        return self::estados()[$estado] ?? 'Sin definir';
    }

    public static function estadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'activo' => 'badge-green',
            'pendiente' => 'badge-yellow',
            'bloqueado' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public static function roles(): array
    {
        return CatalogoOpcion::opciones('roles', [
            'admin' => 'Administrador',
            'empresa' => 'Empresa',
            'candidato' => 'Candidato',
            'interno' => 'Interno',
        ]);
    }

    public static function rolLabel(?string $rol): string
    {
        return CatalogoOpcion::label('roles', $rol);
    }
}
