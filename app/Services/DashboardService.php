<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\PersonalExterno;
use App\Models\ServicioAsignado;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vacante;

/**
 * Servicio para armar los datos de los dashboards por rol.
 * Centraliza stats, listados recientes y alertas operativas.
 */
class DashboardService
{
    /**
     * Retorna todos los datos necesarios para el dashboard del administrador.
     *
     * @return array{stats: array, empresas_pendientes: \Illuminate\Database\Eloquent\Collection, candidatos_pendientes: \Illuminate\Database\Eloquent\Collection, solicitudes_recientes: \Illuminate\Database\Eloquent\Collection, tareas_recientes: \Illuminate\Database\Eloquent\Collection, alertas: array}
     */
    public function datosAdmin(): array
    {
        return [
            'stats' => $this->statsAdmin(),
            'empresas_pendientes' => $this->empresasPendientesRecientes(),
            'candidatos_pendientes' => $this->candidatosPendientesRecientes(),
            'solicitudes_recientes' => $this->solicitudesRecientes(),
            'tareas_recientes' => $this->tareasRecientes(),
            'alertas' => $this->alertasAdmin(),
        ];
    }

    /**
     * Conteos clave para el resumen del admin.
     */
    public function statsAdmin(): array
    {
        return [
            'empresas_pendientes'   => Empresa::where('estado', 'pendiente')->count(),
            'empresas_activas'      => Empresa::where('estado', 'activa')->count(),
            'candidatos_pendientes' => Candidato::where('solicitud_estado', 'enviada')->count(),
            'candidatos_aprobados'  => Candidato::where('solicitud_estado', 'aprobada')->count(),
            'solicitudes_activas'   => Vacante::where('estado', 'activa')->count(),
            'solicitudes_pendientes'=> Vacante::where('estado', 'pendiente')->count(),
            'personal_disponible'   => PersonalExterno::where('disponibilidad', 'disponible')->count(),
            'tareas_activas'        => ServicioAsignado::whereIn('estado', ['activo', 'en_proceso'])->count(),
            'internos_activos'      => User::where('rol', 'interno')->where('estado', 'activo')->count(),
        ];
    }

    /**
     * Empresas pendientes más recientes.
     */
    public function empresasPendientesRecientes(int $limite = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Empresa::where('estado', 'pendiente')
            ->with('usuario')
            ->latest()
            ->take($limite)
            ->get();
    }

    /**
     * Candidatos con solicitud enviada más recientes.
     */
    public function candidatosPendientesRecientes(int $limite = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Candidato::where('solicitud_estado', 'enviada')
            ->with('usuario')
            ->latest('solicitud_enviada_at')
            ->take($limite)
            ->get();
    }

    /**
     * Solicitudes (vacantes) pendientes o activas más recientes.
     */
    public function solicitudesRecientes(int $limite = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Vacante::with('empresa')
            ->whereIn('estado', ['pendiente', 'activa'])
            ->latest()
            ->take($limite)
            ->get();
    }

    /**
     * Tareas activas o en proceso ordenadas por prioridad de estado.
     */
    public function tareasRecientes(int $limite = 5): \Illuminate\Database\Eloquent\Collection
    {
        return ServicioAsignado::with(['servicio', 'asignable', 'asignadoA'])
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->take($limite)
            ->get();
    }

    /**
     * Alertas operativas para el dashboard admin.
     */
    public function alertasAdmin(): array
    {
        $alertas = [];

        $ticketsVencidos = Ticket::whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('estado', ['resuelto', 'cerrado'])
            ->count();

        if ($ticketsVencidos > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => "Hay {$ticketsVencidos} ticket(s) vencido(s) sin resolver. Revisa el módulo de soporte.",
                'link' => route('tickets.index'),
            ];
        }

        $empresasViejas = Empresa::where('estado', 'pendiente')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        if ($empresasViejas > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "{$empresasViejas} empresa(s) lleva(n) más de 7 días esperando aprobación de acceso.",
                'link' => route('admin.empresas', ['estado' => 'pendiente']),
            ];
        }

        $candidatosViejos = Candidato::where('solicitud_estado', 'enviada')
            ->where('solicitud_enviada_at', '<', now()->subDays(7))
            ->count();

        if ($candidatosViejos > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "{$candidatosViejos} candidato(s) lleva(n) más de 7 días esperando revisión.",
                'link' => route('admin.candidatos', ['estado' => 'enviada']),
            ];
        }

        return $alertas;
    }
}
