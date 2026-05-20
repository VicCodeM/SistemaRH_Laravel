<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\ServicioAsignado;
use App\Models\Vacante;
use App\Services\BusquedaService;
use App\Services\DashboardService;
use App\Services\ExportService;
use App\Services\PostulacionService;
use App\Services\ReporteService;
use App\Services\SolicitudCompatibilidadService;
use App\Services\VacanteService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(DashboardService $dashboard)
    {
        return view('admin.dashboard', $dashboard->datosAdmin());
    }

    public function reportes(Request $request, ReporteService $reporteService)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $kpis               = $reporteService->kpis($desde, $hasta);
        $resumen            = $reporteService->resumen();
        $empresasTop        = $reporteService->topEmpresas($desde, $hasta);
        $internosTop        = $reporteService->topInternos($desde, $hasta);
        $solicitudesActivas = $reporteService->solicitudesActivas();
        $tareasRecientes    = $reporteService->tareasRecientes();

        return view('admin.reportes.index', compact(
            'kpis', 'resumen', 'empresasTop', 'internosTop',
            'solicitudesActivas', 'tareasRecientes'
        ));
    }

    public function exportarCsv(ExportService $exportService)
    {
        return $exportService->reporteSistema();
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

    public function rechazarEmpresaModal(Empresa $empresa)
    {
        return view('admin.empresas.modal-rechazar', compact('empresa'));
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

    public function accionEmpresaModal(Empresa $empresa, string $accion)
    {
        $config = match ($accion) {
            'aprobar' => [
                'titulo' => 'Aprobar acceso de empresa',
                'descripcion' => 'La empresa quedara activa y podra entrar a la plataforma.',
                'mensaje' => 'Confirma si deseas aprobar este acceso ahora.',
                'ruta' => route('admin.empresas.aprobar', $empresa),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar aprobacion',
                'clase' => 'btn-success',
            ],
            'rechazar' => [
                'titulo' => 'Rechazar acceso de empresa',
                'descripcion' => 'La empresa quedara rechazada y no podra acceder a la plataforma.',
                'mensaje' => 'Confirma si deseas rechazar este acceso. Podras reactivarlo mas adelante.',
                'ruta' => route('admin.empresas.rechazar', $empresa),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar rechazo',
                'clase' => 'btn-danger',
            ],
            'suspender' => [
                'titulo' => 'Suspender acceso de empresa',
                'descripcion' => 'La empresa conservara sus datos, pero perdera acceso temporalmente.',
                'mensaje' => 'Confirma si deseas suspender este acceso.',
                'ruta' => route('admin.empresas.suspender', $empresa),
                'metodo' => 'PATCH',
                'boton' => 'Suspender empresa',
                'clase' => 'btn-secondary',
            ],
            'reactivar' => [
                'titulo' => 'Reactivar empresa',
                'descripcion' => 'La empresa volvera a estado activa y recuperara acceso.',
                'mensaje' => 'Confirma si deseas reactivar esta empresa.',
                'ruta' => route('admin.empresas.aprobar', $empresa),
                'metodo' => 'PATCH',
                'boton' => 'Reactivar empresa',
                'clase' => 'btn-success',
            ],
            'eliminar' => [
                'titulo' => 'Eliminar empresa',
                'descripcion' => 'Esta accion elimina la empresa y su usuario de forma permanente.',
                'mensaje' => 'Confirma la eliminacion definitiva. Esta accion no se puede deshacer.',
                'ruta' => route('admin.empresas.destroy', $empresa),
                'metodo' => 'DELETE',
                'boton' => 'Eliminar permanentemente',
                'clase' => 'btn-danger',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $empresa->load('usuario');

        $registro = [
            'titulo' => $empresa->nombre_empresa,
            'detalle' => ($empresa->usuario?->email ?? 'Sin correo') . ' · RFC: ' . ($empresa->rfc ?: 'No capturado'),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function exportarEmpresaPdf(Empresa $empresa)
    {
        $empresa->load('usuario');
        return view('admin.empresas.imprimible', compact('empresa'));
    }

    public function editarEmpresa(Empresa $empresa)
    {
        $empresa->load('usuario');
        return view('admin.empresas.editar', compact('empresa'));
    }

    public function actualizarEmpresa(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'nombre_empresa'   => ['required', 'string', 'max:255'],
            'razon_social'     => ['nullable', 'string', 'max:255'],
            'rfc'              => ['nullable', 'string', 'max:20'],
            'nombre_rh'        => ['nullable', 'string', 'max:255'],
            'telefono'         => ['nullable', 'string', 'max:30'],
            'telefono_directo' => ['nullable', 'string', 'max:30'],
            'pagina_web'       => ['nullable', 'string', 'max:255'],
            'direccion'        => ['nullable', 'string', 'max:500'],
            'ciudad'           => ['nullable', 'string', 'max:100'],
            'municipio'        => ['nullable', 'string', 'max:100'],
            'codigo_postal'    => ['nullable', 'string', 'max:10'],
            'descripcion'      => ['nullable', 'string', 'max:2000'],
            'estado'           => ['required', 'in:pendiente,activa,suspendida,rechazada'],
        ]);

        $empresa->update($data);

        return redirect()
            ->route('admin.empresas')
            ->with('success', "Datos de \"{$empresa->nombre_empresa}\" actualizados.");
    }

    public function showCandidato(Candidato $candidato)
    {
        $candidato->load(['usuario', 'postulaciones.vacante.empresa']);
        return view('admin.candidatos.modal', compact('candidato'));
    }

    public function accionCandidatoModal(Candidato $candidato, string $accion)
    {
        $config = match ($accion) {
            'aprobar' => [
                'titulo' => 'Aprobar solicitud',
                'descripcion' => 'El candidato quedara aprobado y podra postularse a vacantes.',
                'mensaje' => 'Confirma si deseas aprobar esta solicitud. Si esta incompleta, el sistema te mostrara un aviso.',
                'ruta' => route('admin.candidatos.aprobar', $candidato),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar aprobacion',
                'clase' => 'btn-success',
            ],
            'rechazar' => [
                'titulo' => 'Rechazar solicitud',
                'descripcion' => 'La solicitud quedara rechazada y el candidato no podra continuar en el proceso.',
                'mensaje' => 'Confirma si deseas rechazar esta solicitud. Podras reactivarla despues si hace falta.',
                'ruta' => route('admin.candidatos.rechazar', $candidato),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar rechazo',
                'clase' => 'btn-danger',
            ],
            'reactivar' => [
                'titulo' => 'Reactivar candidato',
                'descripcion' => 'La solicitud volvera a estado aprobada si cumple con las reglas del sistema.',
                'mensaje' => 'Confirma si deseas reactivar este perfil.',
                'ruta' => route('admin.candidatos.aprobar', $candidato),
                'metodo' => 'PATCH',
                'boton' => 'Reactivar perfil',
                'clase' => 'btn-success',
            ],
            'eliminar' => [
                'titulo' => 'Eliminar candidato',
                'descripcion' => 'Esta accion elimina el perfil del candidato y su usuario asociado.',
                'mensaje' => 'Confirma la eliminacion definitiva. Esta accion no se puede deshacer.',
                'ruta' => route('admin.candidatos.destroy', $candidato),
                'metodo' => 'DELETE',
                'boton' => 'Eliminar permanentemente',
                'clase' => 'btn-danger',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $candidato->load('usuario');

        $registro = [
            'titulo' => $candidato->nombreCompleto(),
            'detalle' => ($candidato->puesto_deseado ?: ($candidato->usuario?->email ?? 'Sin correo')) . ' · Avance: ' . $candidato->solicitudProgreso() . '%',
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function editarSolicitudCandidato(Candidato $candidato)
    {
        $candidato->load('usuario');

        return view('admin.candidatos.solicitud', compact('candidato'));
    }

    public function exportarSolicitudCandidatoPdf(Candidato $candidato)
    {
        $candidato->load('usuario', 'postulaciones.vacante.empresa');
        return view('admin.candidatos.solicitud-imprimible', compact('candidato'));
    }

    public function candidatos(Request $request)
    {
        $query = Candidato::with('usuario')
            ->orderByDesc('solicitud_enviada_at')
            ->orderByDesc('id');
        $this->aplicarFiltrosCandidatos($query, $request);

        $candidatos = $query->paginate(15)->withQueryString();

        return view('admin.candidatos.index', compact('candidatos'));
    }

    public function aprobarCandidato(Candidato $candidato)
    {
        if (! $candidato->solicitudCompleta()) {
            return back()->with('error', 'No se puede aprobar una solicitud incompleta.');
        }

        $candidato->update([
            'solicitud_estado'            => 'aprobada',
            'solicitud_revisada_at'       => now(),
            'solicitud_revision_admin_id' => auth()->id(),
        ]);

        return back()->with('success', "Solicitud de {$candidato->nombre} aprobada.");
    }

    public function rechazarCandidatoModal(Candidato $candidato)
    {
        return view('admin.candidatos.modal-rechazar', compact('candidato'));
    }

    public function rechazarCandidato(Candidato $candidato)
    {
        $candidato->update([
            'solicitud_estado'            => 'rechazada',
            'solicitud_revisada_at'       => now(),
            'solicitud_revision_admin_id' => auth()->id(),
        ]);

        return back()->with('error', "Solicitud de {$candidato->nombre} rechazada.");
    }

    public function vacantes(Request $request)
    {
        $modo = $request->get('modo', 'reclutamiento'); // 'reclutamiento' | 'servicios'

        $query = Vacante::with('empresa')->latest();

        if ($modo === 'reclutamiento') {
            $query->where('tipo_servicio', 'reclutamiento');
        } else {
            $query->where('tipo_servicio', '!=', 'reclutamiento');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', "%{$buscar}%"));
            });
        }

        $withCount = [
            'postulaciones',
            'postulaciones as seleccionados_count' => fn ($q) => $q->where('estado', 'seleccionado'),
            'postulaciones as entrevista_count'    => fn ($q) => $q->where('estado', 'entrevista'),
            'postulaciones as postulados_count'    => fn ($q) => $q->where('estado', 'postulado'),
            'serviciosAsignados',
        ];

        $vacantes = $query->withCount($withCount)->paginate(15)->withQueryString();

        $statsReclutamiento = [
            'pendientes' => Vacante::where('tipo_servicio', 'reclutamiento')->where('estado', 'pendiente')->count(),
            'activas'    => Vacante::where('tipo_servicio', 'reclutamiento')->where('estado', 'activa')->count(),
        ];
        $statsServicios = [
            'pendientes' => Vacante::where('tipo_servicio', '!=', 'reclutamiento')->where('estado', 'pendiente')->count(),
            'activas'    => Vacante::where('tipo_servicio', '!=', 'reclutamiento')->where('estado', 'activa')->count(),
        ];

        return view('admin.vacantes.index', compact('vacantes', 'modo', 'statsReclutamiento', 'statsServicios'));
    }

    public function showVacante(Vacante $vacante)
    {
        $vacante->load([
            'empresa.usuario',
            'serviciosAsignados' => fn ($query) => $query->with('asignadoA')->latest()->take(3),
        ])->loadCount([
            'postulaciones',
            'postulaciones as seleccionados_count' => fn ($query) => $query->where('estado', 'seleccionado'),
            'postulaciones as entrevista_count' => fn ($query) => $query->where('estado', 'entrevista'),
            'postulaciones as postulados_count' => fn ($query) => $query->where('estado', 'postulado'),
            'serviciosAsignados',
        ]);

        return view('admin.vacantes.modal', compact('vacante'));
    }

    public function accionVacanteModal(Vacante $vacante, string $accion)
    {
        $config = match ($accion) {
            'rechazar' => [
                'titulo' => 'Rechazar solicitud',
                'descripcion' => 'La solicitud quedará como Rechazada y dejará de avanzar en el flujo operativo.',
                'mensaje' => '¿Confirmas que deseas rechazar esta solicitud? Podrás reactivarla más adelante si hace falta.',
                'ruta' => route('admin.vacantes.rechazar', $vacante),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar rechazo',
                'clase' => 'btn-danger',
            ],
            'cerrar' => [
                'titulo' => 'Cerrar solicitud',
                'descripcion' => 'La solicitud dejará de recibir movimiento y quedará marcada como Cerrada.',
                'mensaje' => '¿Deseas cerrar esta solicitud ahora? Si cambia la necesidad, luego podrás reabrirla.',
                'ruta' => route('admin.vacantes.cerrar', $vacante),
                'metodo' => 'PATCH',
                'boton' => 'Confirmar cierre',
                'clase' => 'btn-secondary',
            ],
            'eliminar' => [
                'titulo' => 'Eliminar solicitud',
                'descripcion' => 'Esta acción elimina el registro de forma permanente.',
                'mensaje' => '¿Confirmas la eliminación definitiva? Esta acción no se puede deshacer.',
                'ruta' => route('admin.vacantes.destroy', $vacante),
                'metodo' => 'DELETE',
                'boton' => 'Eliminar definitivamente',
                'clase' => 'btn-danger',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $vacante->load('empresa');

        return view('admin.vacantes.modal-accion', compact('vacante', 'accion', 'config'));
    }

    public function activarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'activa']);
        return back()->with('success', "Solicitud \"{$vacante->titulo}\" activada.");
    }

    public function rechazarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'rechazada']);

        return back()->with('error', "Solicitud \"{$vacante->titulo}\" rechazada.");
    }

    public function cerrarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'cerrada']);
        return back()->with('success', "Solicitud \"{$vacante->titulo}\" cerrada.");
    }

    public function crearVacante()
    {
        $empresas  = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $niveles   = CatalogoServicio::nivelesJerarquicosFormulario();
        $estudios  = Vacante::nivelesEstudios();
        $areas     = \App\Models\CatalogoOpcion::opciones('areas_carreras', []);
        $contratos = \App\Models\CatalogoOpcion::opciones('tipos_contrato', []);

        return view('admin.vacantes.create', compact('empresas', 'niveles', 'estudios', 'areas', 'contratos'));
    }

    public function guardarVacante(Request $request, VacanteService $vacanteService)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'empresa_id'            => 'required|exists:empresas,id',
            'titulo'                => 'required|string|max:200',
            'nivel_jerarquico'      => "required|in:{$nivelesValidos}",
            'cupos'                 => 'nullable|integer|min:1|max:100',
            'notas_internas'        => 'nullable|string|max:2000',
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida'        => 'nullable|string|max:150',
            'tipo_contrato'         => 'nullable|string|max:50',
            'experiencia_minima'    => 'nullable|integer|min:0|max:60',
            'descripcion'           => 'nullable|string|max:5000',
            'requerimientos'        => 'nullable|string|max:2000',
            'salario_min'           => 'nullable|numeric|min:0',
            'salario_max'           => 'nullable|numeric|min:0',
            'ubicacion'             => 'nullable|string|max:200',
        ]);

        // Vacante = solo reclutamiento (los demás servicios van por ServicioAsignado)
        $data['tipo_servicio'] = 'reclutamiento';

        $vacanteService->crear($data, (int) $data['empresa_id'], 'activa');

        return redirect()->route('admin.vacantes')->with('success', 'Vacante creada y activada.');
    }

    public function cerrarVacanteManual(Request $request, Vacante $vacante, VacanteService $vacanteService)
    {
        $data = $request->validate([
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        $vacanteService->cerrarManual($vacante, $data['motivo'] ?? null);

        return back()->with('success', "Vacante \"{$vacante->titulo}\" cerrada.");
    }

    public function reabrirVacante(Vacante $vacante, VacanteService $vacanteService)
    {
        try {
            $vacanteService->reabrir($vacante);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Vacante \"{$vacante->titulo}\" reabierta.");
    }

    public function editarVacante(Vacante $vacante)
    {
        $vacante->load('empresa');
        $niveles   = CatalogoServicio::nivelesJerarquicosFormulario();
        $estudios  = Vacante::nivelesEstudios();
        $areas     = \App\Models\CatalogoOpcion::opciones('areas_carreras', []);
        $contratos = \App\Models\CatalogoOpcion::opciones('tipos_contrato', []);

        return view('admin.vacantes.edit', compact('vacante', 'niveles', 'estudios', 'areas', 'contratos'));
    }

    public function actualizarVacante(Request $request, Vacante $vacante, VacanteService $vacanteService)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'titulo'                => 'required|string|max:200',
            'nivel_jerarquico'      => "required|in:{$nivelesValidos}",
            'cupos'                 => 'nullable|integer|min:1|max:100',
            'notas_internas'        => 'nullable|string|max:2000',
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida'        => 'nullable|string|max:150',
            'tipo_contrato'         => 'nullable|string|max:50',
            'experiencia_minima'    => 'nullable|integer|min:0|max:60',
            'descripcion'           => 'nullable|string|max:5000',
            'requerimientos'        => 'nullable|string|max:2000',
            'salario_min'           => 'nullable|numeric|min:0',
            'salario_max'           => 'nullable|numeric|min:0',
            'ubicacion'             => 'nullable|string|max:200',
        ]);

        // Vacante = solo reclutamiento (los demás servicios van por ServicioAsignado)
        $data['tipo_servicio'] = 'reclutamiento';

        try {
            $vacanteService->actualizar($vacante, $data);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.vacantes')->with('success', "Vacante \"{$vacante->titulo}\" actualizada.");
    }

    public function exportarCandidatosCsv(Vacante $vacante, \App\Services\ExportadorService $exportador)
    {
        $vacante->load('postulaciones.candidato.usuario');

        $filas = $vacante->postulaciones->map(fn ($p) => [
            $p->candidato?->nombre . ' ' . ($p->candidato?->apellido_paterno ?? ''),
            $p->candidato?->usuario?->email ?? '',
            $p->candidato?->puesto_deseado ?? '',
            (int) ($p->candidato?->experiencia_anios ?? 0),
            Postulacion::estadoLabel($p->estado),
            $p->fecha_postulacion?->format('d/m/Y') ?? '',
            $p->motivo_asignacion ?? '',
        ]);

        return $exportador->csv("candidatos_vacante_{$vacante->id}", [
            'Nombre', 'Correo', 'Aspiración', 'Experiencia (años)',
            'Estado', 'Postulado', 'Motivo / Notas',
        ], $filas);
    }

    public function exportarCandidatosPdf(Vacante $vacante)
    {
        $vacante->load('empresa', 'postulaciones.candidato.usuario');
        return view('admin.vacantes.candidatos-imprimible', compact('vacante'));
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion, PostulacionService $postulacionService)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Postulacion::estadosProceso())),
        ]);

        try {
            $postulacionService->mover($postulacion, $request->estado);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $postulacionService->mensajeParaEstado($request->estado));
    }

    public function destroyEmpresa(Empresa $empresa)
    {
        $usuario = $empresa->usuario;
        $empresa->delete();
        $usuario?->delete();

        return back()->with('success', 'Empresa eliminada permanentemente.');
    }

    public function destroyCandidato(Candidato $candidato)
    {
        $usuario = $candidato->usuario;
        $candidato->delete();
        $usuario?->delete();

        return back()->with('success', 'Candidato eliminado permanentemente.');
    }

    public function destroyVacante(Vacante $vacante)
    {
        $vacante->delete();

        return back()->with('success', 'Solicitud eliminada permanentemente.');
    }

    public function matchingVacante(Request $request, Vacante $vacante, SolicitudCompatibilidadService $compatibilidad)
    {
        $vacante->load('empresa', 'postulaciones.candidato.usuario');

        $asignados = $vacante->postulaciones()->with('candidato.usuario')->latest('fecha_postulacion')->get();

        $yaActivos = $asignados
            ->whereIn('estado', Postulacion::estadosActivos())
            ->pluck('candidato_id')
            ->all();

        $candidatos = Candidato::where('solicitud_estado', 'aprobada')
            ->whereNotIn('id', $yaActivos)
            ->with('usuario');

        $this->aplicarFiltrosCandidatos($candidatos, $request, false);

        $candidatos = $candidatos
            ->get()
            ->map(function (Candidato $candidato) use ($vacante, $compatibilidad) {
                return [
                    'candidato' => $candidato,
                    'compatibilidad' => $compatibilidad->evaluar($vacante, $candidato),
                ];
            })
            ->sortByDesc(fn ($item) => $item['compatibilidad']['puntaje'])
            ->values();

        $grupos = [
            'aptos' => $candidatos->where('compatibilidad.categoria', 'aptos')->values(),
            'dudosos' => $candidatos->where('compatibilidad.categoria', 'dudosos')->values(),
            'no_aptos' => $candidatos->where('compatibilidad.categoria', 'no_aptos')->values(),
        ];

        $requisitos = [
            'nivel_jerarquico' => CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico),
            'nivel_estudios_minimo' => Vacante::nivelEstudiosLabel($vacante->nivel_estudios_minimo),
            'area_requerida' => $vacante->area_requerida,
            'experiencia_minima' => $vacante->experiencia_minima,
        ];

        return view('admin.vacantes.matching', compact('vacante', 'grupos', 'asignados', 'requisitos'));
    }

    public function asignarCandidato(Request $request, Vacante $vacante, PostulacionService $postulacionService)
    {
        $data = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'forzar' => ['nullable', 'boolean'],
            'motivo_asignacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $resultado = $postulacionService->asignar(
            $vacante,
            (int) $data['candidato_id'],
            $request->boolean('forzar'),
            $data['motivo_asignacion'] ?? null
        );

        if (! $resultado['exito']) {
            return back()->with('error', $resultado['mensaje']);
        }

        return back()->with('success', $resultado['mensaje']);
    }

    public function crearTareaDesdeVacante(Vacante $vacante)
    {
        abort_if($vacante->esReclutamiento(), 403, 'Las vacantes de reclutamiento se gestionan con postulaciones.');

        $servicio = CatalogoServicio::where('tipo', $vacante->tipo_servicio)
            ->where('activo', true)
            ->first();

        if (! $servicio) {
            return back()->with('error', 'No existe un servicio activo en el catálogo para este tipo de solicitud.');
        }

        $tarea = ServicioAsignado::create([
            'servicio_id'    => $servicio->id,
            'asignable_type' => Empresa::class,
            'asignable_id'   => $vacante->empresa_id,
            'vacante_id'     => $vacante->id,
            'estado'         => 'pendiente',
            'notas'          => $vacante->descripcion ?? $vacante->requerimientos,
            'asignado_por'   => auth()->id(),
            'solicitado_por' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.tareas.show', $tarea)
            ->with('success', 'Tarea de servicio creada. Asigna un responsable para iniciar el trabajo.');
    }

    public function buscarGlobal(Request $request, BusquedaService $busqueda)
    {
        $q = trim((string) $request->input('q'));

        if ($q === '') {
            return view('admin.buscar', ['resultados' => [], 'q' => '']);
        }

        $resultados = $busqueda->global($q);

        return view('admin.buscar', compact('resultados', 'q'));
    }

    /**
     * Resultados de búsqueda en JSON para el buscador rápido (Cmd+K).
     */
    public function buscarJson(Request $request, BusquedaService $busqueda)
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['resultados' => []]);
        }

        return response()->json([
            'resultados' => $busqueda->global($q)->values(),
        ]);
    }

    private function aplicarFiltrosCandidatos(Builder $query, Request $request, bool $incluirEstado = true): void
    {
        if ($incluirEstado && $request->filled('estado')) {
            $query->where('solicitud_estado', $request->estado);
        }

        if ($request->filled('estudios')) {
            $nivel = Vacante::normalizarNivelEstudios($request->string('estudios')->toString());
            $nivelesPermitidos = Vacante::nivelesEstudiosDesde($nivel);

            if ($nivelesPermitidos !== []) {
                $query->whereIn('escolaridad', $nivelesPermitidos);
            }
        }

        if ($request->filled('experiencia_min')) {
            $query->where('experiencia_anios', '>=', max(0, (int) $request->input('experiencia_min')));
        }

        if ($request->filled('aspiracion')) {
            $aspiracion = trim((string) $request->input('aspiracion'));

            if ($aspiracion !== '') {
                $query->where('puesto_deseado', 'like', "%{$aspiracion}%");
            }
        }

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('curp', 'like', "%{$buscar}%");
            });
        }
    }
}
