<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\VacanteService;
use App\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function dashboard()
    {
        $empresa = Auth::user()->empresa;

        if (! $empresa) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Tu perfil de empresa todavía no está configurado.');
        }

        if ($empresa->estado !== 'activa') {
            return view('empresa.pendiente', compact('empresa'));
        }

        $vacantesIds = $empresa->vacantes()->pluck('id');

        $stats = [
            'solicitudes_activas' => $empresa->vacantes()->where('estado', 'activa')->count(),
            'solicitudes_pendientes' => $empresa->vacantes()->where('estado', 'pendiente')->count(),
            'candidatos_total' => Postulacion::whereIn('vacante_id', $vacantesIds)->count(),
            'en_proceso' => Postulacion::whereIn('vacante_id', $vacantesIds)
                ->where('estado', 'entrevista')
                ->count(),
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
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $empresa = Auth::user()->empresa;
        $solicitudes = $empresa->vacantes()->latest()->paginate(10);
        $tipos = Vacante::tiposServicio();

        return view('empresa.solicitudes.index', compact('solicitudes', 'empresa', 'tipos'));
    }

    public function crearSolicitud()
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $tipos = Vacante::tiposServicio();
        $estudios = Vacante::nivelesEstudios();

        return view('empresa.solicitudes.create', compact('niveles', 'tipos', 'estudios'));
    }

    public function guardarSolicitud(Request $request, WorkflowService $workflow, VacanteService $vacanteService)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'tipo_servicio' => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo' => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida' => 'nullable|string|max:150',
            'experiencia_minima' => 'nullable|integer|min:0|max:60',
            'descripcion' => 'nullable|string|max:5000',
            'requerimientos' => 'nullable|string|max:2000',
            'salario_min' => 'nullable|numeric|min:0',
            'salario_max' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        $empresa = Auth::user()->empresa;
        abort_if(! $empresa, 403, 'Tu perfil de empresa todavía no está configurado.');

        $solicitud = $vacanteService->crear($data, $empresa->id, 'pendiente');
        $workflow->decideVacanteCreation($solicitud);

        return redirect()
            ->route('empresa.solicitudes')
            ->with('success', 'Solicitud enviada. El administrador la revisará a la brevedad.');
    }

    public function verSolicitud(Vacante $vacante)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarVacante($vacante);
        $vacante->load('postulaciones.candidato.usuario');

        return view('empresa.solicitudes.show', compact('vacante'));
    }

    public function editarSolicitud(Vacante $vacante)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarVacante($vacante);
        abort_if($vacante->estado !== 'pendiente', 403, 'Solo puedes editar solicitudes pendientes.');

        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $tipos = Vacante::tiposServicio();
        $estudios = Vacante::nivelesEstudios();

        return view('empresa.solicitudes.edit', compact('vacante', 'niveles', 'tipos', 'estudios'));
    }

    public function actualizarSolicitud(Request $request, Vacante $vacante, VacanteService $vacanteService)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarVacante($vacante);
        abort_if($vacante->estado !== 'pendiente', 403, 'Solo puedes editar solicitudes pendientes.');

        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'tipo_servicio' => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo' => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida' => 'nullable|string|max:150',
            'experiencia_minima' => 'nullable|integer|min:0|max:60',
            'requerimientos' => 'nullable|string|max:2000',
        ]);

        $vacanteService->actualizar($vacante, $data);

        return redirect()->route('empresa.solicitudes')->with('success', 'Solicitud actualizada correctamente.');
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarPostulacion($postulacion);

        $request->validate(['estado' => 'required|in:' . implode(',', array_keys(Postulacion::estadosProceso()))]);
        $postulacion->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    private function redirigirSiPendiente(): ?RedirectResponse
    {
        $empresa = Auth::user()->empresa;

        if (! $empresa) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Tu perfil de empresa todavía no está configurado.');
        }

        if ($empresa->estado !== 'activa') {
            return redirect()
                ->route('empresa.dashboard')
                ->with('warning', 'Tu empresa está en revisión. Espera la aprobación para usar las solicitudes.');
        }

        return null;
    }

    private function autorizarVacante(Vacante $vacante): void
    {
        $empresa = Auth::user()->empresa;
        abort_if(! $empresa || $vacante->empresa_id !== $empresa->id, 403);
    }

    private function autorizarPostulacion(Postulacion $postulacion): void
    {
        $empresa = Auth::user()->empresa;
        $postulacion->load('vacante');
        abort_if(! $empresa || $postulacion->vacante->empresa_id !== $empresa->id, 403);
    }
}
