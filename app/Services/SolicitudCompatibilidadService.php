<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Vacante;
use Illuminate\Support\Str;

class SolicitudCompatibilidadService
{
    private const RANGOS_ESTUDIO = [
        'sin_estudios' => 0,
        'primaria' => 1,
        'secundaria' => 2,
        'preparatoria' => 3,
        'tecnico' => 4,
        'licenciatura' => 5,
        'ingenieria' => 5,
        'maestria' => 6,
        'doctorado' => 7,
    ];

    public function evaluar(Vacante $vacante, Candidato $candidato): array
    {
        $nivelRequerido = Vacante::normalizarNivelEstudios($vacante->nivel_estudios_minimo);
        $nivelCandidato = $this->mejorNivelCandidato($candidato);
        $experienciaMinima = max(0, (int) ($vacante->experiencia_minima ?? 0));
        $experienciaCandidato = max(0, (int) ($candidato->experiencia_anios ?? 0));
        $areasRequeridas = $this->fragmentarTexto($vacante->area_requerida);

        $perfilTexto = $this->normalizarTexto($this->textoPerfilCandidato($candidato));
        $solicitudTexto = $this->normalizarTexto(implode(' ', array_filter([
            $vacante->titulo,
            $vacante->requerimientos,
        ])));

        $hayRequisitos = $nivelRequerido || ! empty($areasRequeridas) || $experienciaMinima > 0;

        $cumpleEstudios = $nivelRequerido === null
            ? true
            : $this->puntajeNivel($nivelCandidato) >= $this->puntajeNivel($nivelRequerido);

        $cumpleArea = empty($areasRequeridas)
            ? true
            : $this->areaCoincideConPerfil($vacante->area_requerida, $perfilTexto);

        $cumpleExperiencia = $experienciaMinima === 0
            ? true
            : $experienciaCandidato >= $experienciaMinima;

        $puntaje = 0;
        $detalles = [];

        if ($nivelRequerido !== null) {
            if ($cumpleEstudios) {
                $puntaje += 40;
                $detalles[] = 'Cumple estudios mínimos: ' . Vacante::nivelEstudiosLabel($nivelCandidato);
            } else {
                $detalles[] = 'Pide estudios mínimos: ' . Vacante::nivelEstudiosLabel($nivelRequerido);
            }
        }

        if (! empty($areasRequeridas)) {
            if ($cumpleArea) {
                $puntaje += 35;
                $detalles[] = 'Área compatible: ' . implode(', ', $areasRequeridas);
            } else {
                $detalles[] = 'Área no coincide: ' . implode(', ', $areasRequeridas);
            }
        }

        if ($experienciaMinima > 0) {
            if ($cumpleExperiencia) {
                $puntaje += 20;
                $detalles[] = "Experiencia suficiente: {$experienciaCandidato}/{$experienciaMinima} año(s)";
            } else {
                $detalles[] = "Experiencia insuficiente: {$experienciaCandidato}/{$experienciaMinima} año(s)";
            }
        }

        $bonusTexto = $this->coincideConSolicitud($perfilTexto, $solicitudTexto) ? 5 : 0;
        if ($bonusTexto > 0) {
            $puntaje += $bonusTexto;
            $detalles[] = 'Perfil relacionado con la solicitud';
        }

        $fallos = collect([$cumpleEstudios, $cumpleArea, $cumpleExperiencia])
            ->filter(fn ($valor) => $valor === false)
            ->count();

        $categoria = $hayRequisitos
            ? ($fallos === 0 ? 'aptos' : ($fallos === 1 ? 'dudosos' : 'no_aptos'))
            : 'aptos';

        if (! $hayRequisitos && $puntaje === 0) {
            $puntaje = $bonusTexto;
        }

        return [
            'categoria' => $categoria,
            'puntaje' => $puntaje,
            'nivel_candidato' => $nivelCandidato,
            'nivel_candidato_label' => Vacante::nivelEstudiosLabel($nivelCandidato),
            'nivel_requerido' => $nivelRequerido,
            'nivel_requerido_label' => Vacante::nivelEstudiosLabel($nivelRequerido),
            'cumple_estudios' => $cumpleEstudios,
            'cumple_area' => $cumpleArea,
            'cumple_experiencia' => $cumpleExperiencia,
            'experiencia_candidato' => $experienciaCandidato,
            'experiencia_minima' => $experienciaMinima,
            'areas_requeridas' => $areasRequeridas,
            'resumen' => $this->resumen($cumpleEstudios, $cumpleArea, $cumpleExperiencia, $hayRequisitos),
            'detalles' => $detalles,
        ];
    }

    private function mejorNivelCandidato(Candidato $candidato): ?string
    {
        $niveles = collect(array_filter([
            $candidato->escolaridad,
            data_get($candidato->escolaridad_detallada, '*.nivel'),
            data_get($candidato->escolaridad_detallada, '*.titulo'),
            data_get($candidato->escolaridad_detallada, '*.nombre'),
        ]))
            ->flatten()
            ->filter()
            ->map(fn ($valor) => $this->normalizarNivelEstudios((string) $valor))
            ->filter()
            ->unique()
            ->values();

        if ($niveles->isEmpty()) {
            return null;
        }

        return $niveles->sortByDesc(fn ($nivel) => $this->puntajeNivel($nivel))->first();
    }

    private function normalizarNivelEstudios(?string $texto): ?string
    {
        if ($texto === null || $texto === '') {
            return null;
        }

        $texto = Str::of($texto)->ascii()->lower()->trim()->value();

        return match (true) {
            Str::contains($texto, 'doctor') => 'doctorado',
            Str::contains($texto, 'maestr') => 'maestria',
            Str::contains($texto, 'ingenier') => 'ingenieria',
            Str::contains($texto, 'licenci') => 'licenciatura',
            Str::contains($texto, 'bachiller') => 'preparatoria',
            Str::contains($texto, 'preparator') => 'preparatoria',
            Str::contains($texto, 'tecnic') => 'tecnico',
            Str::contains($texto, 'secund') => 'secundaria',
            Str::contains($texto, 'primari') => 'primaria',
            Str::contains($texto, 'sin estudios') => 'sin_estudios',
            default => $this->nivelDesdeClave($texto),
        };
    }

    private function nivelDesdeClave(string $texto): ?string
    {
        return array_key_exists($texto, self::RANGOS_ESTUDIO) ? $texto : null;
    }

    private function puntajeNivel(?string $nivel): int
    {
        return self::RANGOS_ESTUDIO[$nivel ?? ''] ?? 0;
    }

    private function textoPerfilCandidato(Candidato $candidato): string
    {
        $partes = [
            $candidato->nombre,
            $candidato->apellido_paterno,
            $candidato->apellido_materno,
            $candidato->puesto_deseado,
            $candidato->escolaridad,
            $candidato->habilidades,
        ];

        foreach ((array) $candidato->escolaridad_detallada as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $partes[] = $fila['nivel'] ?? null;
            $partes[] = $fila['nombre'] ?? null;
            $partes[] = $fila['titulo'] ?? null;
        }

        foreach ((array) $candidato->historial_laboral as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $partes[] = $fila['empresa'] ?? null;
            $partes[] = $fila['puesto'] ?? null;
            $partes[] = $fila['motivo'] ?? null;
        }

        return trim(implode(' ', array_filter($partes)));
    }

    private function normalizarTexto(?string $texto): string
    {
        $texto = Str::of((string) $texto)->ascii()->lower()->replaceMatches('/\s+/', ' ')->trim()->value();

        return $texto;
    }

    private function fragmentarTexto(?string $texto): array
    {
        $texto = $this->normalizarTexto($texto);

        if ($texto === '') {
            return [];
        }

        return collect(preg_split('/[\n,;\/|]+/', $texto) ?: [])
            ->map(fn ($fragmento) => trim($fragmento))
            ->filter(fn ($fragmento) => $fragmento !== '' && strlen($fragmento) > 2)
            ->unique()
            ->values()
            ->all();
    }

    private function coincideConAlgunFragmento(string $texto, array $fragmentos): bool
    {
        foreach ($fragmentos as $fragmento) {
            if ($fragmento !== '' && Str::contains($texto, $fragmento)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decide si el perfil del candidato corresponde al area requerida.
     * 1) Match literal de los fragmentos del area (comportamiento original).
     * 2) Match por FAMILIA: expande el area a sus palabras clave (ej. "Sistemas / TI"
     *    => desarrollo, programacion, laravel, php, redes...) y las busca en el perfil.
     *    Asi una desarrolladora Laravel cae en "Sistemas / TI", pero un medico no.
     * Si el area no pertenece a ninguna familia conocida, se queda solo con el match literal.
     */
    private function areaCoincideConPerfil(?string $areaRequerida, string $perfilTexto): bool
    {
        $area = $this->normalizarTexto($areaRequerida);

        if ($area === '') {
            return true;
        }

        // 1) Match literal (lo de siempre)
        $fragmentos = $this->fragmentarTexto($areaRequerida);
        if (! empty($fragmentos) && $this->coincideConAlgunFragmento($perfilTexto, $fragmentos)) {
            return true;
        }

        // 2) Match por familia de palabras clave
        foreach ($this->familiasArea() as $familia) {
            $perteneceALaFamilia = false;
            foreach ($familia['claves'] as $clave) {
                if (Str::contains($area, $clave)) {
                    $perteneceALaFamilia = true;
                    break;
                }
            }

            if (! $perteneceALaFamilia) {
                continue;
            }

            foreach ($familia['keywords'] as $keyword) {
                if (Str::contains($perfilTexto, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Familias de area con sus palabras clave (todo en minusculas y sin acentos,
     * igual que normalizarTexto). 'claves' = como se reconoce el area requerida;
     * 'keywords' = terminos del perfil del candidato que cuentan como coincidencia.
     */
    private function familiasArea(): array
    {
        return [
            ['claves' => ['administracion', 'administrativo'], 'keywords' => ['administracion', 'administrativo', 'admin', 'gestion', 'oficina', 'secretari', 'asistente']],
            ['claves' => ['contabilidad', 'contable', 'contador', 'contaduria'], 'keywords' => ['contabilidad', 'contable', 'contador', 'contaduria', 'impuestos', 'fiscal', 'auditoria', 'facturacion']],
            ['claves' => ['finanzas', 'financ'], 'keywords' => ['finanzas', 'financiero', 'financiera', 'tesoreria', 'credito', 'cobranza', 'inversiones', 'presupuesto']],
            ['claves' => ['recursos humanos', 'capital humano', ' rh', 'rh ', 'reclutamiento'], 'keywords' => ['recursos humanos', 'capital humano', 'reclutamiento', 'nomina', 'seleccion de personal', 'relaciones laborales', 'reclutador']],
            ['claves' => ['sistemas', 'ti', 'tic', 'informatica', 'computacion', 'software', 'tecnologias de la informacion'], 'keywords' => ['sistemas', 'informatica', 'computacion', 'software', 'desarrollo', 'desarrollador', 'desarrolladora', 'programacion', 'programador', 'programadora', 'redes', 'soporte tecnico', 'base de datos', 'laravel', 'php', 'python', 'java', 'javascript', 'sql', 'html', 'css', 'web', 'frontend', 'backend', 'fullstack', 'devops', 'tecnologias de la informacion', 'ti']],
            ['claves' => ['marketing', 'mercadotecnia', 'publicidad'], 'keywords' => ['marketing', 'mercadotecnia', 'publicidad', 'community', 'redes sociales', 'branding', 'seo', 'contenido', 'comunicacion', 'diseno']],
            ['claves' => ['ventas', 'comercial'], 'keywords' => ['ventas', 'vendedor', 'vendedora', 'comercial', 'comercializacion', 'asesor de ventas', 'telemarketing', 'mostrador', 'promotor']],
            ['claves' => ['ingenieria', 'ingeniero'], 'keywords' => ['ingenieria', 'ingeniero', 'ingeniera', 'mecanica', 'electrica', 'electronica', 'civil', 'industrial', 'mecatronica']],
            ['claves' => ['produccion', 'operaciones', 'manufactura', 'mantenimiento', 'industrial'], 'keywords' => ['produccion', 'operaciones', 'manufactura', 'planta', 'ensamble', 'maquila', 'mantenimiento', 'calidad', 'industrial', 'operador', 'operario', 'tecnico']],
            ['claves' => ['logistica', 'almacen', 'transporte'], 'keywords' => ['logistica', 'almacen', 'inventario', 'transporte', 'distribucion', 'cadena de suministro', 'supply', 'embarques', 'chofer', 'reparto']],
            ['claves' => ['legal', 'juridico', 'derecho'], 'keywords' => ['legal', 'juridico', 'abogado', 'abogada', 'derecho', 'leyes', 'litigio', 'notaria']],
            ['claves' => ['salud', 'medicina', 'medico'], 'keywords' => ['salud', 'medicina', 'medico', 'medica', 'enfermeria', 'enfermero', 'enfermera', 'clinico', 'hospital', 'paramedico', 'doctor', 'doctora', 'farmacia', 'laboratorio clinico', 'cuidados']],
            ['claves' => ['educacion', 'docente', 'ensenanza'], 'keywords' => ['educacion', 'docente', 'maestro', 'maestra', 'profesor', 'profesora', 'pedagogia', 'ensenanza', 'capacitacion', 'instructor']],
        ];
    }

    private function coincideConSolicitud(string $perfil, string $solicitud): bool
    {
        if ($perfil === '' || $solicitud === '') {
            return false;
        }

        $palabras = collect(explode(' ', $solicitud))
            ->filter(fn ($palabra) => strlen($palabra) > 3)
            ->unique()
            ->values();

        foreach ($palabras as $palabra) {
            if (Str::contains($perfil, $palabra)) {
                return true;
            }
        }

        return false;
    }

    private function resumen(bool $estudios, bool $area, bool $experiencia, bool $hayRequisitos): string
    {
        if (! $hayRequisitos) {
            return 'No hay requisitos estructurados; el sistema ordena por compatibilidad general.';
        }

        $partes = [];

        $partes[] = $estudios ? 'estudios OK' : 'estudios no cumplen';
        $partes[] = $area ? 'área OK' : 'área no coincide';
        $partes[] = $experiencia ? 'experiencia OK' : 'experiencia insuficiente';

        return 'Compatibilidad: ' . implode(', ', $partes) . '.';
    }
}
