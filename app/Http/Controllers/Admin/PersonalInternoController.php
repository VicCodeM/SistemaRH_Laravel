<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrearInternoRequest;
use App\Models\CatalogoServicio;
use App\Models\ServicioAsignado;
use App\Models\User;
use App\Services\ExportadorService;
use App\Services\PersonalInternoService;
use Illuminate\Http\Request;

class PersonalInternoController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('rol', 'interno')
            ->withCount([
                'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
                'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
            ]);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$buscar}%")
                ->orWhere('email', 'like', "%{$buscar}%"));
        }

        $internos = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'     => User::where('rol', 'interno')->count(),
            'activos'   => User::where('rol', 'interno')->where('estado', 'activo')->count(),
            'tareas_abiertas' => ServicioAsignado::whereIn('estado', ['activo', 'en_proceso'])
                ->whereHas('asignadoA', fn ($q) => $q->where('rol', 'interno'))
                ->count(),
        ];

        return view('admin.personal-interno.index', compact('internos', 'stats'));
    }

    public function create()
    {
        $servicios = CatalogoServicio::where('activo', true)->orderBy('nombre')->get();

        return view('admin.personal-interno.form', [
            'interno'   => new User(),
            'servicios' => $servicios,
        ]);
    }

    public function store(CrearInternoRequest $request, PersonalInternoService $servicio)
    {
        $interno = $servicio->crear(
            $request->only(['name', 'email', 'capacidad_maxima_horas', 'departamento', 'disponibilidad']),
            $request->input('servicios', [])
        );

        return redirect()
            ->route('admin.personal-interno.index')
            ->with('success', "Interno \"{$interno->name}\" creado con sus especialidades. Se envió enlace de acceso a {$interno->email}.");
    }

    public function modal(User $interno)
    {
        abort_unless($interno->rol === 'interno', 403);
        $interno->loadCount([
            'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
            'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
        ]);
        $tareas = $interno->serviciosAsignados()
            ->with('servicio')
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->latest()
            ->take(5)
            ->get();

        $servicios = CatalogoServicio::where('activo', true)->orderBy('nombre')->get();
        $serviciosCapacitados = $interno->serviciosCapacitados()->pluck('catalogo_servicios.id')->toArray();

        return view('admin.personal-interno.modal', compact('interno', 'tareas', 'servicios', 'serviciosCapacitados'));
    }

    public function accionModal(User $interno, string $accion)
    {
        abort_unless($interno->rol === 'interno', 403);

        $config = match ($accion) {
            'bloquear' => [
                'titulo' => 'Bloquear interno',
                'descripcion' => 'El interno perdera acceso, pero conservara su perfil y capacidades.',
                'mensaje' => 'Confirma si deseas bloquear este acceso.',
                'ruta' => route('admin.personal-interno.estado', $interno),
                'metodo' => 'PATCH',
                'boton' => 'Bloquear interno',
                'clase' => 'btn-danger',
            ],
            'activar' => [
                'titulo' => 'Activar interno',
                'descripcion' => 'El interno recuperara acceso y podra volver a operar servicios.',
                'mensaje' => 'Confirma si deseas activar este acceso.',
                'ruta' => route('admin.personal-interno.estado', $interno),
                'metodo' => 'PATCH',
                'boton' => 'Activar interno',
                'clase' => 'btn-success',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $registro = [
            'titulo' => $interno->name,
            'detalle' => $interno->email . ' · Estado actual: ' . ($interno->estado === 'activo' ? 'Activo' : 'Bloqueado'),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function actualizarCapacidades(Request $request, User $interno, PersonalInternoService $servicio)
    {
        abort_unless($interno->rol === 'interno', 403);

        $data = $request->validate([
            'servicios'   => ['nullable', 'array'],
            'servicios.*' => ['integer', 'exists:catalogo_servicios,id'],
        ]);

        $servicio->actualizarCapacidades($interno, $data['servicios'] ?? []);

        return redirect()->route('admin.personal-interno.index')->with('success', 'Capacidades actualizadas correctamente.');
    }

    public function toggleEstado(User $interno)
    {
        abort_unless($interno->rol === 'interno', 403);
        $nuevoEstado = $interno->estado === 'activo' ? 'bloqueado' : 'activo';
        $interno->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'Interno activado correctamente.' : 'Interno desactivado.';
        return redirect()->route('admin.personal-interno.index')->with('success', $msg);
    }

    public function exportarCsv(ExportadorService $exportador)
    {
        $internos = User::where('rol', 'interno')
            ->withCount([
                'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
                'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
            ])
            ->orderBy('name')
            ->get();

        $filas = $internos->map(fn ($i) => [
            $i->name,
            $i->email,
            $i->estado === 'activo' ? 'Activo' : 'Bloqueado',
            (int) $i->carga_trabajo_horas,
            (int) $i->capacidad_maxima_horas,
            (int) round($i->ocupacionPorcentaje()) . '%',
            $i->disponibilidad ?? '—',
            $i->departamento ?? '—',
            (int) $i->tareas_activas,
            (int) $i->tareas_completadas,
        ]);

        return $exportador->csv('personal_interno', [
            'Nombre', 'Correo', 'Estado',
            'Carga horas', 'Capacidad horas', 'Ocupación',
            'Disponibilidad', 'Departamento',
            'Tareas activas', 'Tareas completadas',
        ], $filas);
    }

    public function exportarPdf()
    {
        $internos = User::where('rol', 'interno')
            ->withCount([
                'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
                'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.personal-interno.imprimible', compact('internos'));
    }

    public function exportarFichaPdf(User $interno)
    {
        abort_unless($interno->rol === 'interno', 403);
        $interno->load('serviciosCapacitados');

        return view('admin.personal-interno.ficha-imprimible', compact('interno'));
    }
}
