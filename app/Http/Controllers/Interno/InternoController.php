<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\ServicioAsignado;
use App\Models\Vacante;

class InternoController extends Controller
{
    public function dashboard(\App\Services\ResumenRapidoService $resumen)
    {
        $userId = auth()->id();

        // Métricas propias del interno
        $stats = [
            'tareas_por_tomar'  => ServicioAsignado::where('asignado_a', $userId)
                ->where('estado', 'activo')
                ->whereNull('fecha_inicio')
                ->count(),
            'tareas_en_proceso' => ServicioAsignado::where('asignado_a', $userId)
                ->where('estado', 'en_proceso')
                ->count(),
            'tareas_activas'    => ServicioAsignado::where('asignado_a', $userId)
                ->whereIn('estado', ['activo', 'en_proceso'])
                ->count(),
            'tareas_completadas'=> ServicioAsignado::where('asignado_a', $userId)
                ->where('estado', 'completado')
                ->count(),
            // Info general del sistema (solo lectura)
            'solicitudes_activas_sistema' => Vacante::where('estado', 'activa')->count(),
        ];

        $tareas_recientes = ServicioAsignado::with(['servicio', 'asignable', 'asignadoPor'])
            ->where('asignado_a', $userId)
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest()
            ->take(8)
            ->get();

        $acciones = $resumen->paraInterno(auth()->user());

        return view('interno.dashboard', compact(
            'stats',
            'tareas_recientes',
            'acciones'
        ));
    }
}
