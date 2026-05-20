<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\Vacante;

class InternoController extends Controller
{
    public function dashboard(\App\Services\ResumenRapidoService $resumen)
    {
        $stats = [
            'empresas_pendientes'   => Empresa::where('estado', 'pendiente')->count(),
            'candidatos_pendientes' => Candidato::where('solicitud_estado', 'enviada')->count(),
            'solicitudes_pendientes'=> Vacante::where('estado', 'pendiente')->count(),
            'solicitudes_activas'   => Vacante::where('estado', 'activa')->count(),
            'tareas_activas'        => ServicioAsignado::where('asignado_a', auth()->id())
                ->whereIn('estado', ['activo', 'en_proceso'])
                ->count(),
            'tareas_completadas'    => ServicioAsignado::where('asignado_a', auth()->id())
                ->where('estado', 'completado')
                ->count(),
        ];

        $empresas_pendientes = Empresa::with('usuario')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get();

        $candidatos_pendientes = Candidato::with('usuario')
            ->where('solicitud_estado', 'enviada')
            ->latest('solicitud_enviada_at')
            ->take(5)
            ->get();

        $solicitudes_recientes = Vacante::with('empresa')
            ->whereIn('estado', ['pendiente', 'activa'])
            ->latest()
            ->take(5)
            ->get();

        $tareas_recientes = ServicioAsignado::with(['servicio', 'asignable', 'asignadoPor'])
            ->where('asignado_a', auth()->id())
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest()
            ->take(5)
            ->get();

        $acciones = $resumen->paraInterno(auth()->user());

        return view('interno.dashboard', compact(
            'stats',
            'empresas_pendientes',
            'candidatos_pendientes',
            'solicitudes_recientes',
            'tareas_recientes',
            'acciones'
        ));
    }
}
