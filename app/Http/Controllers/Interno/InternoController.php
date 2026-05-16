<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\Ticket;
use App\Models\Vacante;

class InternoController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'empresas_pendientes'   => Empresa::where('estado', 'pendiente')->count(),
            'candidatos_pendientes' => Candidato::where('solicitud_estado', 'enviada')->count(),
            'solicitudes_pendientes'=> Vacante::where('estado', 'pendiente')->count(),
            'solicitudes_activas'   => Vacante::where('estado', 'activa')->count(),
            'tickets_abiertos'      => Ticket::whereIn('estado', ['abierto', 'en_proceso'])->count(),
            'tickets_vencidos'      => Ticket::whereNotIn('estado', ['resuelto', 'cerrado'])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->count(),
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

        $tickets_recientes = Ticket::with('empresa.usuario')
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->orderByRaw("CASE estado WHEN 'abierto' THEN 1 WHEN 'en_proceso' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->orderByRaw('COALESCE(sla_due_at, created_at) ASC')
            ->take(5)
            ->get();

        $tareas_recientes = ServicioAsignado::with(['servicio', 'asignable', 'asignadoPor'])
            ->where('asignado_a', auth()->id())
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest()
            ->take(5)
            ->get();

        return view('interno.dashboard', compact(
            'stats',
            'empresas_pendientes',
            'candidatos_pendientes',
            'solicitudes_recientes',
            'tickets_recientes',
            'tareas_recientes'
        ));
    }
}
