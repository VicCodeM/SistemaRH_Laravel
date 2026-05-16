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

        if ($candidatoId) {
            $candidato = Candidato::findOrFail($candidatoId);
            $candidato->fill($datos)->save();
            return $candidato;
        }

        return Candidato::create($datos);
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
