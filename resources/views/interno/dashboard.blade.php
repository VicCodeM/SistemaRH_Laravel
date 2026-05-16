<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Panel interno</span>
        </nav>
        <h1 class="page-title">Panel de operaciones</h1>
        <p class="page-subtitle">Resumen diario para atender solicitudes, tickets y seguimiento operativo.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    <div class="metrics-grid fade-in">
        <div class="metric-card" style="{{ $stats['empresas_pendientes'] > 0 ? 'border-color: rgba(245,158,11,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Empresas por revisar</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['empresas_pendientes'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Pendientes de aprobación</div>
        </div>

        <div class="metric-card" style="{{ $stats['candidatos_pendientes'] > 0 ? 'border-color: rgba(96,165,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Candidatos por revisar</span>
                <div class="metric-icon" style="background:rgba(96,165,250,.12);color:#60a5fa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['candidatos_pendientes'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Solicitudes enviadas</div>
        </div>

        <div class="metric-card" style="{{ $stats['solicitudes_pendientes'] > 0 ? 'border-color: rgba(167,139,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Solicitudes por activar</span>
                <div class="metric-icon" style="background:rgba(167,139,250,.12);color:#a78bfa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_pendientes'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">En revisión interna</div>
        </div>

        <div class="metric-card" style="{{ $stats['tickets_abiertos'] > 0 ? 'border-color: rgba(34,197,94,.35);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Tickets por atender</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tickets_abiertos'] }}</div>
            <div class="metric-change" style="color:{{ $stats['tickets_vencidos'] > 0 ? 'var(--danger)' : '#64748b' }}; font-size:12px;">
                {{ $stats['tickets_vencidos'] }} vencido(s)
            </div>
        </div>

        <div class="metric-card" style="{{ $stats['tareas_activas'] > 0 ? 'border-color: rgba(14,165,233,.35);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Mis tareas</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tareas_activas'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">
                {{ $stats['tareas_completadas'] }} completadas
            </div>
        </div>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:18px;">
        <a href="{{ route('tickets.index') }}" class="btn btn-primary">Ver tickets</a>
        <a href="{{ route('interno.tareas.index') }}" class="btn btn-secondary">Mis tareas</a>
        <a href="{{ route('chat.index') }}" class="btn btn-secondary">Abrir chat</a>
    </div>

    <div style="display:grid; grid-template-columns: 1.1fr .9fr; gap:24px; margin-top:24px;">
        <div class="card fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-weight:700; margin:0; font-size:1rem;">Tickets por atender</h3>
                <a href="{{ route('tickets.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Ver todos →</a>
            </div>

            @if($tickets_recientes->isEmpty())
                <div style="text-align:center; padding:36px 0; color:#64748b;">
                    Sin tickets abiertos por el momento.
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Asunto</th>
                                <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Empresa</th>
                                <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Prioridad</th>
                                <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">SLA</th>
                                <th style="text-align:right; padding:8px 10px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_recientes as $ticket)
                                @php
                                    $prioridadColors = [
                                        'baja' => ['#64748b', 'rgba(100,116,139,.12)'],
                                        'media' => ['#0f766e', 'rgba(15,118,110,.12)'],
                                        'alta' => ['#d97706', 'rgba(245,158,11,.12)'],
                                        'urgente' => ['#dc2626', 'rgba(220,38,38,.12)'],
                                    ];
                                    [$color, $bg] = $prioridadColors[$ticket->prioridad] ?? ['#64748b', 'rgba(100,116,139,.12)'];
                                @endphp
                                <tr style="border-bottom:1px solid var(--border); {{ $ticket->estaVencido() ? 'background: rgba(220,38,38,.05);' : '' }}">
                                    <td style="padding:9px 10px;">
                                        <div style="font-weight:600;">{{ $ticket->asunto }}</div>
                                        <div style="font-size:11px; color:#64748b;">{{ \App\Models\Ticket::categoriaLabel($ticket->categoria) }}</div>
                                    </td>
                                    <td style="padding:9px 10px; color:#94a3b8;">
                                        {{ $ticket->empresa?->nombre_empresa ?? '—' }}
                                    </td>
                                    <td style="padding:9px 10px;">
                                        <span style="padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; color:{{ $color }}; background:{{ $bg }};">
                                            {{ \App\Models\Ticket::prioridadLabel($ticket->prioridad) }}
                                        </span>
                                    </td>
                                    <td style="padding:9px 10px; color:{{ $ticket->estaVencido() ? 'var(--danger)' : '#64748b' }}; font-size:12px;">
                                        @if($ticket->sla_due_at && !in_array($ticket->estado, ['resuelto', 'cerrado'], true))
                                            {{ $ticket->estaVencido() ? 'Vencido' : $ticket->sla_due_at->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="padding:9px 10px; text-align:right;">
                                        <a href="{{ route('tickets.show', $ticket) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Abrir →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-weight:700; margin:0; font-size:1rem;">Solicitudes recientes</h3>
                <span style="font-size:12px; color:#64748b;">{{ $stats['solicitudes_activas'] }} activas</span>
            </div>

            @if($solicitudes_recientes->isEmpty())
                <div style="text-align:center; padding:36px 0; color:#64748b;">
                    No hay solicitudes recientes.
                </div>
            @else
                <div style="display:grid; gap:10px;">
                    @foreach($solicitudes_recientes as $solicitud)
                        <div style="padding:12px 14px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                            <div style="display:flex; justify-content:space-between; gap:12px;">
                                <div style="min-width:0;">
                                    <div style="font-weight:600; margin-bottom:2px;">{{ $solicitud->titulo }}</div>
                                    <div style="font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $solicitud->empresa?->nombre_empresa ?? 'Empresa' }}
                                    </div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-size:12px; color:#94a3b8;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($solicitud->nivel_jerarquico) }}</div>
                                    <div style="margin-top:4px;">
                                        <span style="padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; background:{{ $solicitud->estado === 'activa' ? 'rgba(34,197,94,.12)' : 'rgba(245,158,11,.12)' }}; color:{{ $solicitud->estado === 'activa' ? '#22c55e' : '#f59e0b' }};">
                                            {{ $solicitud->estado === 'activa' ? 'Activa' : 'En revisión' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card fade-in" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="font-weight:700; margin:0; font-size:1rem;">Mis tareas asignadas</h3>
            <a href="{{ route('interno.tareas.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Ver todas →</a>
        </div>

        @if($tareas_recientes->isEmpty())
            <div style="text-align:center; padding:36px 0; color:#64748b;">
                No tienes tareas asignadas todavía.
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Servicio</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Objetivo</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Estado</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Asignada</th>
                            <th style="text-align:right; padding:8px 10px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareas_recientes as $tarea)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:9px 10px;">
                                    <div style="font-weight:600;">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</div>
                                    <div style="font-size:11px; color:#64748b;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</div>
                                </td>
                                <td style="padding:9px 10px; color:#94a3b8;">
                                    {{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}
                                </td>
                                <td style="padding:9px 10px;">
                                    <span style="padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; background:{{ match($tarea->estado) { 'activo' => 'rgba(59,130,246,.12)', 'en_proceso' => 'rgba(245,158,11,.12)', 'completado' => 'rgba(34,197,94,.12)', default => 'rgba(100,116,139,.12)' } }}; color:{{ match($tarea->estado) { 'activo' => '#3b82f6', 'en_proceso' => '#d97706', 'completado' => '#22c55e', default => '#64748b' } }};">
                                        {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                    </span>
                                </td>
                                <td style="padding:9px 10px; color:#64748b;">
                                    {{ $tarea->created_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td style="padding:9px 10px; text-align:right;">
                                    <a href="{{ route('interno.tareas.show', $tarea) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Abrir →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
