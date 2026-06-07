<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\PostulacionService;
use App\Services\VacanteService;
use App\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    public function dashboard(\App\Services\ResumenRapidoService $resumen)
    {
        $empresa = Auth::user()->empresa;

        if (! $empresa) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Tu perfil de empresa todavia no esta configurado.');
        }

        if ($empresa->estado !== 'activa') {
            return view('empresa.pendiente', compact('empresa'));
        }

        $vacantesIds = $empresa->vacantes()->pluck('id');

        $stats = [
            'solicitudes_activas' => $empresa->vacantes()->where('estado', 'activa')->count(),
            'solicitudes_pendientes' => $empresa->vacantes()->where('estado', 'pendiente')->count(),
            'contratados' => Postulacion::whereIn('vacante_id', $vacantesIds)
                ->where('estado', 'seleccionado')->count(),
            'en_proceso' => $empresa->vacantes()->where('estado', 'activa')
                ->whereDoesntHave('postulaciones', fn ($q) => $q->where('estado', 'seleccionado'))
                ->count(),
        ];

        $solicitudesRecientes = $empresa->vacantes()
            ->withCount(['postulaciones' => fn ($q) => $q->where('estado', 'seleccionado')])
            ->latest()
            ->take(5)
            ->get();

        $acciones = $resumen->paraEmpresa($empresa);

        return view('empresa.dashboard', [
            'empresa' => $empresa,
            'stats' => $stats,
            'solicitudes_recientes' => $solicitudesRecientes,
            'acciones' => $acciones,
        ]);
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

    public function crearSolicitud(Request $request)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $servicioCatalogo = null;
        $catalogoServicioId = $request->integer('catalogo_servicio');

        if ($catalogoServicioId) {
            $servicioCatalogo = $this->catalogoVacanteVisible($catalogoServicioId);

            if (! $servicioCatalogo) {
                return redirect()
                    ->route('empresa.servicios.index')
                    ->with('error', 'El servicio seleccionado no esta disponible para solicitar vacantes.');
            }
        }

        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $estudios = Vacante::nivelesEstudios();
        $areas = \App\Models\CatalogoOpcion::opciones('areas_carreras', []);
        $contratos = \App\Models\CatalogoOpcion::opciones('tipos_contrato', []);

        return view('empresa.solicitudes.create', compact(
            'niveles',
            'estudios',
            'areas',
            'contratos',
            'servicioCatalogo',
        ));
    }

    public function guardarSolicitud(Request $request, WorkflowService $workflow, VacanteService $vacanteService)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'catalogo_servicio_id' => 'nullable|integer|exists:catalogo_servicios,id',
            'titulo' => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'cupos' => 'nullable|integer|min:1|max:100',
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida' => 'nullable|string|max:150',
            'tipo_contrato' => 'nullable|string|max:50',
            'experiencia_minima' => 'nullable|integer|min:0|max:60',
            'requerimientos' => 'nullable|string|max:2000',
            'salario_min' => 'nullable|numeric|min:0',
            'salario_max' => 'nullable|numeric|min:0',
            'ingresos_ofrecidos' => 'nullable|string|max:1000',
            'prestaciones' => 'nullable|string|max:2000',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        $catalogoServicioId = $data['catalogo_servicio_id'] ?? null;
        unset($data['catalogo_servicio_id']);

        $data['tipo_servicio'] = 'reclutamiento';

        $empresa = Auth::user()->empresa;
        abort_if(! $empresa, 403, 'Tu perfil de empresa todavia no esta configurado.');

        if ($catalogoServicioId && ! $this->catalogoVacanteVisible((int) $catalogoServicioId)) {
            return redirect()
                ->route('empresa.servicios.index')
                ->with('error', 'El servicio seleccionado no esta disponible para solicitar vacantes.');
        }

        $solicitud = $vacanteService->crear($data, $empresa->id, 'pendiente');
        $workflow->decideVacanteCreation($solicitud);

        return redirect()
            ->route('empresa.solicitudes')
            ->with('success', 'Solicitud de vacante enviada. Buscaremos candidatos y te avisaremos pronto.');
    }

    public function verSolicitud(Vacante $vacante)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarVacante($vacante);
        $vacante->load(['postulaciones' => fn ($q) => $q->where('estado', 'seleccionado'), 'postulaciones.candidato.usuario']);

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
        $estudios = Vacante::nivelesEstudios();
        $areas = \App\Models\CatalogoOpcion::opciones('areas_carreras', []);
        $contratos = \App\Models\CatalogoOpcion::opciones('tipos_contrato', []);

        return view('empresa.solicitudes.edit', compact('vacante', 'niveles', 'estudios', 'areas', 'contratos'));
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
            'titulo' => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'cupos' => 'nullable|integer|min:1|max:100',
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida' => 'nullable|string|max:150',
            'tipo_contrato' => 'nullable|string|max:50',
            'experiencia_minima' => 'nullable|integer|min:0|max:60',
            'requerimientos' => 'nullable|string|max:2000',
            'salario_min' => 'nullable|numeric|min:0',
            'salario_max' => 'nullable|numeric|min:0',
            'ingresos_ofrecidos' => 'nullable|string|max:1000',
            'prestaciones' => 'nullable|string|max:2000',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        $data['tipo_servicio'] = 'reclutamiento';

        try {
            $vacanteService->actualizar($vacante, $data);
        } catch (\DomainException $e) {
            report($e);

            return back()
                ->with('error', 'No fue posible actualizar la solicitud. Revisa los datos e intentalo de nuevo.')
                ->withInput();
        }

        return redirect()->route('empresa.solicitudes')->with('success', 'Vacante actualizada correctamente.');
    }

    public function eliminarSolicitud(Vacante $vacante): RedirectResponse
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarVacante($vacante);
        abort_if($vacante->estado !== 'pendiente', 422, 'Solo puedes eliminar solicitudes que aun no han sido aprobadas.');

        $vacante->postulaciones()->delete();
        $vacante->delete();

        return redirect()
            ->route('empresa.solicitudes')
            ->with('success', 'Solicitud eliminada.');
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion, PostulacionService $postulacionService)
    {
        if ($redirigir = $this->redirigirSiPendiente()) {
            return $redirigir;
        }

        $this->autorizarPostulacion($postulacion);

        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Postulacion::estadosProceso())),
        ]);

        try {
            $postulacionService->mover($postulacion, $request->estado);
        } catch (\DomainException $e) {
            report($e);

            return back()->with('error', 'No fue posible cambiar el estado de la postulacion. Intenta de nuevo.');
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    private function redirigirSiPendiente(): ?RedirectResponse
    {
        $empresa = Auth::user()->empresa;

        if (! $empresa) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Tu perfil de empresa todavia no esta configurado.');
        }

        if ($empresa->estado !== 'activa') {
            return redirect()
                ->route('empresa.dashboard')
                ->with('warning', 'Tu empresa esta en revision. Espera la aprobacion para usar las solicitudes.');
        }

        return null;
    }

    private function autorizarVacante(Vacante $vacante): void
    {
        $empresa = Auth::user()->empresa;
        abort_if(! $empresa || $vacante->empresa_id !== $empresa->id, 403, 'Esta vacante pertenece a otra empresa.');
    }

    private function autorizarPostulacion(Postulacion $postulacion): void
    {
        $empresa = Auth::user()->empresa;
        $postulacion->load('vacante');
        abort_if(! $empresa || $postulacion->vacante->empresa_id !== $empresa->id, 403, 'Esta postulacion no es de una vacante tuya.');
    }

    private function catalogoVacanteVisible(int $catalogoId): ?CatalogoServicio
    {
        return CatalogoServicio::query()
            ->visiblesParaRol('empresa')
            ->where('flujo', 'vacante')
            ->find($catalogoId);
    }
}
