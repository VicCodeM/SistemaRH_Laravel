<?php

namespace App\Services;

use App\Models\CatalogoServicio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Gestión del personal interno: alta, capacidades, ocupación, disponibilidad.
 */
class PersonalInternoService
{
    /**
     * Crea un nuevo interno con sus capacidades y envía enlace de acceso.
     *
     * @param  array<string,mixed>  $datos        name, email, capacidad_maxima_horas, departamento, disponibilidad
     * @param  array<int>           $serviciosIds Especialidades (catalogo_servicios.id)
     */
    public function crear(array $datos, array $serviciosIds = []): User
    {
        $requiereVerificacion = User::requireEmailVerification();

        return DB::transaction(function () use ($datos, $serviciosIds, $requiereVerificacion) {
            $interno = User::create([
                'name'                   => $datos['name'],
                'email'                  => $datos['email'],
                'rol'                    => 'interno',
                'estado'                 => 'activo',
                'password'               => Hash::make(Str::random(20)),
                'email_verified_at'      => User::emailVerifiedAtInitial(),
                'capacidad_maxima_horas' => $datos['capacidad_maxima_horas'] ?? 40,
                'carga_trabajo_horas'    => 0,
                'departamento'           => $datos['departamento'] ?? null,
                'disponibilidad'         => $datos['disponibilidad'] ?? 'disponible',
            ]);

            if ($serviciosIds) {
                $this->actualizarCapacidades($interno, $serviciosIds);
            }

            Password::sendResetLink(['email' => $interno->email]);

            if ($requiereVerificacion) {
                try {
                    $interno->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return $interno;
        });
    }

    /**
     * Actualiza el listado de servicios que el interno sabe brindar.
     *
     * @param  array<int>  $serviciosIds
     */
    public function actualizarCapacidades(User $interno, array $serviciosIds): void
    {
        $interno->serviciosCapacitados()->sync($serviciosIds);
    }

    /**
     * Resumen de ocupación del interno.
     *
     * @return array{carga:int, capacidad:int, porcentaje:float, libres:int}
     */
    public function ocupacion(User $interno): array
    {
        $libres = max(0, $interno->capacidad_maxima_horas - $interno->carga_trabajo_horas);

        return [
            'carga'      => (int) $interno->carga_trabajo_horas,
            'capacidad'  => (int) $interno->capacidad_maxima_horas,
            'porcentaje' => (float) $interno->ocupacionPorcentaje(),
            'libres'     => $libres,
        ];
    }

    public function tieneCapacidadPara(User $interno, CatalogoServicio $servicio): bool
    {
        return $interno->estaActivo()
            && $interno->disponibilidad === 'disponible'
            && $interno->tieneEspecialidadEn($servicio->id);
    }
}
