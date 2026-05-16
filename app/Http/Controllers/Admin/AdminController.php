<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\CatalogoServicio;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\Ticket;
use App\Models\ServicioAsignado;
use App\Models\Vacante;
use App\Services\DashboardService;
use App\Services\PostulacionService;
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

    public function reportes()
    {
        $resumen = [
            'empresas_total' => Empresa::count(),
            'empresas_activas' => Empresa::where('estado', 'activa')->count(),
            'empresas_pendientes' => Empresa::where('estado', 'pendiente')->count(),
            'candidatos_total' => Candidato::count(),
            'candidatos_aprobados' => Candidato::where('solicitud_estado', 'aprobada')->count(),
            'candidatos_pendientes' => Candidato::where('solicitud_estado', 'enviada')->count(),
            'solicitudes_total' => Vacante::count(),
            'solicitudes_activas' => Vacante::where('estado', 'activa')->count(),
            'solicitudes_pendientes' => Vacante::where('estado', 'pendiente')->count(),
            'tareas_total' => ServicioAsignado::count(),
            'tareas_activas' => ServicioAsignado::whereIn('estado', ['activo', 'en_proceso'])->count(),
            'tickets_total' => Ticket::count(),
            'tickets_abiertos' => Ticket::where('estado', 'abierto')->count(),
            'tickets_vencidos' => Ticket::whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->whereNotIn('estado', ['resuelto', 'cerrado'])
                ->count(),
        ];

        $empresasTop = Empresa::withCount('vacantes')
            ->orderByDesc('vacantes_count')
            ->orderBy('nombre_empresa')
            ->limit(8)
            ->get();

        $solicitudesActivas = Vacante::with('empresa')
            ->where('estado', 'activa')
            ->latest('fecha_publicacion')
            ->limit(8)
            ->get();

        $ticketsRecientes = Ticket::with(['empresa', 'asignado'])
            ->latest()
            ->limit(8)
            ->get();

        $tareasRecientes = ServicioAsignado::with(['servicio', 'asignadoA'])
            ->latest()
            ->limit(8)
            ->get();

        // Datos para gráficas mensuales (últimos 6 meses)
        $meses = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i);
        });

        $graficaVacantes = $meses->map(function ($mes) {
            return [
                'label' => $mes->format('M Y'),
                'valor' => Vacante::whereYear('created_at', $mes->year)->whereMonth('created_at', $mes->month)->count(),
            ];
        });

        $graficaPostulaciones = $meses->map(function ($mes) {
            return [
                'label' => $mes->format('M Y'),
                'valor' => Postulacion::whereYear('created_at', $mes->year)->whereMonth('created_at', $mes->month)->count(),
            ];
        });

        $graficaTickets = $meses->map(function ($mes) {
            return [
                'label' => $mes->format('M Y'),
                'valor' => Ticket::whereYear('created_at', $mes->year)->whereMonth('created_at', $mes->month)->count(),
            ];
        });

        return view('admin.reportes.index', compact(
            'resumen',
            'empresasTop',
            'solicitudesActivas',
            'ticketsRecientes',
            'tareasRecientes',
            'graficaVacantes',
            'graficaPostulaciones',
            'graficaTickets'
        ));
    }

    public function exportarCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_sistema_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['Reporte del Sistema RH - ' . now()->format('d/m/Y')]);
            fputcsv($output, []);
            fputcsv($output, ['Resumen']);
            fputcsv($output, ['Empresas totales', Empresa::count()]);
            fputcsv($output, ['Empresas activas', Empresa::where('estado', 'activa')->count()]);
            fputcsv($output, ['Empresas pendientes', Empresa::where('estado', 'pendiente')->count()]);
            fputcsv($output, ['Candidatos totales', Candidato::count()]);
            fputcsv($output, ['Candidatos aprobados', Candidato::where('solicitud_estado', 'aprobada')->count()]);
            fputcsv($output, ['Solicitudes de servicio', Vacante::count()]);
            fputcsv($output, ['Solicitudes activas', Vacante::where('estado', 'activa')->count()]);
            fputcsv($output, ['Tareas totales', ServicioAsignado::count()]);
            fputcsv($output, ['Tickets totales', Ticket::count()]);
            fputcsv($output, ['Tickets abiertos', Ticket::where('estado', 'abierto')->count()]);
            fputcsv($output, ['Tickets vencidos', Ticket::whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->whereNotIn('estado', ['resuelto', 'cerrado'])->count()]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
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

    public function showCandidato(Candidato $candidato)
    {
        $candidato->load(['usuario', 'postulaciones.vacante.empresa']);
        return view('admin.candidatos.modal', compact('candidato'));
    }

    public function editarSolicitudCandidato(Candidato $candidato)
    {
        $candidato->load('usuario');

        return view('admin.candidatos.solicitud', compact('candidato'));
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
            'postulaciones as seleccionados_count' => fn ($q) => $q->where('estado', 'seleccionado'),
            'postulaciones as entrevista_count'    => fn ($q) => $q->where('estado', 'entrevista'),
            'postulaciones as postulados_count'    => fn ($q) => $q->where('estado', 'postulado'),
        ])->paginate(15)->withQueryString();

        return view('admin.vacantes.index', compact('vacantes'));
    }

    public function activarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'activa']);
        return back()->with('success', "Solicitud \"{$vacante->titulo}\" activada.");
    }

    public function cerrarVacante(Vacante $vacante)
    {
        $vacante->update(['estado' => 'cerrada']);
        return back()->with('success', "Solicitud \"{$vacante->titulo}\" cerrada.");
    }

    public function crearVacante()
    {
        $empresas = Empresa::where('estado', 'activa')->orderBy('nombre_empresa')->get();
        $niveles  = CatalogoServicio::nivelesJerarquicosFormulario();
        $tipos    = Vacante::tiposServicio();
        $estudios  = Vacante::nivelesEstudios();

        return view('admin.vacantes.create', compact('empresas', 'niveles', 'tipos', 'estudios'));
    }

    public function guardarVacante(Request $request, VacanteService $vacanteService)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'empresa_id'       => 'required|exists:empresas,id',
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida'   => 'nullable|string|max:150',
            'experiencia_minima'=> 'nullable|integer|min:0|max:60',
            'descripcion'      => 'nullable|string|max:5000',
            'requerimientos'   => 'nullable|string|max:2000',
            'salario_min'      => 'nullable|numeric|min:0',
            'salario_max'      => 'nullable|numeric|min:0',
            'ubicacion'        => 'nullable|string|max:200',
        ]);

        $vacanteService->crear($data, (int) $data['empresa_id'], 'activa');

        return redirect()->route('admin.vacantes')->with('success', 'Solicitud creada y activada.');
    }

    public function editarVacante(Vacante $vacante)
    {
        $vacante->load('empresa');
        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $tipos   = Vacante::tiposServicio();
        $estudios = Vacante::nivelesEstudios();

        return view('admin.vacantes.edit', compact('vacante', 'niveles', 'tipos', 'estudios'));
    }

    public function actualizarVacante(Request $request, Vacante $vacante, VacanteService $vacanteService)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));

        $data = $request->validate([
            'tipo_servicio'    => 'required|in:' . implode(',', array_keys(Vacante::tiposServicio())),
            'titulo'           => 'required|string|max:200',
            'nivel_jerarquico' => "required|in:{$nivelesValidos}",
            'nivel_estudios_minimo' => ['nullable', 'in:' . implode(',', array_keys(Vacante::nivelesEstudios()))],
            'area_requerida'   => 'nullable|string|max:150',
            'experiencia_minima'=> 'nullable|integer|min:0|max:60',
            'descripcion'      => 'nullable|string|max:5000',
            'requerimientos'   => 'nullable|string|max:2000',
            'salario_min'      => 'nullable|numeric|min:0',
            'salario_max'      => 'nullable|numeric|min:0',
            'ubicacion'        => 'nullable|string|max:200',
        ]);

        $vacanteService->actualizar($vacante, $data);

        return redirect()->route('admin.vacantes')->with('success', "Solicitud \"{$vacante->titulo}\" actualizada.");
    }

    public function moverPostulacion(Request $request, Postulacion $postulacion, PostulacionService $postulacionService)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Postulacion::estadosProceso())),
        ]);

        $postulacionService->mover($postulacion, $request->estado);

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

    public function buscarGlobal(Request $request)
    {
        $q = trim((string) $request->input('q'));

        if ($q === '') {
            return view('admin.buscar', ['resultados' => [], 'q' => '']);
        }

        $like = "%{$q}%";

        $empresas = Empresa::where('nombre_empresa', 'like', $like)
            ->orWhere('rfc', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(8)
            ->get()
            ->map(fn ($e) => ['tipo' => 'empresa', 'titulo' => $e->nombre_empresa, 'sub' => $e->rfc ?? 'Sin RFC', 'url' => route('admin.empresas'), 'estado' => $e->estado]);

        $candidatos = Candidato::where('nombre', 'like', $like)
            ->orWhere('apellido_paterno', 'like', $like)
            ->orWhere('apellido_materno', 'like', $like)
            ->orWhere('curp', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(8)
            ->get()
            ->map(fn ($c) => ['tipo' => 'candidato', 'titulo' => $c->nombreCompleto(), 'sub' => $c->puesto_deseado ?? 'Sin puesto', 'url' => route('admin.candidatos'), 'estado' => $c->solicitud_estado]);

        $vacantes = Vacante::where('titulo', 'like', $like)
            ->orWhere('descripcion', 'like', $like)
            ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', $like))
            ->with('empresa')
            ->limit(8)
            ->get()
            ->map(fn ($v) => ['tipo' => 'vacante', 'titulo' => $v->titulo, 'sub' => $v->empresa?->nombre_empresa ?? 'Sin empresa', 'url' => route('admin.vacantes'), 'estado' => $v->estado]);

        $tickets = Ticket::where('asunto', 'like', $like)
            ->orWhere('descripcion', 'like', $like)
            ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', $like))
            ->with('empresa')
            ->limit(8)
            ->get()
            ->map(fn ($t) => ['tipo' => 'ticket', 'titulo' => $t->asunto, 'sub' => $t->empresa?->nombre_empresa ?? 'Sin empresa', 'url' => route('tickets.show', $t), 'estado' => $t->estado]);

        $resultados = $empresas->merge($candidatos)->merge($vacantes)->merge($tickets);

        return view('admin.buscar', compact('resultados', 'q'));
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
