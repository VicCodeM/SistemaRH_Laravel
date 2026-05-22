<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Maneja la identidad pública del sitio: SEO, favicon, contenido del landing
 * y las páginas legales (privacidad y términos).
 *
 * Todo se guarda en la tabla existente `configuracion_sistemas` (clave-valor),
 * sin crear tablas ni columnas nuevas.
 */
class SitioService
{
    public const CARPETA_FAVICON = 'sitio';
    private const GRUPO = 'sitio';

    /**
     * Claves administrables y su valor por defecto (el contenido actual del landing).
     *
     * @var array<string, string|null>
     */
    private const DEFAULTS = [
        // Identidad / SEO
        'sitio_nombre'        => 'SistemaRH',
        'sitio_subtitulo'     => 'Gestión de talento',
        'sitio_descripcion'   => 'Plataforma de gestión de talento - Reclutamiento, seguimiento de candidatos y automatización de procesos.',
        'sitio_favicon'       => null,

        // Hero del landing
        'landing_hero_badge'    => 'Plataforma de Gestión de Talento',
        'landing_hero_titulo'   => "Recluta, gestiona\ny desarrolla talento",
        'landing_hero_acento'   => 'de forma inteligente',
        'landing_hero_subtitulo' => 'Conecta empresas con candidatos, automatiza procesos de RH y gestiona todo tu equipo desde un solo lugar.',

        // Sección de características
        'landing_feat_label'    => '¿Qué incluye la plataforma?',
        'landing_feat_1_titulo' => 'Catálogo de servicios RH',
        'landing_feat_1_texto'  => 'Reclutamiento, capacitación, coaching y más, organizados por nivel jerárquico.',
        'landing_feat_2_titulo' => 'Matching inteligente',
        'landing_feat_2_texto'  => 'Sugiere candidatos compatibles según jerarquía, perfil y habilidades requeridas.',
        'landing_feat_3_titulo' => 'SLA inteligente',
        'landing_feat_3_texto'  => 'Tiempos de respuesta automáticos calculados por prioridad e impacto del ticket.',
        'landing_feat_4_titulo' => 'Chat integrado',
        'landing_feat_4_texto'  => 'Comunicación directa entre candidatos, empresas e internos sin salir de la plataforma.',
        'landing_feat_5_titulo' => 'Aprobaciones controladas',
        'landing_feat_5_texto'  => 'Flujo de aprobación de empresas y candidatos con control total del administrador.',

        // Footer
        'landing_footer' => 'SistemaRH. Todos los derechos reservados.',

        // Páginas legales (texto simple, respeta saltos de línea)
        'privacidad_contenido' => '',
        'terminos_contenido'   => '',
    ];

    /**
     * Devuelve todos los valores del sitio (guardados o por defecto) en UNA sola consulta.
     *
     * @return array<string, string|null>
     */
    public function valores(): array
    {
        $guardados = ConfiguracionSistema::query()
            ->whereIn('clave', array_keys(self::DEFAULTS))
            ->pluck('valor', 'clave')
            ->toArray();

        $resultado = [];
        foreach (self::DEFAULTS as $clave => $defecto) {
            $valor = $guardados[$clave] ?? null;
            $resultado[$clave] = ($valor === null || $valor === '') ? $defecto : $valor;
        }

        return $resultado;
    }

    /**
     * Solo las claves de texto editables (sin el favicon, que se maneja aparte).
     *
     * @return array<int, string>
     */
    public function clavesTexto(): array
    {
        return array_values(array_diff(array_keys(self::DEFAULTS), ['sitio_favicon']));
    }

    /**
     * Guarda los textos del sitio. Solo persiste claves conocidas.
     *
     * @param array<string, mixed> $datos
     */
    public function guardarTextos(array $datos): void
    {
        foreach ($this->clavesTexto() as $clave) {
            if (! array_key_exists($clave, $datos)) {
                continue;
            }

            ConfiguracionSistema::guardar($clave, (string) ($datos[$clave] ?? ''), [
                'grupo' => self::GRUPO,
                'tipo'  => 'string',
            ]);
        }
    }

    /**
     * Sube un nuevo favicon (reemplaza el anterior) y devuelve su ruta relativa.
     */
    public function guardarFavicon(UploadedFile $archivo): string
    {
        $this->eliminarFavicon();

        $ruta = $archivo->store(self::CARPETA_FAVICON, 'public');

        ConfiguracionSistema::guardar('sitio_favicon', $ruta, [
            'grupo' => self::GRUPO,
            'tipo'  => 'string',
        ]);

        return $ruta;
    }

    /**
     * Elimina el favicon actual del almacenamiento y limpia su valor.
     */
    public function eliminarFavicon(): void
    {
        $actual = ConfiguracionSistema::texto('sitio_favicon');

        if ($actual && Storage::disk('public')->exists($actual)) {
            Storage::disk('public')->delete($actual);
        }

        ConfiguracionSistema::guardar('sitio_favicon', '', [
            'grupo' => self::GRUPO,
            'tipo'  => 'string',
        ]);
    }

    /**
     * URL pública del favicon, o null si no hay.
     */
    public function faviconUrl(): ?string
    {
        $ruta = ConfiguracionSistema::texto('sitio_favicon');

        return $ruta ? asset('storage/' . $ruta) : null;
    }

    /**
     * Parte el nombre de la marca en dos: base (color normal) y acento (color azul).
     *
     * Reglas:
     *  - Si hay espacio, la última palabra es el acento ("Mi Empresa RH" → base "Mi Empresa ", acento "RH").
     *  - Si no, corta en la última transición minúscula→Mayúscula ("SistemaRH" → "Sistema" + "RH").
     *  - Si no aplica nada, todo es base y el acento queda vacío.
     *
     * @return array{base: string, acento: string}
     */
    public static function partirMarca(string $nombre): array
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return ['base' => 'SistemaRH', 'acento' => ''];
        }

        if (str_contains($nombre, ' ')) {
            $pos = mb_strrpos($nombre, ' ');

            return [
                'base'   => mb_substr($nombre, 0, $pos + 1),
                'acento' => mb_substr($nombre, $pos + 1),
            ];
        }

        if (preg_match('/^(.*[a-záéíóúñ])([A-ZÁÉÍÓÚÑ].*)$/u', $nombre, $m)) {
            return ['base' => $m[1], 'acento' => $m[2]];
        }

        return ['base' => $nombre, 'acento' => ''];
    }
}
