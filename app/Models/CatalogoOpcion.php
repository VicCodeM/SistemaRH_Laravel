<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CatalogoOpcion extends Model
{
    protected $table = 'catalogo_opciones';

    protected $fillable = [
        'grupo',
        'clave',
        'valor',
        'descripcion',
        'activo',
        'orden',
        'es_sistema',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_sistema' => 'boolean',
        'orden' => 'integer',
    ];

    protected static ?bool $tablaDisponible = null;

    public static function tablaDisponible(): bool
    {
        if (static::$tablaDisponible !== null) {
            return static::$tablaDisponible;
        }

        try {
            static::$tablaDisponible = Schema::hasTable('catalogo_opciones');
        } catch (\Throwable $e) {
            static::$tablaDisponible = false;
        }

        return static::$tablaDisponible;
    }

    /**
     * Grupos que el admin puede editar desde el panel.
     * Solo datos de negocio. Los estados/roles del sistema y otros
     * obsoletos (como tipos_servicio) están en gruposInternos().
     */
    public static function gruposGestionables(): array
    {
        return [
            'niveles_jerarquicos' => 'Niveles jerárquicos (vacantes y servicios)',
            'niveles_estudios'    => 'Niveles de estudio',
            'areas_carreras'      => 'Áreas y carreras',
            'tipos_contrato'      => 'Tipos de contrato',
            'sectores_empresa'    => 'Sectores de empresa',
        ];
    }

    /**
     * Grupos técnicos / obsoletos que NO se muestran en el panel admin.
     * Existen en BD pero son invariantes del código (estados, roles) o
     * legacy (tipos_servicio se reemplazó por CatalogoServicio + Vacante hardcoded).
     */
    public static function gruposInternos(): array
    {
        return [
            'roles', 'empresa_estados', 'candidato_estados',
            'vacante_estados', 'postulacion_estados',
            'servicio_asignado_estados',
            'chat_room_tipos', 'disponibilidad_externa',
            'tipos_servicio', // legacy: sustituido por CatalogoServicio
        ];
    }

    /**
     * Mapeo de grupos por módulo (para tabs en el panel admin).
     * Cada catálogo aparece en su módulo principal.
     */
    public static function gruposPorModulo(): array
    {
        return [
            'servicios' => [], // El catálogo principal de servicios (CatalogoServicio) se muestra aparte
            'vacantes'  => ['niveles_jerarquicos', 'niveles_estudios', 'areas_carreras', 'tipos_contrato'],
            'empresas'  => ['sectores_empresa'],
        ];
    }

    public static function gruposDelModulo(string $modulo): array
    {
        return self::gruposPorModulo()[$modulo] ?? [];
    }

    public static function grupoLabel(?string $grupo): string
    {
        return self::gruposGestionables()[$grupo] ?? ucfirst(str_replace('_', ' ', (string) $grupo));
    }

    public static function opciones(string $grupo, array $fallback = []): array
    {
        if (! static::tablaDisponible()) {
            return $fallback;
        }

        $opciones = static::query()
            ->where('grupo', $grupo)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('valor')
            ->get(['clave', 'valor']);

        if ($opciones->isEmpty()) {
            return $fallback;
        }

        return $opciones->pluck('valor', 'clave')->all();
    }

    public static function claves(string $grupo, array $fallback = []): array
    {
        return array_keys(self::opciones($grupo, $fallback));
    }

    public static function label(string $grupo, ?string $clave, ?string $fallback = null): string
    {
        if ($clave === null || $clave === '') {
            return $fallback ?? 'Sin definir';
        }

        $opciones = self::opciones($grupo);

        return $opciones[$clave] ?? $fallback ?? ucfirst(str_replace('_', ' ', (string) $clave));
    }

    public static function defaults(): array
    {
        $base = [
            ['grupo' => 'roles', 'clave' => 'admin', 'valor' => 'Administrador', 'descripcion' => 'Usuario con control total del sistema', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'roles', 'clave' => 'empresa', 'valor' => 'Empresa', 'descripcion' => 'Cuenta de empresa cliente', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'roles', 'clave' => 'candidato', 'valor' => 'Candidato', 'descripcion' => 'Cuenta de candidato', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'roles', 'clave' => 'interno', 'valor' => 'Interno', 'descripcion' => 'Usuario operativo interno', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'empresa_estados', 'clave' => 'pendiente', 'valor' => 'Pendiente', 'descripcion' => 'Empresa esperando revisión', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'empresa_estados', 'clave' => 'activa', 'valor' => 'Activa', 'descripcion' => 'Empresa aprobada', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'empresa_estados', 'clave' => 'rechazada', 'valor' => 'Rechazada', 'descripcion' => 'Empresa no aprobada', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'empresa_estados', 'clave' => 'suspendida', 'valor' => 'Suspendida', 'descripcion' => 'Empresa bloqueada temporalmente', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'candidato_estados', 'clave' => 'borrador', 'valor' => 'Borrador', 'descripcion' => 'Solicitud no enviada', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'candidato_estados', 'clave' => 'enviada', 'valor' => 'Enviada', 'descripcion' => 'Solicitud enviada por el candidato', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'candidato_estados', 'clave' => 'en_revision', 'valor' => 'En revisión', 'descripcion' => 'Solicitud en revisión por administración', 'activo' => true, 'orden' => 25, 'es_sistema' => true],
            ['grupo' => 'candidato_estados', 'clave' => 'aprobada', 'valor' => 'Aprobada', 'descripcion' => 'Solicitud aprobada', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'candidato_estados', 'clave' => 'rechazada', 'valor' => 'Rechazada', 'descripcion' => 'Solicitud rechazada', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'vacante_estados', 'clave' => 'pendiente', 'valor' => 'Pendiente', 'descripcion' => 'Solicitud por revisar', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'vacante_estados', 'clave' => 'activa', 'valor' => 'Activa', 'descripcion' => 'Solicitud activa', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'vacante_estados', 'clave' => 'cerrada', 'valor' => 'Cerrada', 'descripcion' => 'Solicitud cerrada', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'vacante_estados', 'clave' => 'rechazada', 'valor' => 'Rechazada', 'descripcion' => 'Solicitud rechazada', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'postulacion_estados', 'clave' => 'postulado', 'valor' => 'En revisión', 'descripcion' => 'Candidato en proceso inicial', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'postulacion_estados', 'clave' => 'entrevista', 'valor' => 'Ya entrevistado', 'descripcion' => 'Candidato ya fue entrevistado', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'postulacion_estados', 'clave' => 'seleccionado', 'valor' => 'Seleccionado', 'descripcion' => 'Candidato seleccionado', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'postulacion_estados', 'clave' => 'rechazado', 'valor' => 'Rechazado', 'descripcion' => 'Candidato rechazado', 'activo' => true, 'orden' => 40, 'es_sistema' => true],
            ['grupo' => 'postulacion_estados', 'clave' => 'retirado', 'valor' => 'Retirado', 'descripcion' => 'Candidato retiró su proceso', 'activo' => true, 'orden' => 50, 'es_sistema' => true],

            ['grupo' => 'servicio_asignado_estados', 'clave' => 'activo', 'valor' => 'Activo', 'descripcion' => 'Tarea creada y pendiente de toma', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'servicio_asignado_estados', 'clave' => 'en_proceso', 'valor' => 'En proceso', 'descripcion' => 'Tarea en ejecución', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'servicio_asignado_estados', 'clave' => 'completado', 'valor' => 'Completado', 'descripcion' => 'Tarea resuelta', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'servicio_asignado_estados', 'clave' => 'cancelado', 'valor' => 'Cancelado', 'descripcion' => 'Tarea cancelada', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'tipos_servicio', 'clave' => 'reclutamiento', 'valor' => 'Reclutamiento de personal', 'descripcion' => 'Servicios de reclutamiento', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'capacitacion', 'valor' => 'Capacitación', 'descripcion' => 'Servicios de capacitación', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'coaching', 'valor' => 'Coaching ejecutivo', 'descripcion' => 'Servicios de coaching', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'evaluacion', 'valor' => 'Evaluación / Assessment', 'descripcion' => 'Servicios de evaluación', 'activo' => true, 'orden' => 40, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'outplacement', 'valor' => 'Outplacement', 'descripcion' => 'Servicios de outplacement', 'activo' => true, 'orden' => 50, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'nomina', 'valor' => 'Nómina y IMSS', 'descripcion' => 'Servicios de nómina', 'activo' => true, 'orden' => 60, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'consultoria', 'valor' => 'Consultoría RH', 'descripcion' => 'Servicios de consultoría', 'activo' => true, 'orden' => 70, 'es_sistema' => true],
            ['grupo' => 'tipos_servicio', 'clave' => 'otro', 'valor' => 'Otro', 'descripcion' => 'Otro tipo de servicio', 'activo' => true, 'orden' => 80, 'es_sistema' => true],

            ['grupo' => 'niveles_estudios', 'clave' => 'sin_estudios', 'valor' => 'Sin estudios', 'descripcion' => 'No requiere estudios formales', 'activo' => true, 'orden' => 5, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'primaria', 'valor' => 'Primaria', 'descripcion' => 'Nivel primaria', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'secundaria', 'valor' => 'Secundaria', 'descripcion' => 'Nivel secundaria', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'preparatoria', 'valor' => 'Preparatoria / Bachillerato', 'descripcion' => 'Nivel preparatoria o bachillerato', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'tecnico', 'valor' => 'Técnico', 'descripcion' => 'Carrera técnica o técnico superior', 'activo' => true, 'orden' => 40, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'licenciatura', 'valor' => 'Licenciatura', 'descripcion' => 'Licenciatura', 'activo' => true, 'orden' => 50, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'ingenieria', 'valor' => 'Ingeniería', 'descripcion' => 'Ingeniería', 'activo' => true, 'orden' => 60, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'maestria', 'valor' => 'Maestría', 'descripcion' => 'Posgrado de maestría', 'activo' => true, 'orden' => 70, 'es_sistema' => true],
            ['grupo' => 'niveles_estudios', 'clave' => 'doctorado', 'valor' => 'Doctorado', 'descripcion' => 'Posgrado de doctorado', 'activo' => true, 'orden' => 80, 'es_sistema' => true],

            ['grupo' => 'niveles_jerarquicos', 'clave' => 'todos', 'valor' => 'Todos los niveles', 'descripcion' => 'Aplica para cualquier nivel', 'activo' => true, 'orden' => 5, 'es_sistema' => true],
            ['grupo' => 'niveles_jerarquicos', 'clave' => 'operativo', 'valor' => 'Operativo', 'descripcion' => 'Nivel operativo', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'niveles_jerarquicos', 'clave' => 'supervision', 'valor' => 'Supervisión', 'descripcion' => 'Nivel de supervisión', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'niveles_jerarquicos', 'clave' => 'gerencia', 'valor' => 'Gerencia', 'descripcion' => 'Nivel gerencial', 'activo' => true, 'orden' => 30, 'es_sistema' => true],
            ['grupo' => 'niveles_jerarquicos', 'clave' => 'direccion', 'valor' => 'Dirección', 'descripcion' => 'Nivel directivo', 'activo' => true, 'orden' => 40, 'es_sistema' => true],

            ['grupo' => 'chat_room_tipos', 'clave' => 'directo', 'valor' => 'Chat directo', 'descripcion' => 'Conversación directa', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'chat_room_tipos', 'clave' => 'grupal', 'valor' => 'Grupo', 'descripcion' => 'Conversación grupal', 'activo' => true, 'orden' => 20, 'es_sistema' => true],

            ['grupo' => 'disponibilidad_externa', 'clave' => 'disponible', 'valor' => 'Disponible', 'descripcion' => 'Disponible para trabajar', 'activo' => true, 'orden' => 10, 'es_sistema' => true],
            ['grupo' => 'disponibilidad_externa', 'clave' => 'ocupado', 'valor' => 'Ocupado', 'descripcion' => 'Tiene agenda ocupada', 'activo' => true, 'orden' => 20, 'es_sistema' => true],
            ['grupo' => 'disponibilidad_externa', 'clave' => 'inactivo', 'valor' => 'Inactivo', 'descripcion' => 'No disponible', 'activo' => true, 'orden' => 30, 'es_sistema' => true],

        ];

        return array_merge($base, self::defaultsNegocio());
    }

    /**
     * Opciones de negocio editables por el admin.
     * Separado para mantener orden y consistencia de columnas (descripcion siempre presente).
     */
    private static function defaultsNegocio(): array
    {
        $filas = [];
        $cargar = function (string $grupo, array $items) use (&$filas) {
            foreach ($items as $orden => [$clave, $valor]) {
                $filas[] = [
                    'grupo'       => $grupo,
                    'clave'       => $clave,
                    'valor'       => $valor,
                    'descripcion' => null,
                    'activo'      => true,
                    'orden'       => ($orden + 1) * 10,
                    'es_sistema'  => false,
                ];
            }
        };

        $cargar('areas_carreras', [
            ['administracion', 'Administración'],
            ['contabilidad', 'Contabilidad'],
            ['finanzas', 'Finanzas'],
            ['recursos_humanos', 'Recursos Humanos'],
            ['sistemas', 'Sistemas / TI'],
            ['marketing', 'Marketing'],
            ['ventas', 'Ventas'],
            ['ingenieria_carrera', 'Ingeniería'],
            ['produccion', 'Producción / Operaciones'],
            ['logistica_carrera', 'Logística'],
            ['legal', 'Legal'],
            ['salud_carrera', 'Salud / Medicina'],
            ['educacion_carrera', 'Educación'],
            ['otra', 'Otra'],
        ]);

        $cargar('tipos_contrato', [
            ['indefinido', 'Indefinido (planta)'],
            ['temporal', 'Temporal'],
            ['por_proyecto', 'Por proyecto'],
            ['por_horas', 'Por horas'],
            ['practicante', 'Prácticas / Becario'],
            ['freelance', 'Freelance'],
        ]);

        $cargar('sectores_empresa', [
            ['manufactura', 'Manufactura'],
            ['tecnologia', 'Tecnología'],
            ['servicios', 'Servicios profesionales'],
            ['comercio', 'Comercio / Retail'],
            ['salud_sector', 'Salud'],
            ['educacion_sector', 'Educación'],
            ['finanzas_sector', 'Banca y finanzas'],
            ['construccion', 'Construcción'],
            ['logistica_sector', 'Logística / Transporte'],
            ['agricultura', 'Agroindustria'],
            ['gobierno', 'Gobierno'],
            ['otro_sector', 'Otro'],
        ]);

        return $filas;
    }

    public static function seedDefaults(): void
    {
        if (! static::tablaDisponible()) {
            return;
        }

        static::upsert(
            self::defaults(),
            ['grupo', 'clave'],
            ['valor', 'descripcion', 'activo', 'orden', 'es_sistema']
        );
    }
}
