<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Support\Collection;

/**
 * Servicio para centralizar la lógica de postulaciones:
 * asignación de candidatos, cambio de estado y listados.
 */
class PostulacionService
{
    public function __construct(
        private SolicitudCompatibilidadService $compatibilidad
    ) {
    }

    /**
     * Lista postulaciones agrupadas por estado para una vacante.
     */
    public function getPostulacionesPorVacante(?int $vacanteId): array
    {
        $query = Postulacion::with('candidato', 'vacante');
        if ($vacanteId) {
            $query->where('vacante_id', $vacanteId);
        }

        return [
            'postulados' => (clone $query)->where('estado', 'postulado')->get(),
            'entrevista' => (clone $query)->where('estado', 'entrevista')->get(),
            'seleccionados' => (clone $query)->where('estado', 'seleccionado')->get(),
            'rechazados' => (clone $query)->where('estado', 'rechazado')->get(),
        ];
    }

    /**
     * Asigna un candidato a una vacante evaluando compatibilidad.
     * Soporta asignación forzada con excepción.
     *
     * @return array{exito: bool, mensaje: string, postulacion: Postulacion|null}
     */
    public function asignar(Vacante $vacante, int $candidatoId, bool $forzar, ?string $motivo): array
    {
        // Asignar = agregar al pipeline. Sin límite, varios candidatos pueden
        // competir por los cupos. El límite aplica solo al marcar como 'seleccionado'.

        $candidato = Candidato::where('id', $candidatoId)
            ->where('solicitud_estado', 'aprobada')
            ->firstOrFail();

        $evaluacion = $this->compatibilidad->evaluar($vacante, $candidato);

        if ($evaluacion['categoria'] === 'no_aptos' && ! $forzar) {
            return [
                'exito' => false,
                'mensaje' => 'Ese candidato no cumple los requisitos mínimos. Usa la asignación con excepción.',
                'postulacion' => null,
            ];
        }

        if ($evaluacion['categoria'] === 'no_aptos' && trim((string) ($motivo ?? '')) === '') {
            return [
                'exito' => false,
                'mensaje' => 'Indica un motivo para justificar la excepción.',
                'postulacion' => null,
            ];
        }

        $postulacion = Postulacion::updateOrCreate(
            [
                'vacante_id' => $vacante->id,
                'candidato_id' => $candidato->id,
            ],
            [
                'estado' => 'postulado',
                'fecha_postulacion' => now(),
                'asignacion_forzada' => $forzar && $evaluacion['categoria'] === 'no_aptos',
                'motivo_asignacion' => trim((string) ($motivo ?? '')) ?: $evaluacion['resumen'],
            ]
        );

        $esExcepcion = $forzar && $evaluacion['categoria'] === 'no_aptos';

        return [
            'exito' => true,
            'mensaje' => $esExcepcion
                ? 'Candidato asignado con excepción registrada.'
                : 'Candidato asignado a la solicitud.',
            'postulacion' => $postulacion,
        ];
    }

    /**
     * Cambia el estado de una postulación validando que sea un estado de proceso válido.
     */
    public function mover(Postulacion $postulacion, string $nuevoEstado): void
    {
        $estadosPermitidos = array_keys(Postulacion::estadosProceso());

        if (! in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new \InvalidArgumentException("Estado de postulación no válido: {$nuevoEstado}");
        }

        // Validar cupos SOLO al pasar a 'seleccionado' (no estaba antes seleccionado).
        // En revisión / Ya entrevistado no tienen límite: pueden haber varios compitiendo.
        if ($nuevoEstado === 'seleccionado' && $postulacion->estado !== 'seleccionado') {
            $vacante = $postulacion->vacante()->first();
            if ($vacante && $vacante->estaLlena()) {
                throw new \DomainException(
                    "No puedes seleccionar: la vacante ya tiene {$vacante->cuposCubiertos()} de {$vacante->cupos} cupo(s) cubierto(s). Retira a alguien para liberar un lugar."
                );
            }
        }

        $estadoAnterior = $postulacion->estado;
        $postulacion->update(['estado' => $nuevoEstado]);

        $this->sincronizarEstadoVacante($postulacion);

        // Notificar al candidato si su postulación cambió a un estado final relevante
        if ($estadoAnterior !== $nuevoEstado && in_array($nuevoEstado, ['seleccionado', 'rechazado'], true)) {
            $this->notificarCandidato($postulacion);
        }
    }

    private function notificarCandidato(Postulacion $postulacion): void
    {
        $usuario = $postulacion->candidato?->usuario;
        if (! $usuario) {
            return;
        }

        try {
            $usuario->notify(new \App\Notifications\PostulacionCambioEstado($postulacion));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo notificar candidato: {$e->getMessage()}");
        }
    }

    /**
     * Cierra automáticamente la vacante cuando se llena, o la reabre si se libera un cupo.
     */
    private function sincronizarEstadoVacante(Postulacion $postulacion): void
    {
        $vacante = $postulacion->vacante()->first();
        if (! $vacante || in_array($vacante->estado, ['rechazada'], true)) {
            return;
        }

        if ($vacante->estaLlena() && $vacante->estado !== 'cerrada') {
            $vacante->update(['estado' => 'cerrada']);
        } elseif (! $vacante->estaLlena() && $vacante->estado === 'cerrada') {
            // Se liberó un cupo (retiro/rechazo de seleccionado) → reabrir
            $vacante->update(['estado' => 'activa']);
        }
    }

    /**
     * Mensaje descriptivo para un cambio de estado.
     */
    public function mensajeParaEstado(string $estado): string
    {
        return match ($estado) {
            'entrevista'   => 'Candidato marcado como ya entrevistado.',
            'seleccionado' => 'Candidato seleccionado.',
            'rechazado'    => 'Candidato rechazado.',
            'retirado'     => 'Candidato retirado de la vacante.',
            default        => 'Estado actualizado.',
        };
    }

    public function getVacantes(): Collection
    {
        return Vacante::all();
    }
}
