<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Reportes</span>
        </nav>
        <h1 class="page-title">Reportes</h1>
        <p class="page-subtitle">Indicadores rápidos: aprobaciones de acceso (empresas, candidatos) y operaciones de servicio (vacantes, tareas, tickets).</p>
    </x-slot>

    <div class="card fade-in" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 6px;font-size:1rem;font-weight:700;">Accesos rápidos</h3>
                <p style="margin:0;color:#64748b;font-size:0.9rem;">Atajos directos para revisar lo que más importa sin navegar de más.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('admin.empresas', ['estado' => 'pendiente']) }}" class="btn btn-secondary">Empresas pendientes</a>
                <a href="{{ route('admin.candidatos', ['estado' => 'enviada']) }}" class="btn btn-secondary">Candidatos por revisar</a>
                <a href="{{ route('admin.vacantes', ['estado' => 'activa']) }}" class="btn btn-secondary">Solicitudes activas</a>
                <a href="{{ route('tickets.index', ['estado' => 'abierto']) }}" class="btn btn-secondary">Tickets abiertos</a>
                <a href="{{ route('admin.reportes.exportar') }}" class="btn btn-primary">📥 Exportar CSV</a>
            </div>
        </div>
    </div>

    <div class="metrics-grid fade-in">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Empresas</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['empresas_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['empresas_activas'] }} activas / {{ $resumen['empresas_pendientes'] }} pendientes</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Candidatos</span>
                <div class="metric-icon" style="background:rgba(96,165,250,.12);color:#60a5fa;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['candidatos_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['candidatos_aprobados'] }} aprobados / {{ $resumen['candidatos_pendientes'] }} por revisar</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Solicitudes de servicio</span>
                <div class="metric-icon" style="background:rgba(167,139,250,.12);color:#a78bfa;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['solicitudes_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['solicitudes_activas'] }} activas / {{ $resumen['solicitudes_pendientes'] }} pendientes</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Tareas</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['tareas_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['tareas_activas'] }} activas o en proceso</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Tickets</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['tickets_total'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">{{ $resumen['tickets_abiertos'] }} abiertos / {{ $resumen['tickets_vencidos'] }} vencidos</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Control</span>
                <div class="metric-icon" style="background:rgba(239,68,68,.12);color:#ef4444;"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></div>
            </div>
            <div class="metric-value">{{ $resumen['tickets_vencidos'] }}</div>
            <span class="metric-change" style="font-size:12px;color:#64748b;">Casos que requieren atención inmediata</span>
        </div>
    </div>

    {{-- Gráficas de tendencias --}}
    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin-top:24px;">
        <div class="card fade-in">
            <h3 style="font-weight:700;margin:0 0 14px;font-size:0.95rem;">Vacantes creadas (6 meses)</h3>
            <div style="position:relative; height:220px;">
                <canvas id="graficaVacantes"></canvas>
            </div>
        </div>
        <div class="card fade-in">
            <h3 style="font-weight:700;margin:0 0 14px;font-size:0.95rem;">Postulaciones recibidas (6 meses)</h3>
            <div style="position:relative; height:220px;">
                <canvas id="graficaPostulaciones"></canvas>
            </div>
        </div>
        <div class="card fade-in">
            <h3 style="font-weight:700;margin:0 0 14px;font-size:0.95rem;">Tickets creados (6 meses)</h3>
            <div style="position:relative; height:220px;">
                <canvas id="graficaTickets"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (window.__reportesCharts) {
                window.__reportesCharts.forEach(function (c) { c.destroy(); });
            }
            window.__reportesCharts = [];

            var commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            };

            window.__reportesCharts.push(new Chart(document.getElementById('graficaVacantes'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($graficaVacantes->pluck('label')) !!},
                    datasets: [{
                        label: 'Vacantes',
                        data: {!! json_encode($graficaVacantes->pluck('valor')) !!},
                        backgroundColor: 'rgba(167,139,250,0.7)',
                        borderRadius: 6
                    }]
                },
                options: commonOptions
            }));

            window.__reportesCharts.push(new Chart(document.getElementById('graficaPostulaciones'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($graficaPostulaciones->pluck('label')) !!},
                    datasets: [{
                        label: 'Postulaciones',
                        data: {!! json_encode($graficaPostulaciones->pluck('valor')) !!},
                        borderColor: 'rgba(96,165,250,1)',
                        backgroundColor: 'rgba(96,165,250,0.15)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4
                    }]
                },
                options: commonOptions
            }));

            window.__reportesCharts.push(new Chart(document.getElementById('graficaTickets'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($graficaTickets->pluck('label')) !!},
                    datasets: [{
                        label: 'Tickets',
                        data: {!! json_encode($graficaTickets->pluck('valor')) !!},
                        backgroundColor: 'rgba(34,197,94,0.7)',
                        borderRadius: 6
                    }]
                },
                options: commonOptions
            }));
        })();
    </script>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin-top:24px;">
        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Empresas con más solicitudes</h3>
                <a href="{{ route('admin.empresas') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a empresas &rarr;</a>
            </div>

            @if($empresasTop->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Empresa</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Estado</th>
                                <th style="text-align:center;padding:8px 10px;color:#475569;font-weight:500;">Solicitudes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empresasTop as $empresa)
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:9px 10px;font-weight:500;">{{ $empresa->nombre_empresa }}</td>
                                    <td style="padding:9px 10px;">
                                        <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}">
                                            {{ \App\Models\Empresa::estadoLabel($empresa->estado) }}
                                        </span>
                                    </td>
                                    <td style="padding:9px 10px;text-align:center;color:#64748b;">{{ $empresa->vacantes_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">Aún no hay empresas con solicitudes.</p>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Solicitudes activas</h3>
                <a href="{{ route('admin.vacantes') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a solicitudes &rarr;</a>
            </div>

            @if($solicitudesActivas->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Solicitud</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Empresa</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Nivel</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudesActivas as $solicitud)
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:9px 10px;font-weight:500;">{{ $solicitud->titulo }}</td>
                                    <td style="padding:9px 10px;color:#64748b;">{{ $solicitud->empresa?->nombre_empresa ?? '—' }}</td>
                                    <td style="padding:9px 10px;color:#64748b;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($solicitud->nivel_jerarquico) }}</td>
                                    <td style="padding:9px 10px;">
                                        <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($solicitud->estado) }}">
                                            {{ \App\Models\Vacante::estadoLabel($solicitud->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">No hay solicitudes activas para mostrar.</p>
                </div>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin-top:24px;">
        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Tickets recientes</h3>
                <a href="{{ route('tickets.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a soporte &rarr;</a>
            </div>

            @if($ticketsRecientes->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Asunto</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Estado</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Prioridad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticketsRecientes as $ticket)
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:9px 10px;font-weight:500;">
                                        {{ $ticket->asunto }}
                                        <div style="font-size:11px;color:#64748b;">{{ $ticket->empresa?->nombre_empresa ?? 'Sin empresa' }}</div>
                                    </td>
                                    <td style="padding:9px 10px;">
                                        <span class="badge {{ \App\Models\Ticket::estadoBadgeClass($ticket->estado) }}">
                                            {{ \App\Models\Ticket::estadoLabel($ticket->estado) }}
                                        </span>
                                    </td>
                                    <td style="padding:9px 10px;">
                                        <span class="badge {{ \App\Models\Ticket::prioridadBadgeClass($ticket->prioridad) }}">
                                            {{ \App\Models\Ticket::prioridadLabel($ticket->prioridad) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">No hay tickets recientes.</p>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Tareas recientes</h3>
                <a href="{{ route('admin.tareas.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ir a tareas &rarr;</a>
            </div>

            @if($tareasRecientes->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Servicio</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Asignado</th>
                                <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tareasRecientes as $tarea)
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:9px 10px;font-weight:500;">
                                        {{ $tarea->servicio?->nombre ?? 'Servicio' }}
                                        <div style="font-size:11px;color:#64748b;">{{ $tarea->servicio?->nivel_jerarquico ? \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio->nivel_jerarquico) : 'Sin nivel' }}</div>
                                    </td>
                                    <td style="padding:9px 10px;color:#64748b;">{{ $tarea->asignadoA?->name ?? 'Sin asignar' }}</td>
                                    <td style="padding:9px 10px;">
                                        <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                            {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:#475569;">
                    <p style="margin:0;font-size:13px;">No hay tareas recientes.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
