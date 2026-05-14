<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Postulacion;
use Illuminate\Support\Facades\Auth;

class CandidatoService
{
    public function __construct(
        private WorkflowService $workflow
    ) {}

    public function guardarBorrador(int $usuarioId, ?int $candidatoId, array $datos): Candidato
    {
        $datos['usuario_id'] = $usuarioId;

        if ($candidatoId) {
            Candidato::where('id', $candidatoId)->update($datos);
            return Candidato::find($candidatoId);
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

        Postulacion::firstOrCreate([
            'candidato_id' => $candidato->id,
            'vacante_id' => 1,
            'estado' => 'postulado',
        ]);

        return $candidato;
    }

    public function obtenerCandidatoPorUsuario(int $usuarioId): ?Candidato
    {
        return Candidato::where('usuario_id', $usuarioId)->first();
    }
}
