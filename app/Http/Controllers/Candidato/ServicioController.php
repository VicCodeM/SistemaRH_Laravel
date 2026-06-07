<?php

namespace App\Http\Controllers\Candidato;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\ServicioAsignado;
use App\Services\ServicioAsignadoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $candidato = $this->candidatoActual();
        $buscar = trim((string) $request->input('buscar', ''));
        $tipo = (string) $request->input('tipo', '');

        $baseQuery = $this->catalogosVisiblesQuery();

        $catalogos = (clone $baseQuery)
            ->when($buscar !== '', function (Builder $query) use ($buscar) {
                $query->where(function (Builder $sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($tipo !== '', fn (Builder $query) => $query->where('tipo', $tipo))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $catalogosDisponibles = (clone $baseQuery)->orderBy('orden')->orderBy('nombre')->get();

        $tiposDisponibles = $catalogosDisponibles
            ->pluck('tipo')
            ->unique()
            ->values();

        $stats = [
            'disponibles' => $catalogosDisponibles->count(),
            'categorias' => $tiposDisponibles->count(),
            'solicitados' => ServicioAsignado::where('asignable_type', \App\Models\Candidato::class)
                ->where('asignable_id', $candidato->id)
                ->count(),
        ];

        return view('candidato.servicios.index', compact(
            'catalogos',
            'stats',
            'tiposDisponibles',
            'tipo'
        ));
    }

    public function create(Request $request)
    {
        $this->candidatoActual();
        $servicioId = $request->integer('servicio_id');

        if (! $servicioId) {
            return redirect()
                ->route('candidato.servicios.index')
                ->with('warning', 'Selecciona un servicio desde la lista para ver su detalle.');
        }

        $servicioSeleccionado = $this->catalogosVisiblesQuery(conRecursos: true)
            ->find($servicioId);

        if (! $servicioSeleccionado) {
            return redirect()
                ->route('candidato.servicios.index')
                ->with('error', 'Este servicio no esta disponible en este momento.');
        }

        return view('candidato.servicios.create', compact('servicioSeleccionado'));
    }

    public function store(Request $request, ServicioAsignadoService $servicio)
    {
        $candidato = $this->candidatoActual();

        $data = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:catalogo_servicios,id'],
            'horas_estimadas' => ['nullable', 'integer', 'min:0', 'max:500'],
            'notas' => ['required', 'string', 'max:2000'],
        ]);

        $catalogoServicio = $this->catalogosVisiblesQuery()->find($data['servicio_id']);

        if (! $catalogoServicio) {
            return redirect()
                ->route('candidato.servicios.index')
                ->with('error', 'Este servicio no se puede solicitar desde aqui.');
        }

        $servicio->registrar($data, $candidato);

        return redirect()
            ->route('candidato.servicios.index')
            ->with('success', 'Solicitud enviada. Un administrador la revisara y te asignara un responsable.');
    }

    public function show(ServicioAsignado $servicio)
    {
        $candidato = $this->candidatoActual();
        $this->autorizar($servicio, $candidato->id);

        $servicio->load(['servicio', 'asignadoA', 'solicitadoPor', 'recursos.subidoPor']);

        return view('partials.pedido-avance', [
            'servicio' => $servicio,
            'rutaListado' => route('candidato.servicios.index'),
        ]);
    }

    public function destroy(ServicioAsignado $servicio)
    {
        $candidato = $this->candidatoActual();
        $this->autorizar($servicio, $candidato->id);

        abort_unless($servicio->estado === 'pendiente', 422, 'Solo puedes eliminar solicitudes que aun no han sido aprobadas.');

        $servicio->comentarios()->delete();
        $servicio->delete();

        return redirect()
            ->route('candidato.servicios.index')
            ->with('success', 'Solicitud eliminada.');
    }

    private function candidatoActual(): \App\Models\Candidato
    {
        $candidato = Auth::user()?->candidato;
        abort_unless($candidato, 403, 'Debes completar tu solicitud primero.');
        abort_unless($candidato->solicitud_estado === 'aprobada', 403, 'Tu solicitud aun no ha sido aprobada.');

        return $candidato;
    }

    private function autorizar(ServicioAsignado $servicio, int $candidatoId): void
    {
        abort_unless(
            $servicio->asignable_type === \App\Models\Candidato::class && $servicio->asignable_id === $candidatoId,
            403,
            'Este servicio no te pertenece.'
        );
    }

    private function catalogosVisiblesQuery(bool $conRecursos = false): Builder
    {
        $query = CatalogoServicio::query()
            ->select('catalogo_servicios.*')
            ->distinct()
            ->visiblesParaRol('candidato');

        if ($conRecursos && CatalogoServicio::tieneTablaRecursos()) {
            $query->with('recursos.subidoPor');
        }

        return $query;
    }
}
