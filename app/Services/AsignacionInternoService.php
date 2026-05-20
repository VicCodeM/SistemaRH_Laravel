<?php

namespace App\Services;

use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sugerencias de internos para asignar a una solicitud de servicio.
 *
 * Reglas:
 *  - El interno debe estar activo y con disponibilidad = 'disponible'.
 *  - Debe tener la especialidad requerida (interno_servicio).
 *  - Debe tener capacidad disponible (carga < capacidad_maxima).
 *  - Se ordena por menor carga (más libre primero).
 */
class AsignacionInternoService
{
    /**
     * Lista ordenada de candidatos elegibles.
     */
    public function candidatos(ServicioAsignado $solicitud): Collection
    {
        return User::query()
            ->where('rol', 'interno')
            ->where('estado', 'activo')
            ->where('disponibilidad', 'disponible')
            ->when($solicitud->servicio_id, fn ($q, $servicioId) =>
                $q->whereHas('serviciosCapacitados', fn ($s) =>
                    $s->where('catalogo_servicios.id', $servicioId)
                )
            )
            ->whereColumn('carga_trabajo_horas', '<', 'capacidad_maxima_horas')
            ->orderBy('carga_trabajo_horas')
            ->orderBy('name')
            ->get();
    }

    /**
     * El mejor candidato (el primero de la lista ordenada).
     */
    public function sugerirMejor(ServicioAsignado $solicitud): ?User
    {
        return $this->candidatos($solicitud)->first();
    }

    /**
     * Evalúa la compatibilidad de un interno con una solicitud.
     *
     * @return array{
     *   puede:bool, puntaje:int, categoria:string,
     *   detalles:array<int,string>, resumen:string
     * }
     */
    public function evaluarCompatibilidad(ServicioAsignado $solicitud, User $interno): array
    {
        $puntaje    = 0;
        $detalles   = [];
        $bloqueante = false;

        // Especialidad (peso fuerte: 50 pts)
        $tieneEspecialidad = ! $solicitud->servicio_id
            || $interno->tieneEspecialidadEn($solicitud->servicio_id);
        if ($tieneEspecialidad) {
            $puntaje += 50;
            $detalles[] = 'Tiene capacitación en el servicio.';
        } else {
            $bloqueante = true;
            $detalles[] = 'Sin capacitación en este servicio.';
        }

        // Disponibilidad (25 pts)
        if ($interno->disponibilidad === 'disponible' && $interno->estaActivo()) {
            $puntaje += 25;
            $detalles[] = 'Disponible para tomar trabajos.';
        } else {
            $bloqueante = true;
            $detalles[] = 'No disponible (' . ($interno->disponibilidad ?? 'sin definir') . ').';
        }

        // Carga de trabajo (25 pts proporcionales)
        $ocupacion = (float) $interno->ocupacionPorcentaje();
        $puntosCarga = (int) round((100 - $ocupacion) * 0.25);
        $puntaje += max(0, $puntosCarga);
        $detalles[] = "Ocupación actual: {$ocupacion}% ({$interno->carga_trabajo_horas}/{$interno->capacidad_maxima_horas} h).";

        $puntaje   = min(100, max(0, $puntaje));
        $categoria = $this->categorizar($bloqueante, $puntaje, $ocupacion, $tieneEspecialidad);
        $resumen   = $this->resumirCategoria($categoria, $interno);

        return [
            'puede'     => ! $bloqueante,
            'puntaje'   => $puntaje,
            'categoria' => $categoria,
            'detalles'  => $detalles,
            'resumen'   => $resumen,
        ];
    }

    /**
     * Clasifica TODOS los internos de la plataforma para esta solicitud.
     *
     * @return array{aptos:Collection, dudosos:Collection, no_aptos:Collection}
     */
    public function clasificarInternos(ServicioAsignado $solicitud): array
    {
        $internos = User::where('rol', 'interno')
            ->where('estado', 'activo')
            ->with('serviciosCapacitados')
            ->orderBy('name')
            ->get();

        $evaluados = $internos->map(fn (User $i) => [
            'interno'        => $i,
            'compatibilidad' => $this->evaluarCompatibilidad($solicitud, $i),
        ])->sortByDesc(fn ($x) => $x['compatibilidad']['puntaje'])->values();

        return [
            'aptos'    => $evaluados->where('compatibilidad.categoria', 'aptos')->values(),
            'dudosos'  => $evaluados->where('compatibilidad.categoria', 'dudosos')->values(),
            'no_aptos' => $evaluados->where('compatibilidad.categoria', 'no_aptos')->values(),
        ];
    }

    private function categorizar(bool $bloqueante, int $puntaje, float $ocupacion, bool $tieneEspecialidad): string
    {
        if ($bloqueante && ! $tieneEspecialidad) {
            return 'no_aptos';
        }
        if ($bloqueante || $ocupacion >= 85 || $puntaje < 60) {
            return 'dudosos';
        }
        return 'aptos';
    }

    private function resumirCategoria(string $categoria, User $interno): string
    {
        return match ($categoria) {
            'aptos'    => "{$interno->name} está capacitado y con capacidad para tomar el servicio.",
            'dudosos'  => "{$interno->name} podría tomar el servicio pero tiene carga alta o disponibilidad parcial.",
            'no_aptos' => "{$interno->name} no cumple los requisitos. Asignación solo con excepción.",
            default    => '',
        };
    }
}
