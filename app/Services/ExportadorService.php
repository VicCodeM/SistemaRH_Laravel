<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Genera descargas CSV de cualquier listado.
 * Excel abre CSV nativamente, así que cubrimos "Excel" sin librería extra.
 */
class ExportadorService
{
    /**
     * Devuelve una StreamedResponse con un CSV listo para descargar.
     *
     * @param  string   $nombreArchivo  Sin extensión (ej. "candidatos-vacante-5")
     * @param  array    $cabeceras      ["Nombre", "Correo", "Estado"]
     * @param  iterable $filas          Cada fila es array de valores en el mismo orden que cabeceras
     */
    public function csv(string $nombreArchivo, array $cabeceras, iterable $filas): StreamedResponse
    {
        $archivo = $nombreArchivo . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        return new StreamedResponse(function () use ($cabeceras, $filas) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel reconozca acentos
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $cabeceras, ';');
            foreach ($filas as $fila) {
                fputcsv($handle, $fila, ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
