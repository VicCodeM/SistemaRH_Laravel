<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\ServicioAsignado;
use App\Services\ServicioAsignadoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel del Interno: ve y gestiona sus solicitudes de servicio asignadas.
 *
 * Internamente trabaja sobre ServicioAsignado; en la UI se le presenta
 * al usuario como "Mis tareas asignadas".
 */
class TareaController extends Controller
{
    public function index(Request $request)
    {
        $interno = auth()->user();

        $query = $interno->serviciosAsignados()
            ->with(['servicio', 'asignable', 'asignadoPor'])
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $tareas = $query->paginate(15)->withQueryString();
        $stats = $this->estadisticasDel($interno);

        return view('interno.tareas.index', compact('tareas', 'stats'));
    }

    public function show(ServicioAsignado $tarea)
    {
        $this->autorizarTarea($tarea);
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);

        return view('interno.tareas.show', compact('tarea'));
    }

    public function tomarModal(ServicioAsignado $tarea): View
    {
        $this->autorizarTarea($tarea);
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);
        return view('interno.tareas.modal-tomar', compact('tarea'));
    }

    public function completarModal(ServicioAsignado $tarea): View
    {
        $this->autorizarTarea($tarea);
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);
        return view('interno.tareas.modal-completar', compact('tarea'));
    }

    public function cancelarModal(ServicioAsignado $tarea): View
    {
        $this->autorizarTarea($tarea);
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);
        return view('interno.tareas.modal-cancelar', compact('tarea'));
    }

    public function tomar(ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $this->autorizarTarea($tarea);
        abort_if($tarea->estado !== 'activo', 422, 'La tarea ya fue tomada o cerrada.');

        $servicio->cambiarEstado($tarea, 'en_proceso');

        return back()->with('success', 'Tarea tomada.');
    }

    public function completar(Request $request, ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $this->autorizarTarea($tarea);
        $this->abortarSiCerrada($tarea);

        $data = $request->validate([
            'cierre_resumen' => ['required', 'string', 'max:2000'],
        ]);

        $servicio->completar($tarea, $data['cierre_resumen']);

        return redirect()->route('interno.tareas.index')->with('success', 'Tarea completada.');
    }

    public function cancelar(Request $request, ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $this->autorizarTarea($tarea);
        $this->abortarSiCerrada($tarea);

        $data = $request->validate([
            'cierre_resumen' => ['required', 'string', 'max:2000'],
        ]);

        $servicio->cancelar($tarea, $data['cierre_resumen']);

        return back()->with('success', 'Tarea cancelada.');
    }

    private function autorizarTarea(ServicioAsignado $tarea): void
    {
        abort_unless($tarea->asignado_a === auth()->id(), 403);
    }

    private function abortarSiCerrada(ServicioAsignado $tarea): void
    {
        abort_if(in_array($tarea->estado, ['completado', 'cancelado'], true), 422, 'La tarea ya fue cerrada.');
    }

    private function estadisticasDel($interno): array
    {
        return [
            'activas'     => $interno->serviciosAsignados()->where('estado', 'activo')->count(),
            'en_proceso'  => $interno->serviciosAsignados()->where('estado', 'en_proceso')->count(),
            'completadas' => $interno->serviciosAsignados()->where('estado', 'completado')->count(),
            'canceladas'  => $interno->serviciosAsignados()->where('estado', 'cancelado')->count(),
        ];
    }
}
