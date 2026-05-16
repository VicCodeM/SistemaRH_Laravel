<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioAsignadoController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicioAsignado::with(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('nivel')) {
            $nivel = CatalogoServicio::normalizarNivelJerarquico($request->nivel);
            $query->whereHas('servicio', fn ($q) => $q->where('nivel_jerarquico', $nivel));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('servicio', fn ($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                  ->orWhereHas('asignadoA', fn ($u) => $u->where('name', 'like', "%{$buscar}%"))
                  ->orWhereHas('asignadoPor', fn ($u) => $u->where('name', 'like', "%{$buscar}%"));
            });
        }

        $tareas = $query
            ->orderByRaw("CASE estado WHEN 'activo' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'completado' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'activas'     => ServicioAsignado::where('estado', 'activo')->count(),
            'en_proceso'  => ServicioAsignado::where('estado', 'en_proceso')->count(),
            'completadas' => ServicioAsignado::where('estado', 'completado')->count(),
            'canceladas'  => ServicioAsignado::where('estado', 'cancelado')->count(),
        ];

        return view('admin.servicios-asignados.index', compact('tareas', 'stats'));
    }

    public function create()
    {
        $servicios = CatalogoServicio::where('activo', true)->orderBy('orden')->orderBy('nombre')->get();
        $empresas = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $candidatos = Candidato::where('solicitud_estado', 'aprobada')
            ->with('usuario')
            ->orderBy('nombre')
            ->get();
        $internos = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();

        return view('admin.servicios-asignados.form', compact('servicios', 'empresas', 'candidatos', 'internos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where(fn ($q) => $q->where('activo', true))],
            'objetivo'    => ['required', 'string', 'regex:/^(empresa|candidato):\d+$/'],
            'asignado_a'  => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'interno')->where('estado', 'activo'))],
            'notas'       => ['nullable', 'string', 'max:5000'],
        ]);

        [$tipoObjetivo, $objetivoId] = explode(':', $data['objetivo'], 2);

        if ($tipoObjetivo === 'empresa') {
            $asignable = Empresa::findOrFail((int) $objetivoId);
            $asignableType = Empresa::class;
        } else {
            $asignable = Candidato::findOrFail((int) $objetivoId);
            $asignableType = Candidato::class;
        }

        ServicioAsignado::create([
            'servicio_id'     => $data['servicio_id'],
            'asignable_type'  => $asignableType,
            'asignable_id'    => $asignable->id,
            'asignado_a'      => $data['asignado_a'],
            'estado'          => 'activo',
            'notas'           => $data['notas'] ?? null,
            'asignado_por'    => auth()->id(),
            'fecha_inicio'    => null,
            'fecha_fin'       => null,
        ]);

        return redirect()->route('admin.tareas.index')->with('success', 'Servicio asignado correctamente.');
    }

    public function show(ServicioAsignado $tarea)
    {
        $tarea->load(['servicio', 'asignable', 'asignadoA', 'asignadoPor']);

        return view('admin.servicios-asignados.show', compact('tarea'));
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
        $internos = User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get();

        $objetivoActual = null;
        if ($tarea->asignable_type === Empresa::class) {
            $objetivoActual = 'empresa:' . $tarea->asignable_id;
        } elseif ($tarea->asignable_type === Candidato::class) {
            $objetivoActual = 'candidato:' . $tarea->asignable_id;
        }

        return view('admin.servicios-asignados.form', compact('tarea', 'servicios', 'empresas', 'candidatos', 'internos', 'objetivoActual'));
    }

    public function update(Request $request, ServicioAsignado $tarea)
    {
        $data = $request->validate([
            'servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where(fn ($q) => $q->where('activo', true))],
            'objetivo'    => ['required', 'string', 'regex:/^(empresa|candidato):\d+$/'],
            'asignado_a'  => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'interno')->where('estado', 'activo'))],
            'estado'      => ['required', 'in:' . implode(',', array_keys(ServicioAsignado::estados()))],
            'notas'       => ['nullable', 'string', 'max:5000'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin'    => ['nullable', 'date'],
            'cierre_resumen' => ['nullable', 'string', 'max:5000'],
        ]);

        [$tipoObjetivo, $objetivoId] = explode(':', $data['objetivo'], 2);

        if ($tipoObjetivo === 'empresa') {
            $asignableType = Empresa::class;
            $asignableId = Empresa::findOrFail((int) $objetivoId)->id;
        } else {
            $asignableType = Candidato::class;
            $asignableId = Candidato::findOrFail((int) $objetivoId)->id;
        }

        $tarea->update([
            'servicio_id'    => $data['servicio_id'],
            'asignable_type' => $asignableType,
            'asignable_id'   => $asignableId,
            'asignado_a'     => $data['asignado_a'],
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
}
