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

        $postulaciones = $query->latest('updated_at')->get();
        $estados = Postulacion::estadosProceso();

        return [
            'estados' => $estados,
            'postulacionesPorEstado' => collect($estados)
                ->mapWithKeys(fn ($label, $estado) => [$estado => $postulaciones->where('estado', $estado)->values()])
                ->all(),
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
        // competir por los cupos. El límite aplica solo al pasar a un estado que ocupa vacante.

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
                'estado' => Postulacion::estadoInicial(),
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

        // Validar cupos solo al entrar a un estado que ocupa vacante.
        if (Postulacion::estadoOcupaCupo($nuevoEstado) && ! Postulacion::estadoOcupaCupo($postulacion->estado)) {
            $vacante = $postulacion->vacante()->first();
            if ($vacante && $vacante->estaLlena()) {
                throw new \DomainException(
                    "No puedes avanzar: la vacante ya tiene {$vacante->cuposCubiertos()} de {$vacante->cupos} cupo(s) cubierto(s). Retira a alguien o aumenta los cupos."
                );
            }
        }

        $estadoAnterior = $postulacion->estado;
        $postulacion->update(['estado' => $nuevoEstado]);

        $this->sincronizarEstadoVacante($postulacion);

        // Notificar al candidato si su postulación cambió a un estado final relevante
        if ($estadoAnterior !== $nuevoEstado && (Postulacion::estadoOcupaCupo($nuevoEstado) || $nuevoEstado === 'rechazado')) {
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
            // Se liberó un cupo al mover fuera de un estado que ocupa vacante.
            $vacante->update(['estado' => 'activa']);
        }
    }

    /**
     * Mensaje descriptivo para un cambio de estado.
     */
    public function mensajeParaEstado(string $estado): string
    {
        return 'Estado actualizado a ' . Postulacion::estadoLabel($estado) . '.';
    }

    public function getVacantes(): Collection
    {
        return Vacante::all();
    }
}
