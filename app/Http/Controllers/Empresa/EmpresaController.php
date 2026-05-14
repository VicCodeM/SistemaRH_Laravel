<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function dashboard()
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return redirect()->route('empresa.perfil.crear');
        }

        $stats = [
            'solicitudes_activas'    => $empresa->vacantes()->where('estado', 'activa')->count(),
            'solicitudes_pendientes' => $empresa->vacantes()->where('estado', 'pendiente')->count(),
            'candidatos_total'       => Postulacion::whereIn('vacante_id', $empresa->vacantes()->pluck('id'))->count(),
            'en_proceso'             => Postulacion::whereIn('vacante_id', $empresa->vacantes()->pluck('id'))
                                            ->where('estado', 'entrevista')->count(),
        ];

        $solicitudes_recientes = $empresa->vacantes()
            ->withCount('postulaciones')
            ->latest()
            ->take(5)
            ->get();

        return view('empresa.dashboard', compact('empresa', 'stats', 'solicitudes_recientes'));
    }

    public function solicitudes()
    {
        $empresa    = Auth::user()->empresa;
        $solicitudes = $empresa->vacantes()->latest()->paginate(10);
        $tipos      = Vacante::tiposServicio();
        return view('empresa.solicitudes.index', compact('solicitudes', 'empresa', 'tipos'));
    }

    public function crearSolicitud()
    {
        $niveles = collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray();
        $tipos   = Vacante::tiposServicio();
        return view('empresa.solicitudes.create', compact('niveles', 'tipos'));
    }

    public function guardarSolicitud(Request $request, WorkflowService $workflow)
    {
        $nivelesValidos = implode(',', array_keys(
            collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray()
        ));

        $data = $request->validate([
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'requerimientos'   => 'nullable|string|max:2000',
        ]);

        $empresa = Auth::user()->empresa;
        $data['empresa_id']        = $empresa->id;
        $data['estado']            = 'pendiente';
        $data['fecha_publicacion'] = now();

        $solicitud = Vacante::create($data);
        $workflow->decideVacanteCreation($solicitud);

        return redirect()->route('empresa.solicitudes')
            ->with('success', 'Solicitud enviada. El administrador la revisará a la brevedad.');
    }

    public function verSolicitud(Vacante $vacante)
    {
        $this->autorizarVacante($vacante);
        $vacante->load('postulaciones.candidato.usuario');
        return view('empresa.solicitudes.show', compact('vacante'));
    }

    public function editarSolicitud(Vacante $vacante)
    {
        $this->autorizarVacante($vacante);
        abort_if($vacante->estado !== 'pendiente', 403, 'Solo puedes editar solicitudes pendientes.');
        $niveles = collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray();
        $tipos   = Vacante::tiposServicio();
        return view('empresa.solicitudes.edit', compact('vacante', 'niveles', 'tipos'));
    }

    public function actualizarSolicitud(Request $request, Vacante $vacante)
    {
        $this->autorizarVacante($vacante);
        abort_if($vacante->estado !== 'pendiente', 403);

        $nivelesValidos = implode(',', array_keys(
            collect(CatalogoServicio::nivelesJerarquicos())->except('todos')->toArray()
        ));

        $data = $request->validate([
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'requerimientos'   => 'nullable|string|max:2000',
        ]);

        $vacante->update($data);
        return redirect()->route('empresa.solicitudes')->with('success', 'Solicitud actualizada.');
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion)
    {
        $this->autorizarPostulacion($postulacion);

        $request->validate(['estado' => 'required|in:postulado,entrevista,seleccionado,rechazado']);
        $postulacion->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    private function autorizarVacante(Vacante $vacante): void
    {
        $empresa = Auth::user()->empresa;
        abort_if(!$empresa || $vacante->empresa_id !== $empresa->id, 403);
    }

    private function autorizarPostulacion(Postulacion $postulacion): void
    {
        $empresa = Auth::user()->empresa;
        $postulacion->load('vacante');
        abort_if(!$empresa || $postulacion->vacante->empresa_id !== $empresa->id, 403);
    }
}
