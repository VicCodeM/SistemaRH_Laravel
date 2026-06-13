<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Genera "acciones pendientes" para cada rol: lo que el usuario debería hacer al entrar.
 *
 * Cada acción tiene la misma estructura para que la vista la consuma de forma genérica.
 */
class ResumenRapidoService
{
    /**
     * Acciones pendientes para una empresa.
     */
    public function paraEmpresa(Empresa $empresa): Collection
    {
        $acciones = collect();

        // Postulaciones nuevas en sus vacantes
        $nuevasPostulaciones = Postulacion::whereHas('vacante', fn ($q) => $q->where('empresa_id', $empresa->id))
            ->where('estado', Postulacion::estadoInicial())
            ->count();

        if ($nuevasPostulaciones > 0) {
            $acciones->push($this->accion(
                icono: '👤',
                titulo: $this->plural($nuevasPostulaciones, 'candidato nuevo', 'candidatos nuevos'),
                mensaje: 'Hay candidatos nuevos esperando revisión.',
                href: route('empresa.solicitudes'),
                color: '#3b82f6',
            ));
        }

        // Servicios en proceso (avance que puede revisar)
        $serviciosActivos = ServicioAsignado::where('asignable_type', Empresa::class)
            ->where('asignable_id', $empresa->id)
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->count();

        if ($serviciosActivos > 0) {
            $acciones->push($this->accion(
                icono: '🔄',
                titulo: $this->plural($serviciosActivos, 'servicio en curso', 'servicios en curso'),
                mensaje: 'Revisa el avance de los servicios solicitados.',
                href: route('empresa.servicios.index'),
                color: '#a855f7',
            ));
        }

        // Si no tiene nada, sugerir crear algo
        if ($acciones->isEmpty()) {
            $acciones->push($this->accion(
                icono: '✨',
                titulo: 'Todo al día',
                mensaje: '¿Necesitas cubrir un puesto o un servicio? Solicítalo aquí.',
                href: route('empresa.solicitudes.crear'),
                color: '#10b981',
                etiquetaBoton: '+ Nueva vacante',
            ));
        }

        return $acciones;
    }

    /**
     * Acciones pendientes para un candidato.
     */
    public function paraCandidato(Candidato $candidato): Collection
    {
        $acciones = collect();

        // Si su solicitud aún no está aprobada
        if ($candidato->solicitud_estado !== 'aprobada') {
            $msg = match ($candidato->solicitud_estado) {
                'borrador'    => 'Termina de llenar tu solicitud para que el administrador la apruebe.',
                'enviada'     => 'Tu solicitud está en revisión. Te avisaremos cuando sea aprobada.',
                'rechazada'   => 'Tu solicitud fue rechazada. Corrige y vuelve a enviar.',
                default       => 'Completa tu solicitud para empezar.',
            };

            $acciones->push($this->accion(
                icono: '📝',
                titulo: 'Completa tu solicitud',
                mensaje: $msg,
                href: route('candidato.solicitud'),
                color: '#f59e0b',
                etiquetaBoton: 'Ir a mi solicitud',
            ));
            return $acciones;
        }

        // Postulaciones con cambio reciente
        $cambiosRecientes = $candidato->postulaciones()
            ->whereIn('estado', array_merge(Postulacion::estadosOcupanCupo(), ['rechazado']))
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        if ($cambiosRecientes > 0) {
            $acciones->push($this->accion(
                icono: '🎉',
                titulo: $this->plural($cambiosRecientes, 'novedad en tus postulaciones', 'novedades en tus postulaciones'),
                mensaje: 'Tienes actualizaciones recientes en las vacantes a las que aplicaste.',
                href: route('candidato.postulaciones'),
                color: '#10b981',
            ));
        }

        // Sugerir postular si no tiene postulaciones
        $totalPostulaciones = $candidato->postulaciones()->count();

        if ($totalPostulaciones === 0) {
            $acciones->push($this->accion(
                icono: '🔍',
                titulo: 'Aún no te has postulado',
                mensaje: 'Explora las vacantes disponibles y postúlate a las que te interesen.',
                href: route('candidato.vacantes'),
                color: '#3b82f6',
                etiquetaBoton: 'Ver vacantes',
            ));
        }

        // Si todo bien, motivar
        if ($acciones->isEmpty()) {
            $acciones->push($this->accion(
                icono: '✅',
                titulo: 'Tu perfil está activo',
                mensaje: 'Revisa nuevas vacantes y solicita servicios si necesitas capacitación.',
                href: route('candidato.vacantes'),
                color: '#10b981',
                etiquetaBoton: 'Ver vacantes',
            ));
        }

        return $acciones;
    }

    /**
     * Acciones pendientes para un interno.
     */
    public function paraInterno(User $interno): Collection
    {
        $acciones = collect();

        // Tareas activas sin iniciar
        $sinIniciar = $interno->serviciosAsignados()
            ->where('estado', 'activo')
            ->whereNull('fecha_inicio')
            ->count();

        if ($sinIniciar > 0) {
            $acciones->push($this->accion(
                icono: '🆕',
                titulo: $this->plural($sinIniciar, 'tarea nueva por iniciar', 'tareas nuevas por iniciar'),
                mensaje: 'Tienes pedidos asignados esperando que los tomes.',
                href: route('interno.tareas.index', ['estado' => 'activo']),
                color: '#ef4444',
            ));
        }

        // En proceso
        $enProceso = $interno->serviciosAsignados()->where('estado', 'en_proceso')->count();

        if ($enProceso > 0) {
            $acciones->push($this->accion(
                icono: '🔄',
                titulo: $this->plural($enProceso, 'tarea en proceso', 'tareas en proceso'),
                mensaje: 'Sigue trabajando o complétalas cuando termines.',
                href: route('interno.tareas.index', ['estado' => 'en_proceso']),
                color: '#f59e0b',
            ));
        }

        // Sin especialidades registradas (importante para recibir asignaciones)
        if ($interno->serviciosCapacitados()->count() === 0) {
            $acciones->push($this->accion(
                icono: '🎓',
                titulo: 'Aún no tienes especialidades',
                mensaje: 'Sin especialidades el sistema no puede asignarte servicios. Pide al admin que las configure.',
                href: route('interno.tareas.index'),
                color: '#ef4444',
                etiquetaBoton: 'Entendido',
            ));
        }

        if ($acciones->isEmpty()) {
            $acciones->push($this->accion(
                icono: '✅',
                titulo: 'Todo al día',
                mensaje: 'No tienes tareas pendientes. Cuando te asignen una aparecerá aquí.',
                href: route('interno.tareas.index'),
                color: '#10b981',
                etiquetaBoton: 'Ver mis tareas',
            ));
        }

        return $acciones;
    }

    private function accion(
        string $icono,
        string $titulo,
        string $mensaje,
        string $href,
        string $color = '#3b82f6',
        string $etiquetaBoton = 'Ir'
    ): array {
        return compact('icono', 'titulo', 'mensaje', 'href', 'color', 'etiquetaBoton');
    }

    private function plural(int $n, string $singular, string $plural): string
    {
        return $n . ' ' . ($n === 1 ? $singular : $plural);
    }
}
