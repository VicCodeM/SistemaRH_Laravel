<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\ServicioAsignado;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->serviciosAsignados()
            ->with(['servicio', 'asignable', 'asignadoPor'])
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $tareas = $query->paginate(15)->withQueryString();

        $stats = [
            'activas'      => $user->serviciosAsignados()->where('estado', 'activo')->count(),
            'en_proceso'   => $user->serviciosAsignados()->where('estado', 'en_proceso')->count(),
            'completadas'  => $user->serviciosAsignados()->where('estado', 'completado')->count(),
            'canceladas'   => $user->serviciosAsignados()->where('estado', 'cancelado')->count(),
        ];

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

    public function tomar(ServicioAsignado $tarea)
    {
        $this->autorizarTarea($tarea);
        abort_if($tarea->estado !== 'activo', 422, 'La tarea ya fue tomada o cerrada.');

        $tarea->update([
            'estado' => 'en_proceso',
            'fecha_inicio' => $tarea->fecha_inicio ?? now(),
        ]);

        return back()->with('success', 'Tarea tomada.');
    }

    public function completar(Request $request, ServicioAsignado $tarea)
    {
        $this->autorizarTarea($tarea);
        abort_if(in_array($tarea->estado, ['completado', 'cancelado'], true), 422, 'La tarea ya fue cerrada.');

        $data = $request->validate([
            'cierre_resumen' => ['required', 'string', 'max:2000'],
        ]);

        $tarea->update([
            'estado' => 'completado',
            'fecha_inicio' => $tarea->fecha_inicio ?? now(),
            'fecha_fin' => now(),
            'cierre_resumen' => $data['cierre_resumen'],
        ]);

        return redirect()->route('interno.tareas.index')->with('success', 'Tarea completada.');
    }

    public function cancelar(Request $request, ServicioAsignado $tarea)
    {
        $this->autorizarTarea($tarea);
        abort_if(in_array($tarea->estado, ['completado', 'cancelado'], true), 422, 'La tarea ya fue cerrada.');

        $data = $request->validate([
            'cierre_resumen' => ['required', 'string', 'max:2000'],
        ]);

        $tarea->update([
            'estado' => 'cancelado',
            'fecha_inicio' => $tarea->fecha_inicio ?? now(),
            'fecha_fin' => now(),
            'cierre_resumen' => $data['cierre_resumen'],
        ]);

        return back()->with('success', 'Tarea cancelada.');
    }

    private function autorizarTarea(ServicioAsignado $tarea): void
    {
        $user = auth()->user();
        abort_unless($tarea->asignado_a === $user->id, 403);
    }
}
