<?php

namespace App\Services;

use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Support\Collection;

class PostulacionService
{
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

    public function cambiarEstado(int $postulacionId, string $estado): void
    {
        Postulacion::where('id', $postulacionId)->update(['estado' => $estado]);
    }

    public function getVacantes(): Collection
    {
        return Vacante::all();
    }
}
