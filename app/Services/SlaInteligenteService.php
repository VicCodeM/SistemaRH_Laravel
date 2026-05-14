<?php

namespace App\Services;

class SlaInteligenteService
{
    public function defaultRules(): array
    {
        return [
            'base_minutes' => [
                'default' => ['alta' => 45, 'media' => 180, 'baja' => 480],
                'solicitud_entrevista_candidato' => ['alta' => 30, 'media' => 90, 'baja' => 240],
                'solicitud_empresa_soporte' => ['alta' => 25, 'media' => 80, 'baja' => 240],
                'solicitud_empresa_vacante' => ['alta' => 60, 'media' => 240, 'baja' => 720],
                'solicitud_empresa_capacitacion' => ['alta' => 120, 'media' => 360, 'baja' => 960],
                'solicitud_empresa_seguimiento' => ['alta' => 90, 'media' => 300, 'baja' => 900],
            ],
            'escalation_extensions' => [
                'alta' => [20, 35],
                'media' => [90, 150],
                'baja' => [240, 360],
            ],
            'assignment' => [
                'mode' => 'least_load',
            ],
        ];
    }

    public function clasificar(
        string $tipo,
        string $titulo,
        string $mensaje,
        string $prioridadSolicitada = 'media',
        array $rules = []
    ): array {
        $effectiveRules = $this->mergeRules($rules);
        $tipoNorm = $this->normalize($tipo);
        $text = $this->normalize($titulo . ' ' . $mensaje);
        $score = 0;
        $motivos = [];

        $requested = in_array($prioridadSolicitada, ['alta', 'media', 'baja'], true)
            ? $prioridadSolicitada
            : 'media';

        $tipoWeights = [
            'solicitud_entrevista_candidato' => 5,
            'solicitud_empresa_soporte' => 4,
            'solicitud_empresa_vacante' => 3,
            'solicitud_empresa_capacitacion' => 2,
            'solicitud_empresa_seguimiento' => 2,
        ];
        foreach ($tipoWeights as $key => $weight) {
            if ($tipoNorm === $this->normalize($key) || str_starts_with($tipoNorm, $this->normalize($key))) {
                $score += $weight;
                $motivos[] = 'tipo:' . $key;
                break;
            }
        }

        $highKeywords = ['urgente', 'inmediato', 'hoy', 'bloqueo', 'caido', 'error', 'entrevista', 'contratacion', 'incidente', 'critico'];
        $mediumKeywords = ['vacante', 'capacitacion', 'prioridad', 'seguimiento', 'reclutamiento', 'talento'];
        $lowKeywords = ['consulta', 'cuando puedan', 'sin prisa', 'informativo'];

        foreach ($highKeywords as $word) {
            if (str_contains($text, $word)) {
                $score += 3;
                $motivos[] = 'kw_alta:' . $word;
            }
        }
        foreach ($mediumKeywords as $word) {
            if (str_contains($text, $word)) {
                $score += 1;
                $motivos[] = 'kw_media:' . $word;
            }
        }
        foreach ($lowKeywords as $word) {
            if (str_contains($text, $word)) {
                $score -= 1;
                $motivos[] = 'kw_baja:' . $word;
            }
        }

        if ($requested === 'alta') {
            $score += 2;
            $motivos[] = 'prioridad_solicitada_alta';
        } elseif ($requested === 'media') {
            $score += 1;
            $motivos[] = 'prioridad_solicitada_media';
        } else {
            $score -= 1;
            $motivos[] = 'prioridad_solicitada_baja';
        }

        $prioridad = $this->prioridadPorScore($score);
        if ($requested === 'alta' && $prioridad !== 'alta') {
            $prioridad = 'alta';
            $motivos[] = 'override_solicitud_alta';
        }
        if ($requested === 'media' && $prioridad === 'baja') {
            $prioridad = 'media';
            $motivos[] = 'override_solicitud_media';
        }

        $slaMinutes = $this->slaBaseMinutes($prioridad, $tipoNorm, $effectiveRules);

        return [
            'prioridad' => $prioridad,
            'sla_minutes' => $slaMinutes,
            'score' => $score,
            'motivos' => array_values(array_unique($motivos)),
        ];
    }

    public function extensionEscalacionMinutes(string $prioridad, int $nivel, array $rules = []): int
    {
        $effectiveRules = $this->mergeRules($rules);
        $priority = in_array($prioridad, ['alta', 'media', 'baja'], true) ? $prioridad : 'media';
        $level = max(1, $nivel);

        $map = $effectiveRules['escalation_extensions'] ?? [];
        $values = $map[$priority] ?? [];
        if (is_array($values) && !empty($values)) {
            $index = min($level - 1, count($values) - 1);
            $value = (int) ($values[$index] ?? 0);
            if ($value > 0) {
                return $value;
            }
        }

        return match ($priority) {
            'alta' => 20,
            'media' => 90,
            default => 240,
        };
    }

    private function prioridadPorScore(int $score): string
    {
        return match (true) {
            $score >= 7 => 'alta',
            $score >= 3 => 'media',
            default => 'baja',
        };
    }

    private function slaBaseMinutes(string $prioridad, string $tipoNorm, array $rules): int
    {
        $base = is_array($rules['base_minutes'] ?? null) ? $rules['base_minutes'] : [];

        $matchKey = 'default';
        foreach (array_keys($base) as $rawKey) {
            if ($this->normalize((string) $rawKey) === $tipoNorm) {
                $matchKey = (string) $rawKey;
                break;
            }
        }

        $priorityMap = is_array($base[$matchKey] ?? null) ? $base[$matchKey] : [];
        if (!empty($priorityMap[$prioridad])) {
            return max(1, (int) $priorityMap[$prioridad]);
        }

        return match ($prioridad) {
            'alta' => 45,
            'media' => 180,
            default => 480,
        };
    }

    private function mergeRules(array $rules): array
    {
        $defaults = $this->defaultRules();
        if (empty($rules)) {
            return $defaults;
        }

        $merged = $defaults;
        foreach (['base_minutes', 'escalation_extensions', 'assignment'] as $key) {
            if (!isset($rules[$key]) || !is_array($rules[$key])) {
                continue;
            }
            $merged[$key] = array_replace_recursive($merged[$key], $rules[$key]);
        }

        return $merged;
    }

    private function normalize(string $value): string
    {
        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower(trim($value), 'UTF-8')
            : strtolower(trim($value));

        return strtr($normalized, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n',
        ]);
    }
}
