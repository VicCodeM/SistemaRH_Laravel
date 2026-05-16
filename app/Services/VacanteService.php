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

        $datos['tipo_servicio'] = $input['tipo_servicio'] ?? null;
        $datos['titulo'] = $input['titulo'] ?? null;

        $datos['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico(
            $input['nivel_jerarquico'] ?? null
        );

        $datos['nivel_estudios_minimo'] = Vacante::normalizarNivelEstudios(
            $input['nivel_estudios_minimo'] ?? null
        );

        $datos['area_requerida'] = $this->limpiarNulo($input['area_requerida'] ?? null);
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

        $datos['ubicacion'] = $this->limpiarNulo($input['ubicacion'] ?? null);

        return $datos;
    }

    /**
     * Crea una nueva vacante con los datos ya normalizados.
     */
    public function crear(array $input, int $empresaId, string $estadoInicial = 'activa'): Vacante
    {
        $datos = $this->prepararDatos($input);
        $datos['empresa_id'] = $empresaId;
        $datos['estado'] = $estadoInicial;
        $datos['fecha_publicacion'] = now();

        return Vacante::create($datos);
    }

    /**
     * Actualiza una vacante existente con los datos normalizados.
     */
    public function actualizar(Vacante $vacante, array $input): Vacante
    {
        $datos = $this->prepararDatos($input);
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
