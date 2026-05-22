<?php

namespace App\Http\Controllers\Candidato;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\ServicioAsignado;
use App\Services\ServicioAsignadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Solicitudes de servicio del lado CANDIDATO.
 *
 * El candidato puede pedir cursos individuales, coaching personal,
 * evaluaciones, etc. También ve los servicios donde un admin/empresa
 * lo inscribió como destinatario.
 */
class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $candidato = $this->candidatoActual();

        $query = ServicioAsignado::with(['servicio', 'asignadoA', 'solicitadoPor'])
            ->where('asignable_type', \App\Models\Candidato::class)
            ->where('asignable_id', $candidato->id)
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $servicios = $query->paginate(15)->withQueryString();

        $stats = $this->estadisticas($candidato->id);

        return view('candidato.servicios.index', compact('servicios', 'stats'));
    }

    public function create()
    {
        $this->candidatoActual();
        $catalogo = CatalogoServicio::where('activo', true)->orderBy('nombre')->get();

        return view('candidato.servicios.create', compact('catalogo'));
    }

    public function store(Request $request, ServicioAsignadoService $servicio)
    {
        $candidato = $this->candidatoActual();

        $data = $request->validate([
            'servicio_id'     => ['required', 'integer', 'exists:catalogo_servicios,id'],
            'horas_estimadas' => ['nullable', 'integer', 'min:0', 'max:500'],
            'notas'           => ['required', 'string', 'max:2000'],
        ]);

        $servicio->registrar($data, $candidato);

        return redirect()
            ->route('candidato.servicios.index')
            ->with('success', 'Solicitud enviada. Un administrador la revisará y te asignará un responsable.');
    }

    public function show(ServicioAsignado $servicio)
    {
        $candidato = $this->candidatoActual();
        $this->autorizar($servicio, $candidato->id);

        $servicio->load(['servicio', 'asignadoA', 'solicitadoPor']);

        return view('partials.pedido-avance', [
            'servicio'    => $servicio,
            'rutaListado' => route('candidato.servicios.index'),
        ]);
    }

    public function destroy(ServicioAsignado $servicio)
    {
        $candidato = $this->candidatoActual();
        $this->autorizar($servicio, $candidato->id);

        abort_unless($servicio->estado === 'pendiente', 422, 'Solo puedes eliminar solicitudes que aún no han sido aprobadas.');

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
        abort_unless($candidato->solicitud_estado === 'aprobada', 403, 'Tu solicitud aún no ha sido aprobada.');
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

    private function estadisticas(int $candidatoId): array
    {
        $base = ServicioAsignado::where('asignable_type', \App\Models\Candidato::class)
            ->where('asignable_id', $candidatoId);

        return [
            'pendientes'  => (clone $base)->where('estado', 'pendiente')->count(),
            'activos'     => (clone $base)->where('estado', 'activo')->count(),
            'en_proceso'  => (clone $base)->where('estado', 'en_proceso')->count(),
            'completados' => (clone $base)->where('estado', 'completado')->count(),
        ];
    }
}
