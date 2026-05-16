<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\ServicioAsignado;
use App\Models\Ticket;
use App\Models\Vacante;
use Illuminate\Support\Collection;

/**
 * Servicio para agregar datos de reportes y gráficas del administrador.
 */
class ReporteService
{
    /**
     * Resumen numérico de todas las entidades principales.
     */
    public function resumen(): array
    {
        return [
            'empresas_total' => Empresa::count(),
            'empresas_activas' => Empresa::where('estado', 'activa')->count(),
            'empresas_pendientes' => Empresa::where('estado', 'pendiente')->count(),
            'candidatos_total' => \App\Models\Candidato::count(),
            'candidatos_aprobados' => \App\Models\Candidato::where('solicitud_estado', 'aprobada')->count(),
            'candidatos_pendientes' => \App\Models\Candidato::where('solicitud_estado', 'enviada')->count(),
            'solicitudes_total' => Vacante::count(),
            'solicitudes_activas' => Vacante::where('estado', 'activa')->count(),
            'solicitudes_pendientes' => Vacante::where('estado', 'pendiente')->count(),
            'tareas_total' => ServicioAsignado::count(),
            'tareas_activas' => ServicioAsignado::whereIn('estado', ['activo', 'en_proceso'])->count(),
            'tickets_total' => Ticket::count(),
            'tickets_abiertos' => Ticket::where('estado', 'abierto')->count(),
            'tickets_vencidos' => Ticket::whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->whereNotIn('estado', ['resuelto', 'cerrado'])
                ->count(),
        ];
    }

    /**
     * Empresas con más vacantes publicadas.
     */
    public function empresasTop(int $limite = 8): Collection
    {
        return Empresa::withCount('vacantes')
            ->orderByDesc('vacantes_count')
            ->orderBy('nombre_empresa')
            ->limit($limite)
            ->get();
    }

    /**
     * Solicitudes activas más recientes.
     */
    public function solicitudesActivas(int $limite = 8): Collection
    {
        return Vacante::with('empresa')
            ->where('estado', 'activa')
            ->latest('fecha_publicacion')
            ->limit($limite)
            ->get();
    }

    /**
     * Tickets más recientes.
     */
    public function ticketsRecientes(int $limite = 8): Collection
    {
        return Ticket::with(['empresa', 'asignado'])
            ->latest()
            ->limit($limite)
            ->get();
    }

    /**
     * Tareas más recientes.
     */
    public function tareasRecientes(int $limite = 8): Collection
    {
        return ServicioAsignado::with(['servicio', 'asignadoA'])
            ->latest()
            ->limit($limite)
            ->get();
    }

    /**
     * Genera datos de gráfica mensual para un modelo dado.
     *
     * @param class-string $modelo Clase del modelo Eloquent (ej: Vacante::class)
     * @param int $meses Cantidad de meses hacia atrás
     * @return array<int, array{label: string, valor: int}>
     */
    public function graficaMensual(string $modelo, int $meses = 6): array
    {
        $rango = collect(range($meses - 1, 0))->map(fn ($i) => now()->subMonths($i));

        return $rango->map(function ($mes) use ($modelo) {
            return [
                'label' => $mes->format('M Y'),
                'valor' => $modelo::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)
                    ->count(),
            ];
        })->all();
    }
}
