<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
