<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\Vacante;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servicio para generar archivos de exportación (CSV, etc.).
 */
class ExportService
{
    /**
     * Genera un CSV con el resumen general del sistema.
     */
    public function reporteSistema(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_sistema_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['Reporte del Sistema RH - ' . now()->format('d/m/Y')], ';');
            fputcsv($output, [], ';');
            fputcsv($output, ['Indicador', 'Valor'], ';');
            fputcsv($output, ['Empresas totales', Empresa::count()], ';');
            fputcsv($output, ['Empresas activas', Empresa::where('estado', 'activa')->count()], ';');
            fputcsv($output, ['Empresas pendientes', Empresa::where('estado', 'pendiente')->count()], ';');
            fputcsv($output, ['Candidatos totales', Candidato::count()], ';');
            fputcsv($output, ['Candidatos aprobados', Candidato::where('solicitud_estado', 'aprobada')->count()], ';');
            fputcsv($output, ['Solicitudes de servicio', Vacante::count()], ';');
            fputcsv($output, ['Solicitudes activas', Vacante::where('estado', 'activa')->count()], ';');
            fputcsv($output, ['Tareas totales', ServicioAsignado::count()], ';');

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
