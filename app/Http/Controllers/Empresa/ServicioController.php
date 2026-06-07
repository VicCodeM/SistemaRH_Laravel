<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\ServicioAsignado;
use App\Services\ServicioAsignadoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $empresa = $this->empresaActual();
        $buscar = trim((string) $request->input('buscar', ''));
        $tipo = (string) $request->input('tipo', '');
        $nivel = (string) $request->input('nivel', '');

        $baseQuery = $this->catalogosVisiblesQuery();

        $catalogos = (clone $baseQuery)
            ->when($buscar !== '', function (Builder $query) use ($buscar) {
                $query->where(function (Builder $sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($tipo !== '', fn (Builder $query) => $query->where('tipo', $tipo))
            ->when($nivel !== '', function (Builder $query) use ($nivel) {
                $query->where(function (Builder $sub) use ($nivel) {
                    $sub->where('nivel_jerarquico', $nivel)
                        ->orWhere('nivel_jerarquico', 'todos');
                });
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $catalogosDisponibles = (clone $baseQuery)->orderBy('orden')->orderBy('nombre')->get();

        $tiposDisponibles = $catalogosDisponibles
            ->pluck('tipo')
            ->unique()
            ->values();

        $nivelesDisponibles = $catalogosDisponibles
            ->filter(fn (CatalogoServicio $catalogo) => ! $catalogo->esFlujoVacante())
            ->pluck('nivel_jerarquico')
            ->map(fn (?string $nivel) => CatalogoServicio::normalizarNivelJerarquico($nivel))
            ->filter()
            ->unique()
            ->values();

        $stats = [
            'disponibles' => $catalogosDisponibles->count(),
            'categorias' => $tiposDisponibles->count(),
            'vacantes' => $catalogosDisponibles->where('flujo', 'vacante')->count(),
            'solicitados' => ServicioAsignado::where('asignable_type', \App\Models\Empresa::class)
                ->where('asignable_id', $empresa->id)
                ->count(),
        ];

        return view('empresa.servicios.index', compact(
            'catalogos',
            'stats',
            'tiposDisponibles',
            'nivelesDisponibles',
            'tipo',
            'nivel'
        ));
    }

    public function create(Request $request)
    {
        $this->empresaActual();
        $servicioId = $request->integer('servicio_id');

        if (! $servicioId) {
            return redirect()
                ->route('empresa.servicios.index')
                ->with('warning', 'Selecciona un servicio desde la lista para ver su detalle.');
        }

        $servicioSeleccionado = $this->catalogosVisiblesQuery(conRecursos: true)
            ->find($servicioId);

        if (! $servicioSeleccionado) {
            return redirect()
                ->route('empresa.servicios.index')
                ->with('error', 'Este servicio no esta disponible en este momento.');
        }

        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();

        return view('empresa.servicios.create', compact('servicioSeleccionado', 'niveles'));
    }

    public function store(Request $request, ServicioAsignadoService $servicio)
    {
        $empresa = $this->empresaActual();
        $nivelesValidos = array_keys(CatalogoServicio::nivelesJerarquicos());

        $data = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:catalogo_servicios,id'],
            'nivel_jerarquico' => ['required', 'string', 'in:' . implode(',', $nivelesValidos)],
            'horas_estimadas' => ['nullable', 'integer', 'min:0', 'max:500'],
            'notas' => ['required', 'string', 'max:2000'],
        ]);

        $catalogoServicio = $this->catalogosVisiblesQuery()->find($data['servicio_id']);

        if (! $catalogoServicio || $catalogoServicio->esFlujoVacante()) {
            return redirect()
                ->route('empresa.servicios.index')
                ->with('error', 'Este servicio no se puede solicitar desde aqui.');
        }

        $servicio->registrar($data, $empresa);

        return redirect()
            ->route('empresa.servicios.index')
            ->with('success', 'Solicitud de servicio enviada. Un administrador la revisara.');
    }

    public function show(ServicioAsignado $servicio)
    {
        $empresa = $this->empresaActual();
        $this->autorizar($servicio, $empresa->id);

        $servicio->load(['servicio', 'asignadoA', 'solicitadoPor', 'recursos.subidoPor']);

        return view('partials.pedido-avance', [
            'servicio' => $servicio,
            'rutaListado' => route('empresa.servicios.index'),
        ]);
    }

    public function destroy(ServicioAsignado $servicio)
    {
        $empresa = $this->empresaActual();
        $this->autorizar($servicio, $empresa->id);

        abort_unless($servicio->estado === 'pendiente', 422, 'Solo puedes eliminar solicitudes que aun no han sido aprobadas.');

        $servicio->comentarios()->delete();
        $servicio->delete();

        return redirect()
            ->route('empresa.servicios.index')
            ->with('success', 'Solicitud eliminada.');
    }

    private function empresaActual(): \App\Models\Empresa
    {
        $empresa = Auth::user()?->empresa;
        abort_unless($empresa && $empresa->estado === 'activa', 403, 'Tu empresa no esta activa.');

        return $empresa;
    }

    private function autorizar(ServicioAsignado $servicio, int $empresaId): void
    {
        abort_unless(
            $servicio->asignable_type === \App\Models\Empresa::class && $servicio->asignable_id === $empresaId,
            403,
            'Este servicio no te pertenece.'
        );
    }

    private function catalogosVisiblesQuery(bool $conRecursos = false): Builder
    {
        $query = CatalogoServicio::query()
            ->select('catalogo_servicios.*')
            ->distinct()
            ->visiblesParaRol('empresa');

        if ($conRecursos && CatalogoServicio::tieneTablaRecursos()) {
            $query->with('recursos.subidoPor');
        }

        return $query;
    }
}
