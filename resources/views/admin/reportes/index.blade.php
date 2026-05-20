<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Reportes</span>
        </nav>
        <h1 class="page-title">Reportes</h1>
        <p class="page-subtitle">Indicadores rapidos: aprobaciones de acceso y operacion de vacantes y servicios.</p>
    </x-slot>

    <div class="card fade-in" style="margin-bottom:18px;">
        <form method="GET" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
            <div>
                <label class="form-label" style="font-size:12px;">Desde</label>
                <input type="date" name="desde" value="{{ request('desde', now()->startOfMonth()->format('Y-m-d')) }}" class="form-input" style="font-size:13px;">
            </div>
            <div>
                <label class="form-label" style="font-size:12px;">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta', now()->format('Y-m-d')) }}" class="form-input" style="font-size:13px;">
            </div>
            <button type="submit" class="btn btn-primary" style="font-size:13px;">Filtrar</button>
            @if(request('desde') || request('hasta'))
                <a href="{{ route('admin.reportes') }}" class="btn btn-secondary" style="font-size:13px;">Limpiar</a>
            @endif
            <a href="{{ route('admin.reportes.exportar') }}" class="btn btn-secondary" style="font-size:13px; margin-left:auto;">Exportar resumen</a>
        </form>
    </div>

    <h2 style="font-size:1rem; font-weight:700; margin:18px 0 12px; color:var(--text);">Resumen del periodo ({{ $kpis['desde'] }} -> {{ $kpis['hasta'] }})</h2>
    <div class="metrics-grid fade-in" style="margin-bottom:24px;">
        <div class="metric-card" style="border-left:3px solid #22c55e;">
            <div class="metric-top">
                <span class="metric-label">Vacantes cubiertas</span>
            </div>
            <div class="metric-value" style="color:#22c55e;">{{ $kpis['vacantes_cerradas'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">{{ $kpis['vacantes_nuevas'] }} nuevas publicadas</span>
        </div>
        <div class="metric-card" style="border-left:3px solid #3b82f6;">
            <div class="metric-top">
                <span class="metric-label">Servicios completados</span>
            </div>
            <div class="metric-value" style="color:#3b82f6;">{{ $kpis['servicios_completados'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">{{ $kpis['servicios_nuevos'] }} pedidos nuevos</span>
        </div>
        <div class="metric-card" style="border-left:3px solid #a855f7;">
            <div class="metric-top">
                <span class="metric-label">Nuevos candidatos</span>
            </div>
            <div class="metric-value" style="color:#a855f7;">{{ $kpis['candidatos_nuevos'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">{{ $kpis['empresas_nuevas'] }} empresas nuevas</span>
        </div>
        <div class="metric-card" style="border-left:3px solid #f59e0b;">
            <div class="metric-top">
                <span class="metric-label">Promedio para cerrar vacante</span>
            </div>
            <div class="metric-value" style="color:#f59e0b;">{{ $kpis['dias_promedio_cierre'] ?? '—' }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">Dias desde publicacion hasta cierre</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-bottom:24px;">
        <div class="card fade-in">
            <h3 style="margin:0 0 14px; font-size:0.95rem; font-weight:700;">Top internos del periodo</h3>
            @if($internosTop->isEmpty())
                <p style="color:#94a3b8; font-size:0.85rem; text-align:center; padding:20px;">Aun no hay servicios completados en este periodo.</p>
            @else
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Interno</th>
                                <th style="text-align:center;">Completados</th>
                                <th style="text-align:center;">Activos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($internosTop as $i)
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$i->avatar_url" :nombre="$i->name" :tamano="26" />
                                            <span style="font-weight:500;">{{ $i->name }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;"><span class="badge badge-green">{{ $i->completados }}</span></td>
                                    <td style="text-align:center; color:#64748b;">{{ $i->activos }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($internosTop as $i)
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                        <x-avatar :src="$i->avatar_url" :nombre="$i->name" :tamano="40" />
                                        <div style="min-width:0;">
                                            <h3 class="candidate-mobile-card-title">{{ $i->name }}</h3>
                                            <p class="candidate-mobile-card-subtitle">{{ $i->email }}</p>
                                        </div>
                                    </div>
                                    <span class="badge badge-green">{{ $i->completados }}</span>
                                </div>
                                <div class="candidate-mobile-meta">
                                    <div>
                                        <p class="candidate-mobile-meta-label">Completados</p>
                                        <p class="candidate-mobile-meta-value">{{ $i->completados }}</p>
                                    </div>
                                    <div>
                                        <p class="candidate-mobile-meta-label">Activos</p>
                                        <p class="candidate-mobile-meta-value">{{ $i->activos }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <h3 style="margin:0 0 14px; font-size:0.95rem; font-weight:700;">Top empresas del periodo</h3>
            @if($empresasTop->isEmpty())
                <p style="color:#94a3b8; font-size:0.85rem; text-align:center; padding:20px;">Sin empresas activas en este periodo.</p>
            @else
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th style="text-align:center;">Vacantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empresasTop as $e)
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$e->usuario?->avatar_url" :nombre="$e->nombre_empresa" :tamano="26" />
                                            <span style="font-weight:500;">{{ $e->nombre_empresa }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;"><span class="badge badge-blue">{{ $e->vacantes_periodo }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($empresasTop as $e)
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                        <x-avatar :src="$e->usuario?->avatar_url" :nombre="$e->nombre_empresa" :tamano="40" />
                                        <div style="min-width:0;">
                                            <h3 class="candidate-mobile-card-title">{{ $e->nombre_empresa }}</h3>
                                            <p class="candidate-mobile-card-subtitle">Vacantes del periodo</p>
                                        </div>
                                    </div>
                                    <span class="badge badge-blue">{{ $e->vacantes_periodo }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <h2 style="font-size:1rem; font-weight:700; margin:18px 0 12px; color:var(--text);">Totales del sistema</h2>
    <div class="metrics-grid fade-in" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Empresas</span>
            </div>
            <div class="metric-value">{{ $resumen['empresas_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['empresas_activas'] }} activas / {{ $resumen['empresas_pendientes'] }} pendientes</span>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Candidatos</span>
            </div>
            <div class="metric-value">{{ $resumen['candidatos_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['candidatos_aprobados'] }} aprobados / {{ $resumen['candidatos_pendientes'] }} por revisar</span>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Vacantes</span>
            </div>
            <div class="metric-value">{{ $resumen['solicitudes_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['solicitudes_activas'] }} activas / {{ $resumen['solicitudes_pendientes'] }} pendientes</span>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Tareas</span>
            </div>
            <div class="metric-value">{{ $resumen['tareas_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['tareas_activas'] }} activas o en proceso</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-top:24px;">
        <div class="card fade-in">
            <div class="candidate-inline-meta" style="margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Empresas destacadas</h3>
                <a href="{{ route('admin.empresas') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a empresas</a>
            </div>
            @if($empresasTop->isNotEmpty())
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Estado</th>
                                <th style="text-align:center;">Vacantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empresasTop as $empresa)
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$empresa->usuario?->avatar_url" :nombre="$empresa->nombre_empresa" :tamano="26" />
                                            <span style="font-weight:500;">{{ $empresa->nombre_empresa }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}">{{ \App\Models\Empresa::estadoLabel($empresa->estado) }}</span>
                                    </td>
                                    <td style="text-align:center; color:#64748b;">{{ $empresa->vacantes_periodo ?? $empresa->vacantes_count ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($empresasTop as $empresa)
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                        <x-avatar :src="$empresa->usuario?->avatar_url" :nombre="$empresa->nombre_empresa" :tamano="40" />
                                        <div style="min-width:0;">
                                            <h3 class="candidate-mobile-card-title">{{ $empresa->nombre_empresa }}</h3>
                                            <p class="candidate-mobile-card-subtitle">{{ \App\Models\Empresa::estadoLabel($empresa->estado) }}</p>
                                        </div>
                                    </div>
                                    <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}">{{ $empresa->vacantes_periodo ?? $empresa->vacantes_count ?? 0 }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">Aun no hay empresas con solicitudes.</p>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div class="candidate-inline-meta" style="margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Solicitudes activas</h3>
                <a href="{{ route('admin.vacantes') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a solicitudes</a>
            </div>
            @if($solicitudesActivas->isNotEmpty())
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Empresa</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudesActivas as $solicitud)
                                <tr>
                                    <td style="font-weight:500;">{{ $solicitud->titulo }}</td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$solicitud->empresa?->usuario?->avatar_url" :nombre="$solicitud->empresa?->nombre_empresa ?? '?'" :tamano="24" />
                                            <span>{{ $solicitud->empresa?->nombre_empresa ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($solicitud->nivel_jerarquico) }}</td>
                                    <td><span class="badge {{ \App\Models\Vacante::estadoBadgeClass($solicitud->estado) }}">{{ \App\Models\Vacante::estadoLabel($solicitud->estado) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($solicitudesActivas as $solicitud)
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div>
                                        <h3 class="candidate-mobile-card-title">{{ $solicitud->titulo }}</h3>
                                        <p class="candidate-mobile-card-subtitle">{{ $solicitud->empresa?->nombre_empresa ?? '—' }}</p>
                                    </div>
                                    <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($solicitud->estado) }}">{{ \App\Models\Vacante::estadoLabel($solicitud->estado) }}</span>
                                </div>
                                <div class="candidate-mobile-meta">
                                    <div>
                                        <p class="candidate-mobile-meta-label">Nivel</p>
                                        <p class="candidate-mobile-meta-value">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($solicitud->nivel_jerarquico) }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">No hay solicitudes activas para mostrar.</p>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div class="candidate-inline-meta" style="margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Tareas recientes</h3>
                <a href="{{ route('admin.tareas.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a tareas</a>
            </div>
            @if($tareasRecientes->isNotEmpty())
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Asignado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tareasRecientes as $tarea)
                                <tr>
                                    <td>
                                        {{ $tarea->servicio?->nombre ?? 'Servicio' }}
                                        <div style="font-size:11px;color:#64748b;">{{ $tarea->servicio?->nivel_jerarquico ? \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio->nivel_jerarquico) : 'Sin nivel' }}</div>
                                    </td>
                                    <td>
                                        @if($tarea->asignadoA)
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="24" />
                                                <span>{{ $tarea->asignadoA->name }}</span>
                                            </div>
                                        @else
                                            <span style="color:#94a3b8;">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">{{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($tareasRecientes as $tarea)
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div>
                                        <h3 class="candidate-mobile-card-title">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</h3>
                                        <p class="candidate-mobile-card-subtitle">{{ $tarea->asignadoA?->name ?? 'Sin asignar' }}</p>
                                    </div>
                                    <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">{{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}</span>
                                </div>
                                <div class="candidate-mobile-meta">
                                    <div>
                                        <p class="candidate-mobile-meta-label">Nivel</p>
                                        <p class="candidate-mobile-meta-value">{{ $tarea->servicio?->nivel_jerarquico ? \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio->nivel_jerarquico) : 'Sin nivel' }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">No hay tareas recientes.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
