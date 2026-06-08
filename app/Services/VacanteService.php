<?php

namespace App\Services;

use App\Models\CatalogoServicio;
use App\Models\Vacante;

/**
 * Servicio para centralizar la creación, actualización y normalización
 * de solicitudes/vacantes. Elimina duplicación entre Admin y Empresa.
 */
class VacanteService
{
    /**
     * Normaliza los campos de entrada crudos para una vacante.
     * Aplica trims, nulls y conversiones de catálogo.
     */
    public function prepararDatos(array $input): array
    {
        $datos = [];

        // Vacante = solo reclutamiento. Default seguro para no romper NOT NULL.
        $datos['tipo_servicio'] = $input['tipo_servicio'] ?? 'reclutamiento';

        // Agregar tipo_contrato si viene
        if (array_key_exists('tipo_contrato', $input)) {
            $datos['tipo_contrato'] = $this->limpiarNulo($input['tipo_contrato'] ?? null);
        }

        $datos['titulo'] = $input['titulo'] ?? null;

        $datos['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico(
            $input['nivel_jerarquico'] ?? null
        );

        $datos['nivel_estudios_minimo'] = Vacante::normalizarNivelEstudios(
            $input['nivel_estudios_minimo'] ?? null
        );

        // Acepta varias areas (array, desde el admin) o una sola (string, desde empresa).
        $area = $input['area_requerida'] ?? null;
        if (is_array($area)) {
            $area = implode(', ', array_filter(array_map('trim', $area), fn ($v) => $v !== ''));
        }
        $datos['area_requerida'] = $this->limpiarNulo($area);
        $datos['experiencia_minima'] = isset($input['experiencia_minima']) && $input['experiencia_minima'] !== ''
            ? (int) $input['experiencia_minima']
            : null;

        $datos['descripcion'] = $this->limpiarNulo($input['descripcion'] ?? null);
        $datos['requerimientos'] = $this->limpiarNulo($input['requerimientos'] ?? null);

        $datos['salario_min'] = isset($input['salario_min']) && $input['salario_min'] !== ''
            ? (float) $input['salario_min']
            : null;

        $datos['salario_max'] = isset($input['salario_max']) && $input['salario_max'] !== ''
            ? (float) $input['salario_max']
            : null;

        $datos['ingresos_ofrecidos'] = $this->limpiarNulo($input['ingresos_ofrecidos'] ?? null);
        $datos['prestaciones'] = $this->limpiarNulo($input['prestaciones'] ?? null);

        $datos['ubicacion'] = $this->limpiarNulo($input['ubicacion'] ?? null);

        // Cupos: cantidad de personas a contratar. Mínimo 1.
        $datos['cupos'] = isset($input['cupos']) && (int) $input['cupos'] > 0
            ? (int) $input['cupos']
            : 1;

        // Notas internas: solo si vienen (no las pisamos con null si no se envían)
        if (array_key_exists('notas_internas', $input)) {
            $datos['notas_internas'] = $this->limpiarNulo($input['notas_internas']);
        }

        // Presentacion visual (diapositivas): solo si viene en el input
        if (array_key_exists('presentacion_activa', $input)) {
            $datos['presentacion_activa'] = (bool) $input['presentacion_activa'];
        }

        return $datos;
    }

    /**
     * Crea una nueva vacante con los datos ya normalizados.
     */
    public function crear(array $input, int $empresaId, string $estadoInicial = 'activa'): Vacante
    {
        $datos = $this->prepararDatos($input);
        $datos['empresa_id'] = $empresaId;
        $datos['estado'] = $input['estado'] ?? $estadoInicial;
        $datos['fecha_publicacion'] = now();

        return Vacante::create($datos);
    }

    /**
     * Actualiza una vacante existente con los datos normalizados.
     */
    /**
     * Cierra manualmente una vacante con motivo opcional.
     * Útil para cerrar antes de cubrir todos los cupos (ej: cliente canceló).
     */
    public function cerrarManual(Vacante $vacante, ?string $motivo = null): void
    {
        if ($vacante->estado === 'cerrada') {
            return;
        }

        $vacante->update([
            'estado'        => 'cerrada',
            'cierre_motivo' => trim((string) $motivo) ?: null,
            'fecha_cierre'  => now(),
        ]);
    }

    /**
     * Reabre una vacante cerrada manualmente.
     */
    public function reabrir(Vacante $vacante): void
    {
        if ($vacante->estado !== 'cerrada') {
            return;
        }

        if ($vacante->estaLlena()) {
            throw new \DomainException('No puedes reabrir: la vacante ya cubrió todos sus cupos. Aumenta cupos o retira a alguien.');
        }

        $vacante->update([
            'estado'        => 'activa',
            'cierre_motivo' => null,
            'fecha_cierre'  => null,
        ]);
    }

    public function actualizar(Vacante $vacante, array $input): Vacante
    {
        $datos = $this->prepararDatos($input);

        // No permitir bajar cupos por debajo de los ya cubiertos
        $cubiertos = $vacante->cuposCubiertos();
        if (isset($datos['cupos']) && (int) $datos['cupos'] < $cubiertos) {
            throw new \DomainException(
                "No puedes bajar a {$datos['cupos']} cupo(s): ya tienes {$cubiertos} persona(s) seleccionada(s). Retira a alguien primero."
            );
        }

        $vacante->update($datos);

        return $vacante;
    }

    /**
     * Devuelve el string limpio o null si está vacío.
     */
    private function limpiarNulo(?string $valor): ?string
    {
        $limpio = trim((string) $valor);

        return $limpio !== '' ? $limpio : null;
    }
}
