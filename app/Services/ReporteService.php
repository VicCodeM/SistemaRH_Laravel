<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\ServicioAsignado;
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
     * KPIs principales para la página de reportes.
     * Acepta filtros de fecha desde/hasta.
     */
    public function kpis(?string $desde = null, ?string $hasta = null): array
    {
        $desdeCarbon = $desde ? \Carbon\Carbon::parse($desde)->startOfDay() : now()->startOfMonth();
        $hastaCarbon = $hasta ? \Carbon\Carbon::parse($hasta)->endOfDay() : now()->endOfDay();

        $vacantesCerradas = Vacante::where('estado', 'cerrada')
            ->whereBetween('updated_at', [$desdeCarbon, $hastaCarbon])
            ->count();

        $serviciosCompletados = ServicioAsignado::where('estado', 'completado')
            ->whereBetween('fecha_fin', [$desdeCarbon, $hastaCarbon])
            ->count();

        $vacantesNuevas = Vacante::whereBetween('created_at', [$desdeCarbon, $hastaCarbon])->count();
        $serviciosNuevos = ServicioAsignado::whereBetween('created_at', [$desdeCarbon, $hastaCarbon])->count();
        $candidatosNuevos = \App\Models\Candidato::whereBetween('created_at', [$desdeCarbon, $hastaCarbon])->count();
        $empresasNuevas = Empresa::whereBetween('created_at', [$desdeCarbon, $hastaCarbon])->count();

        // Tiempo promedio para cerrar vacante (en días)
        $tiempoPromedio = Vacante::where('estado', 'cerrada')
            ->whereBetween('updated_at', [$desdeCarbon, $hastaCarbon])
            ->whereNotNull('fecha_publicacion')
            ->get()
            ->avg(fn ($v) => $v->fecha_publicacion->diffInDays($v->updated_at));

        return [
            'desde'                 => $desdeCarbon->format('d/m/Y'),
            'hasta'                 => $hastaCarbon->format('d/m/Y'),
            'vacantes_cerradas'     => $vacantesCerradas,
            'vacantes_nuevas'       => $vacantesNuevas,
            'servicios_completados' => $serviciosCompletados,
            'servicios_nuevos'      => $serviciosNuevos,
            'candidatos_nuevos'     => $candidatosNuevos,
            'empresas_nuevas'       => $empresasNuevas,
            'dias_promedio_cierre'  => $tiempoPromedio ? round($tiempoPromedio, 1) : null,
        ];
    }

    /**
     * Top internos por servicios completados en el rango.
     */
    public function topInternos(?string $desde = null, ?string $hasta = null, int $limite = 5): Collection
    {
        $desdeCarbon = $desde ? \Carbon\Carbon::parse($desde)->startOfDay() : now()->startOfMonth();
        $hastaCarbon = $hasta ? \Carbon\Carbon::parse($hasta)->endOfDay() : now()->endOfDay();

        return \App\Models\User::where('rol', 'interno')
            ->withCount([
                'serviciosAsignados as completados' => fn ($q) =>
                    $q->where('estado', 'completado')->whereBetween('fecha_fin', [$desdeCarbon, $hastaCarbon]),
                'serviciosAsignados as activos' => fn ($q) =>
                    $q->whereIn('estado', ['activo', 'en_proceso']),
            ])
            ->having('completados', '>', 0)
            ->orderByDesc('completados')
            ->limit($limite)
            ->get();
    }

    /**
     * Top empresas por número de vacantes/servicios en el rango.
     */
    public function topEmpresas(?string $desde = null, ?string $hasta = null, int $limite = 5): Collection
    {
        $desdeCarbon = $desde ? \Carbon\Carbon::parse($desde)->startOfDay() : now()->startOfMonth();
        $hastaCarbon = $hasta ? \Carbon\Carbon::parse($hasta)->endOfDay() : now()->endOfDay();

        return Empresa::withCount([
                'vacantes as vacantes_periodo' => fn ($q) =>
                    $q->whereBetween('created_at', [$desdeCarbon, $hastaCarbon]),
            ])
            ->having('vacantes_periodo', '>', 0)
            ->orderByDesc('vacantes_periodo')
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
    public function graficaMensual(string $modelo, int $meses = 6): \Illuminate\Support\Collection
    {
        $rango = collect(range($meses - 1, 0))->map(fn ($i) => now()->subMonths($i));

        return $rango->map(function ($mes) use ($modelo) {
            return [
                'label' => $mes->format('M Y'),
                'valor' => $modelo::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)
                    ->count(),
            ];
        });
    }
}
