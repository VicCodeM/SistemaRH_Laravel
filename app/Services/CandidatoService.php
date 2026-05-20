<?php

namespace App\Services;

use App\Models\Candidato;

class CandidatoService
{
    public function __construct(
        private WorkflowService $workflow
    ) {}

    public function guardarBorrador(int $usuarioId, ?int $candidatoId, array $datos): Candidato
    {
        $datos['usuario_id'] = $usuarioId;
        $datos = $this->normalizarVacios($datos);

        if ($candidatoId) {
            $candidato = Candidato::findOrFail($candidatoId);
            $candidato->fill($datos)->save();
            return $candidato;
        }

        return Candidato::create($datos);
    }

    /**
     * Convierte strings vacíos a null para evitar errores de truncamiento
     * en columnas ENUM, DATE y otros tipos que no admiten '' en MySQL.
     */
    private function normalizarVacios(array $datos): array
    {
        return array_map(function ($valor) {
            if (is_string($valor) && $valor === '') {
                return null;
            }

            return $valor;
        }, $datos);
    }

    public function enviarSolicitud(int $candidatoId): Candidato
    {
        $candidato = Candidato::findOrFail($candidatoId);
        $candidato->update([
            'solicitud_estado' => 'enviada',
            'solicitud_enviada_at' => now(),
        ]);

        $this->workflow->decideCandidatoRegistration($candidato);

        return $candidato;
    }

    public function obtenerCandidatoPorUsuario(int $usuarioId): ?Candidato
    {
        return Candidato::where('usuario_id', $usuarioId)->first();
    }
}
