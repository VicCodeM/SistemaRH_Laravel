<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\ServicioAsignado;
use App\Services\ServicioAsignadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Solicitudes de servicio del lado EMPRESA.
 *
 * La empresa puede pedir capacitaciones, coaching, mantenimiento, etc.
 * Internamente cada solicitud es un ServicioAsignado donde la empresa
 * es el `asignable` (destinatario del servicio).
 */
class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $empresa = $this->empresaActual();

        $query = ServicioAsignado::with(['servicio', 'asignadoA'])
            ->where('asignable_type', \App\Models\Empresa::class)
            ->where('asignable_id', $empresa->id)
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $servicios = $query->paginate(15)->withQueryString();

        $stats = $this->estadisticas($empresa->id);

        return view('empresa.servicios.index', compact('servicios', 'stats'));
    }

    public function create()
    {
        $this->empresaActual();
        $catalogo = CatalogoServicio::where('activo', true)->orderBy('nombre')->get();
        $niveles  = CatalogoServicio::nivelesJerarquicosFormulario();

        return view('empresa.servicios.create', compact('catalogo', 'niveles'));
    }

    public function store(Request $request, ServicioAsignadoService $servicio)
    {
        $empresa = $this->empresaActual();

        $nivelesValidos = array_keys(\App\Models\CatalogoServicio::nivelesJerarquicos());

        $data = $request->validate([
            'servicio_id'      => ['required', 'integer', 'exists:catalogo_servicios,id'],
            'nivel_jerarquico' => ['required', 'string', 'in:' . implode(',', $nivelesValidos)],
            'horas_estimadas'  => ['nullable', 'integer', 'min:0', 'max:500'],
            'notas'            => ['required', 'string', 'max:2000'],
        ]);

        $servicio->registrar($data, $empresa);

        return redirect()
            ->route('empresa.servicios.index')
            ->with('success', 'Solicitud de servicio enviada. Un administrador la revisará.');
    }

    public function show(ServicioAsignado $servicio)
    {
        $empresa = $this->empresaActual();
        $this->autorizar($servicio, $empresa->id);

        $servicio->load(['servicio', 'asignadoA', 'solicitadoPor']);

        return view('partials.pedido-avance', [
            'servicio'    => $servicio,
            'rutaListado' => route('empresa.servicios.index'),
        ]);
    }

    public function destroy(ServicioAsignado $servicio)
    {
        $empresa = $this->empresaActual();
        $this->autorizar($servicio, $empresa->id);

        abort_unless($servicio->estado === 'pendiente', 422, 'Solo puedes eliminar solicitudes que aún no han sido aprobadas.');

        $servicio->comentarios()->delete();
        $servicio->delete();

        return redirect()
            ->route('empresa.servicios.index')
            ->with('success', 'Solicitud eliminada.');
    }

    private function empresaActual(): \App\Models\Empresa
    {
        $empresa = Auth::user()?->empresa;
        abort_unless($empresa && $empresa->estado === 'activa', 403, 'Tu empresa no está activa.');
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

    private function estadisticas(int $empresaId): array
    {
        $base = ServicioAsignado::where('asignable_type', \App\Models\Empresa::class)
            ->where('asignable_id', $empresaId);

        return [
            'pendientes'  => (clone $base)->where('estado', 'pendiente')->count(),
            'activos'     => (clone $base)->where('estado', 'activo')->count(),
            'en_proceso'  => (clone $base)->where('estado', 'en_proceso')->count(),
            'completados' => (clone $base)->where('estado', 'completado')->count(),
        ];
    }
}
