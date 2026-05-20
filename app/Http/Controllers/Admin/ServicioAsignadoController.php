<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\User;
use App\Services\AsignacionInternoService;
use App\Services\ExportadorService;
use App\Services\ServicioAsignadoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioAsignadoController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicioAsignado::with(['servicio', 'asignable', 'asignadoA', 'asignadoPor', 'solicitadoPor']);

        $this->aplicarFiltros($query, $request);

        // Ordenamiento dinámico
        $sort = $request->input('sort', 'estado_fecha');
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $this->aplicarOrden($query, $sort, $dir);

        $tareas = $query->paginate(15)->withQueryString();

        $stats = $this->estadisticasGenerales();
        $serviciosCatalogo = CatalogoServicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        $internosLista     = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get(['id', 'name']);

        return view('admin.servicios-asignados.index', compact(
            'tareas', 'stats', 'serviciosCatalogo', 'internosLista', 'sort', 'dir'
        ));
    }

    /**
     * Aplica todos los filtros del listado (estado, servicio, solicitante,
     * interno asignado, urgencia, búsqueda).
     */
    private function aplicarFiltros($query, Request $request): void
    {
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('servicio_id')) {
            $query->where('servicio_id', (int) $request->servicio_id);
        }

        if ($request->filled('solicitante_tipo')) {
            $tipo = match ($request->solicitante_tipo) {
                'empresa'   => Empresa::class,
                'candidato' => Candidato::class,
                'interno'   => User::class,
                default     => null,
            };
            if ($tipo) {
                $query->where('asignable_type', $tipo);
            }
        }

        if ($request->filled('interno_id')) {
            $query->where('asignado_a', (int) $request->interno_id);
        } elseif ($request->input('interno_id') === 'sin') {
            $query->whereNull('asignado_a');
        }

        // Urgencia: pedidos pendientes/activos sin avanzar en N días
        if ($request->filled('urgencia')) {
            $dias = (int) $request->urgencia;
            $query->whereIn('estado', ['pendiente', 'activo'])
                  ->where('created_at', '<=', now()->subDays($dias));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('servicio',   fn ($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                  ->orWhereHas('asignadoA', fn ($u) => $u->where('name', 'like', "%{$buscar}%"))
                  ->orWhereHas('asignable', fn ($a) => $a->where('name', 'like', "%{$buscar}%")
                      ->orWhere('nombre_empresa', 'like', "%{$buscar}%")
                      ->orWhere('nombre', 'like', "%{$buscar}%")
                      ->orWhere('apellidos', 'like', "%{$buscar}%"));
            });
        }
    }

    private function aplicarOrden($query, string $sort, string $dir): void
    {
        match ($sort) {
            'servicio' => $query->orderBy(
                CatalogoServicio::select('nombre')->whereColumn('catalogo_servicios.id', 'servicios_asignados.servicio_id'),
                $dir
            ),
            'estado'   => $query->orderBy('estado', $dir),
            'fecha'    => $query->orderBy('created_at', $dir),
            'dias'     => $query->orderBy('created_at', $dir === 'asc' ? 'desc' : 'asc'),
            default    => $query
                ->orderByRaw("CASE estado WHEN 'pendiente' THEN 1 WHEN 'activo' THEN 2 WHEN 'en_proceso' THEN 3 WHEN 'completado' THEN 4 ELSE 5 END")
                ->orderByDesc('created_at'),
        };
    }

    private function estadisticasGenerales(): array
    {
        return [
            'pendientes'  => ServicioAsignado::where('estado', 'pendiente')->count(),
            'activas'     => ServicioAsignado::where('estado', 'activo')->count(),
            'en_proceso'  => ServicioAsignado::where('estado', 'en_proceso')->count(),
            'completadas' => ServicioAsignado::where('estado', 'completado')->count(),
            'canceladas'  => ServicioAsignado::where('estado', 'cancelado')->count(),
        ];
    }

    public function create()
    {
        $servicios = CatalogoServicio::where('activo', true)->orderBy('orden')->orderBy('nombre')->get();
        $empresas = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $candidatos = Candidato::where('solicitud_estado', 'aprobada')
            ->with('usuario')
            ->orderBy('nombre')
            ->get();
        $internosObjetivo = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();
        $internos = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();

        return view('admin.servicios-asignados.form', compact(
            'servicios', 'empresas', 'candidatos', 'internos', 'internosObjetivo'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where(fn ($q) => $q->where('activo', true))],
            'objetivo'    => ['required', 'string', 'regex:/^(empresa|candidato|interno):\d+$/'],
            'asignado_a'  => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'interno')->where('estado', 'activo'))],
            'notas'       => ['nullable', 'string', 'max:5000'],
        ]);

        [$tipoObjetivo, $objetivoId] = explode(':', $data['objetivo'], 2);

        [$asignableType, $asignable] = $this->resolverObjetivo($tipoObjetivo, (int) $objetivoId);

        $estado = $data['asignado_a'] ? 'activo' : 'pendiente';

        ServicioAsignado::create([
            'servicio_id'     => $data['servicio_id'],
            'asignable_type'  => $asignableType,
            'asignable_id'    => $asignable->id,
            'asignado_a'      => $data['asignado_a'] ?? null,
            'estado'          => $estado,
            'notas'           => $data['notas'] ?? null,
            'asignado_por'    => auth()->id(),
            'solicitado_por'  => auth()->id(),
            'fecha_inicio'    => null,
            'fecha_fin'       => null,
        ]);

        $msg = $estado === 'pendiente'
            ? 'Servicio registrado como pendiente. Asigna un responsable desde el tablero.'
            : 'Servicio asignado correctamente.';

        return redirect()->route('admin.tareas.index')->with('success', $msg);
    }

    public function show(ServicioAsignado $tarea)
    {
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor', 'solicitadoPor']);

        return view('admin.servicios-asignados.show', compact('tarea'));
    }

    public function accionModal(ServicioAsignado $tarea, string $accion)
    {
        $config = match ($accion) {
            'cancelar' => [
                'titulo' => 'Cancelar pedido',
                'descripcion' => 'El pedido se marcara como cancelado y dejara de avanzar.',
                'mensaje' => 'Confirma si deseas cancelar este pedido de servicio.',
                'ruta' => route('admin.tareas.estado', $tarea),
                'metodo' => 'PATCH',
                'boton' => 'Cancelar pedido',
                'clase' => 'btn-danger',
                'campos' => ['estado' => 'cancelado'],
            ],
            'reabrir' => [
                'titulo' => 'Reabrir pedido',
                'descripcion' => 'El pedido volvera a pendiente para retomarlo o reasignarlo.',
                'mensaje' => 'Confirma si deseas reabrir este pedido.',
                'ruta' => route('admin.tareas.estado', $tarea),
                'metodo' => 'PATCH',
                'boton' => 'Reabrir pedido',
                'clase' => 'btn-secondary',
                'campos' => ['estado' => 'pendiente'],
            ],
            'liberar' => [
                'titulo' => 'Liberar interno',
                'descripcion' => 'Se quitara el responsable actual y el pedido volvera a pendiente.',
                'mensaje' => 'Confirma si deseas liberar al interno actual para reasignar este pedido.',
                'ruta' => route('admin.tareas.liberar', $tarea),
                'metodo' => 'POST',
                'boton' => 'Liberar interno',
                'clase' => 'btn-secondary',
            ],
            'eliminar' => [
                'titulo' => 'Eliminar pedido',
                'descripcion' => 'Esta accion borra el pedido de forma permanente.',
                'mensaje' => 'Confirma si deseas eliminar este pedido. Esta accion no se puede deshacer.',
                'ruta' => route('admin.tareas.eliminar', $tarea),
                'metodo' => 'DELETE',
                'boton' => 'Eliminar pedido',
                'clase' => 'btn-danger',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $registro = [
            'titulo' => $tarea->servicio?->nombre ?? 'Pedido de servicio',
            'detalle' => $tarea->asignableNombre() . ' · Estado actual: ' . ServicioAsignado::estadoLabel($tarea->estado),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function edit(ServicioAsignado $tarea)
    {
        $tarea->load(['servicio', 'asignable', 'asignadoA']);

        $servicios = CatalogoServicio::where('activo', true)->orderBy('orden')->orderBy('nombre')->get();
        $empresas = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $candidatos = Candidato::where('solicitud_estado', 'aprobada')
            ->with('usuario')
            ->orderBy('nombre')
            ->get();
        $internosObjetivo = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();
        $internos = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();

        $objetivoActual = match ($tarea->asignable_type) {
            Empresa::class   => 'empresa:' . $tarea->asignable_id,
            Candidato::class => 'candidato:' . $tarea->asignable_id,
            User::class      => 'interno:' . $tarea->asignable_id,
            default          => null,
        };

        return view('admin.servicios-asignados.form', compact(
            'tarea', 'servicios', 'empresas', 'candidatos', 'internos', 'internosObjetivo', 'objetivoActual'
        ));
    }

    public function update(Request $request, ServicioAsignado $tarea)
    {
        $data = $request->validate([
            'servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where(fn ($q) => $q->where('activo', true))],
            'objetivo'    => ['required', 'string', 'regex:/^(empresa|candidato|interno):\d+$/'],
            'asignado_a'  => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'interno')->where('estado', 'activo'))],
            'estado'      => ['required', 'in:' . implode(',', array_keys(ServicioAsignado::estados()))],
            'notas'       => ['nullable', 'string', 'max:5000'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin'    => ['nullable', 'date'],
            'cierre_resumen' => ['nullable', 'string', 'max:5000'],
        ]);

        [$tipoObjetivo, $objetivoId] = explode(':', $data['objetivo'], 2);
        [$asignableType, $asignable] = $this->resolverObjetivo($tipoObjetivo, (int) $objetivoId);

        $tarea->update([
            'servicio_id'    => $data['servicio_id'],
            'asignable_type' => $asignableType,
            'asignable_id'   => $asignable->id,
            'asignado_a'     => $data['asignado_a'] ?? null,
            'estado'         => $data['estado'],
            'notas'          => $data['notas'] ?? null,
            'fecha_inicio'   => $data['fecha_inicio'] ?? null,
            'fecha_fin'      => $data['fecha_fin'] ?? null,
            'cierre_resumen' => $data['cierre_resumen'] ?? null,
        ]);

        return redirect()->route('admin.tareas.show', $tarea)->with('success', 'Tarea actualizada correctamente.');
    }

    public function destroy(ServicioAsignado $tarea)
    {
        $tarea->delete();

        return redirect()->route('admin.tareas.index')->with('success', 'Tarea eliminada permanentemente.');
    }

    /**
     * Cambia rápidamente el estado de una tarea desde el listado.
     */
    public function cambiarEstado(Request $request, ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $data = $request->validate([
            'estado' => ['required', 'in:' . implode(',', ServicioAsignadoService::ESTADOS)],
        ]);

        $nuevoEstado = $data['estado'];

        if (in_array($nuevoEstado, ['activo', 'en_proceso'], true) && ! $tarea->asignado_a) {
            return back()->with('error', 'Asigna un responsable antes de mover a este estado.');
        }

        try {
            $servicio->cambiarEstado($tarea, $nuevoEstado);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Asigna un interno (con o sin excepción) a un pedido de servicio.
     */
    public function asignar(Request $request, ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $data = $request->validate([
            'asignado_a'        => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'interno')->where('estado', 'activo'))],
            'forzar'            => ['nullable', 'boolean'],
            'motivo_asignacion' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($tarea->puedeAsignar(), 403, 'Este servicio no puede asignarse en su estado actual.');

        try {
            $interno = User::findOrFail($data['asignado_a']);
            $servicio->asignarInterno(
                $tarea,
                $interno,
                $request->boolean('forzar'),
                $data['motivo_asignacion'] ?? null
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.tareas.matching', $tarea)
            ->with('success', "Asignado a {$interno->name}.");
    }

    /**
     * Libera al interno asignado para permitir reasignación.
     */
    public function liberarInterno(Request $request, ServicioAsignado $tarea, ServicioAsignadoService $servicio)
    {
        $data = $request->validate([
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $servicio->liberarInterno($tarea, $data['motivo'] ?? null);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Interno liberado. Ya puedes asignar a otro.');
    }

    /**
     * Descarga listado de pedidos como CSV (respeta filtros activos).
     */
    public function exportarCsv(Request $request, ExportadorService $exportador)
    {
        $query = ServicioAsignado::with(['servicio', 'asignable', 'asignadoA']);
        $this->aplicarFiltros($query, $request);

        $tareas = $query->latest()->get();

        $filas = $tareas->map(fn ($t) => [
            $t->servicio?->nombre ?? 'Servicio',
            $this->etiquetaSolicitante($t),
            $t->asignableNombre(),
            $t->asignadoA?->name ?? 'Sin asignar',
            ServicioAsignado::estadoLabel($t->estado),
            \App\Models\CatalogoServicio::nivelJerarquicoLabel($t->nivel_jerarquico),
            (int) $t->horas_estimadas,
            $t->created_at?->format('d/m/Y') ?? '',
            $t->fecha_inicio?->format('d/m/Y') ?? '',
            $t->fecha_fin?->format('d/m/Y') ?? '',
        ]);

        return $exportador->csv('pedidos_servicio', [
            'Servicio', 'Tipo solicitante', 'Solicitante', 'Responsable',
            'Estado', 'Nivel', 'Horas estimadas',
            'Creado', 'Iniciado', 'Finalizado',
        ], $filas);
    }

    /**
     * Vista imprimible para descargar como PDF desde el navegador.
     */
    public function exportarPdf(Request $request)
    {
        $query = ServicioAsignado::with(['servicio', 'asignable', 'asignadoA']);
        $this->aplicarFiltros($query, $request);

        $tareas = $query->latest()->get();

        return view('admin.servicios-asignados.imprimible', compact('tareas'));
    }

    private function etiquetaSolicitante(ServicioAsignado $tarea): string
    {
        return match ($tarea->asignable_type) {
            \App\Models\Empresa::class   => 'Empresa',
            \App\Models\Candidato::class => 'Candidato',
            \App\Models\User::class      => 'Interno',
            default                       => '—',
        };
    }

    /**
     * Vista "matching": clasifica internos por compatibilidad con el servicio.
     */
    public function matching(ServicioAsignado $tarea, AsignacionInternoService $matcher)
    {
        $tarea->load(['servicio', 'asignable', 'asignadoA']);

        $grupos = $matcher->clasificarInternos($tarea);

        $nivelSolicitado = $tarea->nivel_jerarquico ?? $tarea->servicio?->nivel_jerarquico;

        $requisitos = [
            'servicio'      => $tarea->servicio?->nombre ?? 'Sin servicio',
            'nivel'         => $nivelSolicitado
                ? CatalogoServicio::nivelJerarquicoLabel($nivelSolicitado)
                : 'Sin nivel',
            'solicitante'   => $tarea->asignableNombre(),
            'estado_actual' => ServicioAsignado::estadoLabel($tarea->estado),
        ];

        return view('admin.servicios-asignados.matching', compact('tarea', 'grupos', 'requisitos'));
    }

    /**
     * Devuelve los internos capacitados para un servicio específico (JSON).
     */
    public function internosCapacitados(Request $request)
    {
        $request->validate(['servicio_id' => 'required|integer|exists:catalogo_servicios,id']);

        $internos = User::where('rol', 'interno')
            ->where('estado', 'activo')
            ->whereHas('serviciosCapacitados', fn ($q) => $q->where('catalogo_servicios.id', $request->servicio_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($internos);
    }

    private function resolverObjetivo(string $tipo, int $id): array
    {
        return match ($tipo) {
            'empresa' => [Empresa::class, Empresa::findOrFail($id)],
            'candidato' => [Candidato::class, Candidato::findOrFail($id)],
            'interno' => [User::class, User::where('rol', 'interno')->where('estado', 'activo')->findOrFail($id)],
            default => throw new \InvalidArgumentException('Tipo de objetivo no válido.'),
        };
    }
}
