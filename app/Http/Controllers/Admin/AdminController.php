<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'empresas_pendientes'   => Empresa::where('estado', 'pendiente')->count(),
            'empresas_activas'      => Empresa::where('estado', 'activa')->count(),
            'candidatos_pendientes' => Candidato::where('solicitud_estado', 'enviada')->count(),
            'candidatos_aprobados'  => Candidato::where('solicitud_estado', 'aprobada')->count(),
            'solicitudes_activas'   => Vacante::where('estado', 'activa')->count(),
            'solicitudes_pendientes'=> Vacante::where('estado', 'pendiente')->count(),
            'personal_disponible'   => \App\Models\PersonalExterno::where('disponibilidad', 'disponible')->count(),
        ];

        $empresas_pendientes = Empresa::where('estado', 'pendiente')
            ->with('usuario')->latest()->take(6)->get();

        $candidatos_pendientes = Candidato::where('solicitud_estado', 'enviada')
            ->with('usuario')->latest('solicitud_enviada_at')->take(6)->get();

        $solicitudes_recientes = Vacante::with('empresa')
            ->whereIn('estado', ['pendiente', 'activa'])
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'empresas_pendientes', 'candidatos_pendientes', 'solicitudes_recientes'
        ));
    }

    public function empresas(Request $request)
    {
        $query = Empresa::with('usuario')->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_empresa', 'like', "%{$buscar}%")
                  ->orWhere('rfc', 'like', "%{$buscar}%");
            });
        }

        $empresas = $query->paginate(15)->withQueryString();

        return view('admin.empresas.index', compact('empresas'));
    }

    public function aprobarEmpresa(Empresa $empresa)
    {
        $empresa->update(['estado' => 'activa']);
        return back()->with('success', "Empresa \"{$empresa->nombre_empresa}\" aprobada.");
    }

    public function rechazarEmpresa(Empresa $empresa)
    {
        $empresa->update(['estado' => 'rechazada']);
        return back()->with('error', "Empresa \"{$empresa->nombre_empresa}\" rechazada.");
    }

    public function suspenderEmpresa(Empresa $empresa)
    {
        $empresa->update(['estado' => 'suspendida']);
        return back()->with('success', "Empresa \"{$empresa->nombre_empresa}\" suspendida.");
    }

    public function showEmpresa(Empresa $empresa)
    {
        $empresa->load(['usuario', 'vacantes' => fn ($q) => $q->latest()->take(5)]);
        return view('admin.empresas.modal', compact('empresa'));
    }

    public function showCandidato(Candidato $candidato)
    {
        $candidato->load(['usuario', 'postulaciones.vacante.empresa']);
        return view('admin.candidatos.modal', compact('candidato'));
    }

    public function candidatos(Request $request)
    {
        $query = Candidato::with('usuario')->latest();

        if ($request->filled('estado')) {
            $query->where('solicitud_estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('curp', 'like', "%{$buscar}%");
            });
        }

        $candidatos = $query->paginate(15)->withQueryString();

        return view('admin.candidatos.index', compact('candidatos'));
    }

    public function aprobarCandidato(Candidato $candidato)
    {
        $candidato->update([
            'solicitud_estado'           => 'aprobada',
            'solicitud_revisada_at'      => now(),
            'solicitud_revision_admin_id'=> auth()->id(),
        ]);
        return back()->with('success', "Solicitud de {$candidato->nombre} aprobada.");
    }

    public function rechazarCandidato(Candidato $candidato)
    {
        $candidato->update([
            'solicitud_estado'           => 'rechazada',
            'solicitud_revisada_at'      => now(),
            'solicitud_revision_admin_id'=> auth()->id(),
        ]);
        return back()->with('error', "Solicitud de {$candidato->nombre} rechazada.");
    }

    public function vacantes(Request $request)
    {
        $query = Vacante::with('empresa')->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_servicio', $request->tipo);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', "%{$buscar}%"));
            });
        }

        $vacantes = $query->withCount([
            'postulaciones',
            'postulaciones as seleccionados_count'  => fn ($q) => $q->where('estado', 'seleccionado'),
            'postulaciones as entrevista_count'      => fn ($q) => $q->where('estado', 'entrevista'),
            'postulaciones as postulados_count'      => fn ($q) => $q->where('estado', 'postulado'),
        ])->paginate(15)->withQueryString();

        return view('admin.vacantes.index', compact('vacantes'));
    }

    public function activarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'activa']);
        return back()->with('success', "Vacante \"{$vacante->titulo}\" activada.");
    }

    public function cerrarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'cerrada']);
        return back()->with('success', "Vacante \"{$vacante->titulo}\" cerrada.");
    }

    public function crearVacante()
    {
        $empresas = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $niveles  = collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray();
        $tipos    = Vacante::tiposServicio();
        return view('admin.vacantes.create', compact('empresas', 'niveles', 'tipos'));
    }

    public function guardarVacante(Request $request)
    {
        $nivelesValidos = implode(',', array_keys(
            collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray()
        ));

        $data = $request->validate([
            'empresa_id'       => 'required|exists:empresas,id',
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'requerimientos'   => 'nullable|string|max:2000',
        ]);

        $data['estado']            = 'activa';
        $data['fecha_publicacion'] = now();

        Vacante::create($data);

        return redirect()->route('admin.vacantes')->with('success', 'Solicitud creada y activada.');
    }

    public function editarVacante(Vacante $vacante)
    {
        $vacante->load('empresa');
        $niveles = collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray();
        $tipos   = Vacante::tiposServicio();
        return view('admin.vacantes.edit', compact('vacante', 'niveles', 'tipos'));
    }

    public function actualizarVacante(Request $request, Vacante $vacante)
    {
        $nivelesValidos = implode(',', array_keys(
            collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray()
        ));

        $data = $request->validate([
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'descripcion'      => 'nullable|string|max:5000',
            'requerimientos'   => 'nullable|string|max:2000',
            'salario_min'      => 'nullable|numeric|min:0',
            'salario_max'      => 'nullable|numeric|min:0',
            'ubicacion'        => 'nullable|string|max:200',
        ]);

        $vacante->update($data);

        return redirect()->route('admin.vacantes')->with('success', "Solicitud \"{$vacante->titulo}\" actualizada.");
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion)
    {
        $request->validate([
            'estado' => 'required|in:postulado,entrevista,seleccionado,rechazado,retirado',
        ]);

        $postulacion->update(['estado' => $request->estado]);

        $msg = match($request->estado) {
            'entrevista'   => 'Candidato movido a entrevista.',
            'seleccionado' => 'Candidato seleccionado.',
            'rechazado'    => 'Candidato rechazado.',
            'retirado'     => 'Candidato retirado de la vacante.',
            default        => 'Estado actualizado.',
        };

        return back()->with('success', $msg);
    }

    public function matchingVacante(Vacante $vacante)
    {
        $vacante->load('empresa', 'postulaciones.candidato.usuario');

        // Solo excluir candidatos activos (postulado, entrevista, seleccionado) — retirados/rechazados pueden reasignarse
        $yaActivos = $vacante->postulaciones
            ->whereIn('estado', ['postulado', 'entrevista', 'seleccionado'])
            ->pluck('candidato_id')
            ->toArray();

        // Candidatos aprobados no asignados aún
        $palabras = collect(explode(' ', Str::lower($vacante->titulo)))
            ->filter(fn ($w) => strlen($w) > 3)
            ->values();

        $query = Candidato::where('solicitud_estado', 'aprobada')
            ->whereNotIn('id', $yaActivos)
            ->with('usuario');

        // Ordenar: primero los que tienen puesto_deseado compatible
        if ($palabras->isNotEmpty()) {
            $query->orderByRaw(
                'CASE WHEN ' .
                $palabras->map(fn ($p) => "LOWER(puesto_deseado) LIKE '%{$p}%'")->implode(' OR ') .
                ' THEN 0 ELSE 1 END'
            );
        }

        $candidatos = $query->get();
        $asignados  = $vacante->postulaciones;

        return view('admin.vacantes.matching', compact('vacante', 'candidatos', 'asignados'));
    }

    public function asignarCandidato(Request $request, Vacante $vacante)
    {
        $request->validate(['candidato_id' => ['required', 'exists:candidatos,id']]);

        $existe = Postulacion::where('vacante_id', $vacante->id)
            ->where('candidato_id', $request->candidato_id)
            ->exists();

        if (!$existe) {
            Postulacion::create([
                'vacante_id'        => $vacante->id,
                'candidato_id'      => $request->candidato_id,
                'estado'            => 'postulado',
                'fecha_postulacion' => now(),
            ]);
        }

        return back()->with('success', 'Candidato asignado a la vacante.');
    }
}
