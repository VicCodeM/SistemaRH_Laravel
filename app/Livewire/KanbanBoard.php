<?php

namespace App\Livewire;

use App\Models\Postulacion;
use App\Services\PostulacionService;
use Livewire\Component;

class KanbanBoard extends Component
{
    public $vacanteId = '';
    private PostulacionService $postulacionService;

    public function boot(PostulacionService $postulacionService)
    {
        $this->postulacionService = $postulacionService;
    }

    public function render()
    {
        $vacantes = $this->postulacionService->getVacantes();
        $data = $this->postulacionService->getPostulacionesPorVacante($this->vacanteId ? (int) $this->vacanteId : null);

        return view('livewire.kanban-board', array_merge($data, [
            'vacantes' => $vacantes,
        ]));
    }

    /**
     * Cambia el estado de una postulación desde el tablero Kanban.
     * Solo admin o interno pueden mover tarjetas.
     */
    public function moverEstado(int $postulacionId, string $nuevoEstado): void
    {
        $user = auth()->user();

        if (! $user || (! $user->esAdmin() && ! $user->esInterno())) {
            return;
        }

        $postulacion = Postulacion::find($postulacionId);
        if (! $postulacion) return;
        $this->postulacionService->mover($postulacion, $nuevoEstado);
    }
}
