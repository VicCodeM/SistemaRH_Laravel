<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Support\Collection;

/**
 * Servicio para búsquedas transversales en el sistema (admin).
 * Devuelve resultados con URL directa al recurso.
 */
class BusquedaService
{
    /**
     * Busca en empresas, candidatos, internos, vacantes y servicios.
     *
     * @return Collection<int, array{tipo,titulo,sub,url,estado,icono,avatar}>
     */
    public function global(string $q): Collection
    {
        $like = "%{$q}%";

        return collect()
            ->merge($this->buscarModulos($q))
            ->merge($this->buscarEmpresas($like))
            ->merge($this->buscarCandidatos($like))
            ->merge($this->buscarInternos($like))
            ->merge($this->buscarVacantes($like))
            ->merge($this->buscarServicios($like));
    }

    /**
     * Busca entre las secciones/módulos del panel admin (Empresas, Candidatos, etc.).
     * Coincide por nombre o palabras clave, sin distinguir acentos ni mayúsculas.
     */
    private function buscarModulos(string $q): Collection
    {
        $termino = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($q));

        $modulos = [
            ['Panel', '📊', 'Resumen general del sistema', 'admin.dashboard', 'panel inicio dashboard tablero resumen'],
            ['Empresas', '🏢', 'Cuentas de empresas y aprobaciones', 'admin.empresas', 'empresas empresa compania clientes negocios'],
            ['Candidatos', '👤', 'Solicitudes y perfiles de candidatos', 'admin.candidatos', 'candidatos candidato postulantes aspirantes personas'],
            ['Vacantes', '💼', 'Reclutamiento y puestos abiertos', 'admin.vacantes', 'vacantes vacante reclutamiento puestos empleos plazas'],
            ['Pedidos de servicio', '✨', 'Capacitaciones y servicios asignados', 'admin.tareas.index', 'pedidos servicios servicio capacitacion coaching tareas asignaciones'],
            ['Personal interno', '👷', 'Equipo interno y su carga de trabajo', 'admin.personal-interno.index', 'personal interno internos equipo empleados staff'],
            ['Reportes', '📈', 'Indicadores y estadísticas', 'admin.reportes', 'reportes reporte analisis kpis estadisticas metricas'],
            ['Catálogos', '🏷️', 'Opciones y listas del sistema', 'admin.catalogos.index', 'catalogos catalogo opciones listas'],
            ['Configuración', '⚙️', 'Ajustes y usuarios del sistema', 'admin.configuracion', 'configuracion ajustes settings usuarios sistema parametros'],
            ['Chat', '💬', 'Mensajes y soporte', 'chat.index', 'chat mensajes conversaciones soporte'],
        ];

        return collect($modulos)
            ->filter(function ($m) use ($termino) {
                $busqueda = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($m[0] . ' ' . $m[4]));

                return str_contains($busqueda, $termino);
            })
            ->map(fn ($m) => [
                'tipo'   => 'Sección',
                'icono'  => $m[1],
                'titulo' => $m[0],
                'sub'    => $m[2],
                'url'    => route($m[3]),
                'estado' => 'Abrir',
                'avatar' => null,
            ])
            ->values();
    }

    /**
     * Convierte una ruta de avatar (relativa o absoluta) en URL completa.
     * Misma lógica que el componente <x-avatar>.
     */
    private function avatarUrl(?string $src): ?string
    {
        if (! $src) {
            return null;
        }

        return str_starts_with($src, 'http') || str_starts_with($src, '/')
            ? $src
            : asset('storage/' . $src);
    }

    private function buscarEmpresas(string $like): Collection
    {
        return Empresa::where('nombre_empresa', 'like', $like)
            ->orWhere('rfc', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(6)
            ->get()
            ->map(fn ($e) => [
                'tipo'   => 'Empresa',
                'icono'  => '🏢',
                'titulo' => $e->nombre_empresa,
                'sub'    => $e->usuario?->email ?? ($e->rfc ?? 'Sin RFC'),
                'url'    => route('admin.empresas.editar', $e),
                'estado' => Empresa::estadoLabel($e->estado),
                'avatar' => $this->avatarUrl($e->usuario?->avatar_url),
            ]);
    }

    private function buscarCandidatos(string $like): Collection
    {
        return Candidato::where('nombre', 'like', $like)
            ->orWhere('apellido_paterno', 'like', $like)
            ->orWhere('apellido_materno', 'like', $like)
            ->orWhere('curp', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(6)
            ->get()
            ->map(fn ($c) => [
                'tipo'   => 'Candidato',
                'icono'  => '👤',
                'titulo' => $c->nombreCompleto(),
                'sub'    => $c->puesto_deseado ?: ($c->usuario?->email ?? 'Sin puesto'),
                'url'    => route('admin.candidatos.solicitud', $c),
                'estado' => Candidato::solicitudEstadoLabel($c->solicitud_estado),
                'avatar' => $this->avatarUrl($c->usuario?->avatar_url),
            ]);
    }

    private function buscarInternos(string $like): Collection
    {
        return User::where('rol', 'interno')
            ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
            ->limit(6)
            ->get()
            ->map(fn ($i) => [
                'tipo'   => 'Interno',
                'icono'  => '👷',
                'titulo' => $i->name,
                'sub'    => $i->email,
                'url'    => route('admin.personal-interno.index', ['buscar' => $i->name]),
                'estado' => $i->estado === 'activo' ? 'Activo' : 'Bloqueado',
                'avatar' => $this->avatarUrl($i->avatar_url),
            ]);
    }

    private function buscarVacantes(string $like): Collection
    {
        return Vacante::where('titulo', 'like', $like)
            ->orWhere('descripcion', 'like', $like)
            ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', $like))
            ->with('empresa')
            ->limit(6)
            ->get()
            ->map(fn ($v) => [
                'tipo'   => 'Vacante',
                'icono'  => '💼',
                'titulo' => $v->titulo,
                'sub'    => $v->empresa?->nombre_empresa ?? 'Sin empresa',
                'url'    => route('admin.vacantes.matching', $v),
                'estado' => Vacante::estadoLabel($v->estado),
                'avatar' => null,
            ]);
    }

    private function buscarServicios(string $like): Collection
    {
        return ServicioAsignado::whereHas('servicio', fn ($s) => $s->where('nombre', 'like', $like))
            ->orWhereHas('asignadoA', fn ($u) => $u->where('name', 'like', $like))
            ->with(['servicio', 'asignable'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($s) => [
                'tipo'   => 'Pedido de servicio',
                'icono'  => '✨',
                'titulo' => $s->servicio?->nombre ?? 'Servicio',
                'sub'    => $s->asignableNombre(),
                'url'    => route('admin.tareas.show', $s),
                'estado' => ServicioAsignado::estadoLabel($s->estado),
                'avatar' => null,
            ]);
    }
}
