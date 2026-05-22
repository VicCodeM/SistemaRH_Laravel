<?php

namespace App\Livewire;

use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\ServicioAsignado;
use App\Models\User;
use App\Services\AsignacionInternoService;
use App\Services\ServicioAsignadoService;
use Livewire\Component;

class ServiciosKanbanBoard extends Component
{
    public string $servicioId = '';
    public string $objetivoTipo = '';
    public string $objetivoId = '';
    public string $buscar = '';

    /** Máximo de tarjetas por columna. Lo demás va al link "Ver más". */
    private const LIMITE_POR_COLUMNA = 8;

    public function render()
    {
        $serviciosCatalogo = CatalogoServicio::where('activo', true)->orderBy('nombre')->get();
        $empresas = Empresa::with('usuario')->orderBy('nombre_empresa')->get();
        $candidatos = Candidato::with('usuario')->orderBy('nombre')->get();
        $internosObjetivo = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();

        [$columnas, $totales] = $this->columnasPorEstado();
        $sugerenciasPorSolicitud = $this->sugerenciasParaPendientes($columnas['pendientes']);
        $limite = self::LIMITE_POR_COLUMNA;

        return view('livewire.servicios-kanban-board', array_merge(
            compact('serviciosCatalogo', 'empresas', 'candidatos', 'internosObjetivo', 'sugerenciasPorSolicitud', 'totales', 'limite'),
            $columnas
        ));
    }

    /**
     * Top 3 internos sugeridos por cada pedido pendiente (los más libres y capacitados).
     *
     * @return array<int, \Illuminate\Support\Collection>  [solicitud_id => Collection<User>]
     */
    private function sugerenciasParaPendientes($pendientes): array
    {
        $matcher = app(AsignacionInternoService::class);
        $sugerencias = [];

        foreach ($pendientes as $solicitud) {
            if ($solicitud->asignado_a) {
                continue;
            }
            $sugerencias[$solicitud->id] = $matcher->candidatos($solicitud)->take(3);
        }

        return $sugerencias;
    }

    /**
     * Devuelve [columnas (limitadas), totales (sin limite)].
     * Orden:
     *  - pendientes y activos: ASC por fecha (los más antiguos primero = urgentes)
     *  - en_proceso / completados / cancelados: DESC por fecha
     */
    private function columnasPorEstado(): array
    {
        $base   = $this->queryBase();
        $limite = self::LIMITE_POR_COLUMNA;

        $estados = [
            'pendientes'  => ['estado' => 'pendiente',  'asc' => true],
            'activos'     => ['estado' => 'activo',     'asc' => true],
            'en_proceso'  => ['estado' => 'en_proceso', 'asc' => false],
            'completados' => ['estado' => 'completado', 'asc' => false],
            'cancelados'  => ['estado' => 'cancelado',  'asc' => false],
        ];

        $columnas = [];
        $totales  = [];

        foreach ($estados as $clave => $cfg) {
            $q = (clone $base)->where('estado', $cfg['estado']);
            $totales[$clave] = $q->count();

            $orden = $cfg['asc']
                ? $q->orderBy('created_at')   // más viejo primero (urgencia)
                : $q->orderByDesc('created_at');

            $columnas[$clave] = $orden->limit($limite)->get();
        }

        return [$columnas, $totales];
    }

    private function queryBase()
    {
        $query = ServicioAsignado::with(['servicio', 'asignable', 'asignadoA', 'asignadoPor', 'solicitadoPor']);

        if ($this->servicioId) {
            $query->where('servicio_id', (int) $this->servicioId);
        }

        if ($this->objetivoTipo) {
            $tipoClase = $this->resolverTipoClase($this->objetivoTipo);
            if ($tipoClase) {
                $query->where('asignable_type', $tipoClase);
                if ($this->objetivoId) {
                    $query->where('asignable_id', (int) $this->objetivoId);
                }
            }
        }

        if ($this->buscar) {
            $this->aplicarBusqueda($query, $this->buscar);
        }

        return $query;
    }

    private function resolverTipoClase(string $tipo): ?string
    {
        return match ($tipo) {
            'empresa'   => Empresa::class,
            'candidato' => Candidato::class,
            'interno'   => User::class,
            default     => null,
        };
    }

    private function aplicarBusqueda($query, string $texto): void
    {
        $query->where(function ($q) use ($texto) {
            $q->whereHas('servicio', fn ($s) => $s->where('nombre', 'like', "%{$texto}%"))
              ->orWhereHas('asignadoA', fn ($u) => $u->where('name', 'like', "%{$texto}%"))
              ->orWhereHas('asignable', fn ($a) => $a->where('name', 'like', "%{$texto}%")
                  ->orWhere('nombre_empresa', 'like', "%{$texto}%")
                  ->orWhere('nombre', 'like', "%{$texto}%")
                  ->orWhere('apellidos', 'like', "%{$texto}%"));
        });
    }

    public function puedeOperar(): bool
    {
        $user = auth()->user();
        return $user && ($user->esAdmin() || $user->esInterno());
    }

    public function iniciar(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;
        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;

        if (! $solicitud->asignado_a) {
            $this->notificar('Asigna un responsable antes de iniciar.', 'error');
            return;
        }

        app(ServicioAsignadoService::class)->cambiarEstado($solicitud, 'en_proceso');
    }

    public function activar(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;
        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;

        if (! $solicitud->asignado_a) {
            $this->notificar('Asigna un responsable antes de activar.', 'error');
            return;
        }

        app(ServicioAsignadoService::class)->cambiarEstado($solicitud, 'activo');
    }

    public function completar(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;
        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;
        app(ServicioAsignadoService::class)->cambiarEstado($solicitud, 'completado');
    }

    public function cancelar(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;
        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;
        app(ServicioAsignadoService::class)->cambiarEstado($solicitud, 'cancelado');
    }

    public function reabrir(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;
        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;
        app(ServicioAsignadoService::class)->cambiarEstado($solicitud, 'pendiente');
    }

    public function asignarInterno(int $solicitudId, int $internoId): void
    {
        if (! $this->puedeOperar()) return;

        $solicitud = ServicioAsignado::find($solicitudId);
        $interno = User::find($internoId);
        if (! $solicitud || ! $interno) return;

        try {
            app(ServicioAsignadoService::class)->asignarInterno($solicitud, $interno);
            $this->notificar("Asignado a {$interno->name}", 'success');
        } catch (\Throwable $e) {
            $this->notificar($e->getMessage(), 'error');
        }
    }

    public function asignarInteligente(int $solicitudId): void
    {
        if (! $this->puedeOperar()) return;

        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;

        $mejor = app(AsignacionInternoService::class)->sugerirMejor($solicitud);

        if (! $mejor) {
            $this->notificar('No hay internos disponibles y capacitados para este servicio.', 'warning');
            return;
        }

        app(ServicioAsignadoService::class)->asignarInterno($solicitud, $mejor);
        $this->notificar("Asignado automáticamente a {$mejor->name}", 'success');
    }

    public function cambiarObjetivo(int $solicitudId, string $tipo, int $id): void
    {
        if (! $this->puedeOperar()) return;

        $solicitud = ServicioAsignado::find($solicitudId);
        if (! $solicitud) return;

        $tipoClase = $this->resolverTipoClase($tipo);

        if (! $tipoClase) {
            $this->notificar('Tipo de objetivo no válido.', 'error');
            return;
        }

        $solicitud->update([
            'asignable_type' => $tipoClase,
            'asignable_id'   => $id,
        ]);
    }

    public function obtenerCapacitados(int $servicioId): array
    {
        return User::where('rol', 'interno')
            ->where('estado', 'activo')
            ->whereHas('serviciosCapacitados', fn ($q) => $q->where('catalogo_servicios.id', $servicioId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function notificar(string $mensaje, string $tipo = 'info'): void
    {
        $this->dispatch('notificacion', mensaje: $mensaje, tipo: $tipo);
    }
}
