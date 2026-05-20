<?php

namespace App\Services;

use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Gestiona el ciclo de vida de un ServicioAsignado (pedido de servicio).
 *
 * Estados: pendiente → activo → en_proceso → completado / cancelado
 *
 * Toda mutación de estado pasa por aquí. Nunca actualices `estado` ni
 * `User.carga_trabajo_horas` desde un controlador o componente directamente.
 *
 * NOTA: NO confundir con la "solicitud" del candidato (su perfil/CV).
 */
class ServicioAsignadoService
{
    /**
     * Registra un nuevo pedido de servicio en estado 'pendiente'.
     *
     * @param  array<string,mixed>  $datos        servicio_id, notas, vacante_id, ...
     * @param  Model                $solicitante  Empresa | Candidato | User
     */
    public function registrar(array $datos, Model $solicitante): ServicioAsignado
    {
        return ServicioAsignado::create([
            'servicio_id'      => $datos['servicio_id'],
            'nivel_jerarquico' => $datos['nivel_jerarquico'] ?? null,
            'horas_estimadas'  => (int) ($datos['horas_estimadas'] ?? 0),
            'asignable_type'   => get_class($solicitante),
            'asignable_id'     => $solicitante->id,
            'notas'            => $datos['notas'] ?? null,
            'estado'           => 'pendiente',
            'solicitado_por'   => auth()->id(),
            'vacante_id'       => $datos['vacante_id'] ?? null,
        ]);
    }

    /**
     * Asigna un interno a la solicitud. Valida especialidad y suma horas.
     *
     * @param  bool         $forzar    Permite asignar aunque no tenga la especialidad (con motivo).
     * @param  string|null  $motivo    Motivo de la excepción (obligatorio si $forzar=true).
     *
     * @throws \DomainException si el interno no es válido o falta motivo al forzar.
     */
    public function asignarInterno(ServicioAsignado $solicitud, User $interno, bool $forzar = false, ?string $motivo = null): void
    {
        if (! $interno->esInterno() || ! $interno->estaActivo()) {
            throw new \DomainException('El usuario debe ser interno y estar activo.');
        }

        $tieneEspecialidad = ! $solicitud->servicio_id
            || $interno->tieneEspecialidadEn($solicitud->servicio_id);

        if (! $tieneEspecialidad && ! $forzar) {
            throw new \DomainException('El interno no tiene la especialidad requerida. Usa "forzar" con un motivo.');
        }

        if ($forzar && (! $motivo || trim($motivo) === '')) {
            throw new \DomainException('Debes indicar el motivo de la asignación con excepción.');
        }

        $horas = (int) ($solicitud->horas_estimadas ?? 0);

        if ($horas > 0 && ! $interno->tieneCapacidad($horas)) {
            throw new \DomainException(
                "{$interno->name} no tiene horas disponibles ({$interno->carga_trabajo_horas}/{$interno->capacidad_maxima_horas}). Libera otros pedidos o aumenta su capacidad."
            );
        }

        DB::transaction(function () use ($solicitud, $interno, $forzar, $motivo, $horas) {
            $notas = $solicitud->notas;
            if ($forzar) {
                $marca = '[Excepción ' . now()->format('d/m/Y H:i') . '] ' . $motivo;
                $notas = trim(($notas ? $notas . "\n\n" : '') . $marca);
            }

            $solicitud->update([
                'asignado_a'   => $interno->id,
                'asignado_por' => auth()->id(),
                'estado'       => 'activo',
                'notas'        => $notas,
            ]);

            // Sumar horas a la carga del interno
            if ($horas > 0) {
                $interno->increment('carga_trabajo_horas', $horas);
            }
        });

        // Notificar al interno (silencioso: si falla email no rompe la asignación)
        try {
            $interno->notify(new \App\Notifications\ServicioAsignadoAlInterno($solicitud->fresh()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo notificar al interno: {$e->getMessage()}");
        }
    }

    /**
     * Cambia el estado y aplica efectos colaterales (timestamps, liberar horas).
     */
    public function cambiarEstado(ServicioAsignado $solicitud, string $nuevoEstado): void
    {
        $this->validarEstado($nuevoEstado);

        $cambios = ['estado' => $nuevoEstado];

        if ($nuevoEstado === 'en_proceso') {
            $cambios['fecha_inicio'] = $solicitud->fecha_inicio ?? now();
        }

        if (in_array($nuevoEstado, ['completado', 'cancelado'], true)) {
            $cambios['fecha_fin'] = now();
        }

        if ($nuevoEstado === 'pendiente') {
            $cambios['fecha_inicio'] = null;
            $cambios['fecha_fin']    = null;
        }

        DB::transaction(function () use ($solicitud, $cambios, $nuevoEstado) {
            $solicitud->update($cambios);

            if (in_array($nuevoEstado, ['completado', 'cancelado'], true)) {
                $this->liberarHoras($solicitud);
            }
        });

        // Notificar al solicitante cuando se completa
        if ($nuevoEstado === 'completado') {
            $this->notificarCompletado($solicitud->fresh());
        }
    }

    /**
     * Envía notificación al solicitante (User detrás de la Empresa/Candidato) cuando el pedido se completa.
     */
    private function notificarCompletado(ServicioAsignado $solicitud): void
    {
        $destinatario = match (true) {
            $solicitud->asignable_type === \App\Models\Empresa::class
                => $solicitud->asignable?->usuario,
            $solicitud->asignable_type === \App\Models\Candidato::class
                => $solicitud->asignable?->usuario,
            default => null,
        };

        if (! $destinatario) {
            return;
        }

        try {
            $destinatario->notify(new \App\Notifications\ServicioCompletado($solicitud));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo notificar solicitante: {$e->getMessage()}");
        }
    }

    public function completar(ServicioAsignado $solicitud, string $resumen): void
    {
        $solicitud->update(['cierre_resumen' => $resumen]);
        $this->cambiarEstado($solicitud, 'completado');
    }

    public function cancelar(ServicioAsignado $solicitud, string $motivo): void
    {
        $solicitud->update(['cierre_resumen' => $motivo]);
        $this->cambiarEstado($solicitud, 'cancelado');
    }

    /**
     * Libera al interno asignado y devuelve el pedido a 'pendiente'.
     * Útil para reasignar a otro interno sin perder el pedido.
     */
    public function liberarInterno(ServicioAsignado $solicitud, ?string $motivo = null): void
    {
        if (! $solicitud->asignado_a) {
            throw new \DomainException('Este pedido no tiene interno asignado.');
        }

        DB::transaction(function () use ($solicitud, $motivo) {
            $this->liberarHoras($solicitud);

            $marca = '[Interno liberado ' . now()->format('d/m/Y H:i') . ']';
            if ($motivo) {
                $marca .= ' ' . $motivo;
            }

            $notas = trim(($solicitud->notas ? $solicitud->notas . "\n\n" : '') . $marca);

            $solicitud->update([
                'asignado_a'   => null,
                'estado'       => 'pendiente',
                'fecha_inicio' => null,
                'notas'        => $notas,
            ]);
        });
    }

    /**
     * Resta las horas estimadas del pedido a la carga del interno asignado.
     * Se llama al completar, cancelar o liberar. Nunca permite carga negativa.
     */
    private function liberarHoras(ServicioAsignado $solicitud): void
    {
        $interno = $solicitud->asignadoA;
        $horas   = (int) ($solicitud->horas_estimadas ?? 0);

        if (! $interno || $horas <= 0) {
            return;
        }

        $nuevaCarga = max(0, (int) $interno->carga_trabajo_horas - $horas);
        $interno->update(['carga_trabajo_horas' => $nuevaCarga]);
    }

    /**
     * Estados válidos del ciclo de vida. Invariante de negocio, no editable.
     * El catálogo de opciones solo sirve para labels personalizables.
     */
    public const ESTADOS = ['pendiente', 'activo', 'en_proceso', 'completado', 'cancelado'];

    private function validarEstado(string $estado): void
    {
        if (! in_array($estado, self::ESTADOS, true)) {
            throw new \InvalidArgumentException("Estado inválido: {$estado}");
        }
    }
}
