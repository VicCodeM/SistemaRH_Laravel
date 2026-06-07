<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\BienvenidaCuentaVerificada;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'email', 'password', 'google_sub', 'avatar_url', 'sexo', 'rol', 'estado', 'email_verified_at', 'carga_trabajo_horas', 'capacidad_maxima_horas', 'nivel_jerarquico', 'departamento', 'disponibilidad', 'disponible_desde'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function serviciosCapacitados(): BelongsToMany
    {
        return $this->belongsToMany(CatalogoServicio::class, 'interno_servicio', 'user_id', 'servicio_id');
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendWelcomeVerifiedNotification(): void
    {
        $this->notify(new BienvenidaCuentaVerificada);
    }

    public static function requireEmailVerification(): bool
    {
        return config('auth.require_email_verification', false);
    }

    public static function emailVerifiedAtInitial(): ?Carbon
    {
        return self::requireEmailVerification() ? null : now();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Registra actividad del usuario (máx. 1 escritura cada 15s).
     */
    public function tocarPresencia(): void
    {
        if (! $this->last_seen_at || $this->last_seen_at->lt(now()->subSeconds(15))) {
            $this->forceFill(['last_seen_at' => now()])->saveQuietly();
        }
    }

    /**
     * En línea si dio señales de vida en los últimos 35 segundos.
     */
    public function estaEnLinea(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subSeconds(35));
    }

    /**
     * Texto amigable de la última conexión ("hace 5 minutos").
     */
    public function ultimaVezTexto(): ?string
    {
        return $this->last_seen_at?->locale('es')->diffForHumans();
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

    public function ocupacionPorcentaje(): float
    {
        if ($this->capacidad_maxima_horas <= 0) {
            return 0;
        }

        return min(100, round(($this->carga_trabajo_horas / $this->capacidad_maxima_horas) * 100, 2));
    }

    public function tieneCapacidad(int $horas = 0): bool
    {
        return ($this->carga_trabajo_horas + $horas) <= $this->capacidad_maxima_horas;
    }

    public function disponiblePara(ServicioAsignado $solicitud, int $horasRequeridas = 0): bool
    {
        if ($this->disponibilidad !== 'disponible' || ! $this->estaActivo()) {
            return false;
        }

        if (! $this->tieneCapacidad($horasRequeridas)) {
            return false;
        }

        if ($solicitud->servicio_id) {
            return $this->tieneEspecialidadEn($solicitud->servicio_id);
        }

        return true;
    }

    public function tieneEspecialidadEn(int $servicioId): bool
    {
        return $this->serviciosCapacitados()
            ->where('catalogo_servicios.id', $servicioId)
            ->exists();
    }

    public function solicitudesActivas(): int
    {
        return $this->serviciosAsignados()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->count();
    }
}
