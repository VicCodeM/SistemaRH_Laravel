<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Mi panel</span>
        </nav>
        <h1 class="page-title">Mi panel de trabajo</h1>
        <p class="page-subtitle">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }} &mdash; Tus tareas y carga de trabajo.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    @isset($acciones)
        <x-acciones-pendientes titulo="Que sigue?" :acciones="$acciones" />
    @endisset

    {{-- Tarjetas de MIS tareas --}}
    <div class="metrics-grid fade-in">
        <div class="metric-card" style="{{ $stats['tareas_por_tomar'] > 0 ? 'border-color: rgba(245,158,11,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Por tomar</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tareas_por_tomar'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Asignadas, esperando que las tomes</div>
        </div>

        <div class="metric-card" style="{{ $stats['tareas_en_proceso'] > 0 ? 'border-color: rgba(14,165,233,.35);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">En proceso</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tareas_en_proceso'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Trabajando activamente</div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Completadas</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tareas_completadas'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Total historico</div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">En el sistema</span>
                <div class="metric-icon" style="background:rgba(167,139,250,.12);color:#a78bfa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_activas_sistema'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Solicitudes activas en general</div>
        </div>
    </div>

    {{-- Barra de carga de trabajo --}}
    @php
        $totalActivas = $stats['tareas_por_tomar'] + $stats['tareas_en_proceso'];
        $capacidad = 10; // referencia visual
        $porcentaje = $capacidad > 0 ? min(100, round(($totalActivas / $capacidad) * 100)) : 0;
        $colorBarra = $porcentaje >= 80 ? '#ef4444' : ($porcentaje >= 50 ? '#f59e0b' : '#22c55e');
    @endphp
    <div class="card fade-in" style="margin-top:16px; padding:16px 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-weight:600; font-size:14px;">Carga de trabajo</span>
            <span style="font-size:13px; color:#64748b;">{{ $totalActivas }} tarea{{ $totalActivas !== 1 ? 's' : '' }} activa{{ $totalActivas !== 1 ? 's' : '' }}</span>
        </div>
        <div style="background:var(--border); border-radius:8px; height:10px; overflow:hidden;">
            <div style="background:{{ $colorBarra }}; height:100%; border-radius:8px; width:{{ $porcentaje }}%; transition:width .5s ease;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:11px; color:#94a3b8;">
            <span>{{ $porcentaje >= 80 ? 'Carga alta' : ($porcentaje >= 50 ? 'Carga media' : 'Disponible') }}</span>
            <span>{{ $porcentaje }}%</span>
        </div>
    </div>

    <div class="candidate-actions" style="margin-top:16px;">
        <a href="{{ route('interno.tareas.index') }}" class="btn btn-primary">Mis tareas</a>
        <a href="{{ route('chat.index') }}" class="btn btn-secondary">Abrir chat</a>
    </div>

    {{-- Tabla de tareas recientes --}}
    <div class="card fade-in" style="margin-top:24px;">
        <div class="candidate-inline-meta" style="margin-bottom:16px;">
            <h3 style="font-weight:700; margin:0; font-size:1rem;">Mis tareas asignadas</h3>
            <a href="{{ route('interno.tareas.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Ver todas &rarr;</a>
        </div>

        @if($tareas_recientes->isEmpty())
            <div style="text-align:center; padding:36px 0; color:#64748b;">
                No tienes tareas asignadas todavia.
            </div>
        @else
            <div class="desktop-only table-scroll">
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
                                    <a href="{{ route('interno.tareas.show', $tarea) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Abrir &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($tareas_recientes as $tarea)
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div>
                                    <h4 class="candidate-mobile-card-title">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</h4>
                                    <p class="candidate-mobile-card-subtitle">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</p>
                                </div>
                                <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                    {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Objetivo</p>
                                    <p class="candidate-mobile-meta-value">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Asignada</p>
                                    <p class="candidate-mobile-meta-value">{{ $tarea->created_at?->diffForHumans() ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="toolbar-wrap mt-4">
                                <a href="{{ route('interno.tareas.show', $tarea) }}" class="btn btn-secondary btn-sm">Abrir</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
