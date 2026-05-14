<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\PostulacionService;

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
}
