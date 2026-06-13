<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Empresa</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Panel</span>
        </nav>
        <div class="toolbar-wrap" style="gap:12px;">
            <h1 class="page-title" style="margin:0;">{{ $empresa->nombre_empresa }}</h1>
            <span class="badge {{ \App\Models\Empresa::estadoBadgeClass($empresa->estado) }}">
                {{ \App\Models\Empresa::estadoLabel($empresa->estado) }}
            </span>
        </div>
        <p class="page-subtitle">Resumen operativo de solicitudes, candidatos y seguimiento.</p>
    </x-slot>

    @isset($acciones)
        <x-acciones-pendientes titulo="Que sigue?" :acciones="$acciones" />
    @endisset

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--warning-light); color:var(--warning); border-radius:8px; border-left:4px solid var(--warning);">
            {{ session('warning') }}
        </div>
    @endif

    <div class="metrics-grid fade-in">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Solicitudes activas</span>
                <div class="metric-icon" style="background:var(--success-light); color:var(--success);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_activas'] }}</div>
            <a href="{{ route('empresa.solicitudes') }}" class="metric-change" style="color:var(--success); text-decoration:none; font-size:12px;">Ver solicitudes &rarr;</a>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Solicitudes en revision</span>
                <div class="metric-icon" style="background:var(--warning-light); color:var(--warning);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_pendientes'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">Esperando respuesta del admin</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Personal contratado</span>
                <div class="metric-icon" style="background:var(--accent-light); color:var(--accent);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['contratados'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">Total en todos tus servicios</span>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Procesos activos</span>
                <div class="metric-icon" style="background:var(--danger-light); color:var(--danger);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['en_proceso'] }}</div>
            <span class="metric-change" style="font-size:12px; color:#64748b;">Seguimiento RH</span>
        </div>
    </div>

    @php
        $ultimaSolicitud = $solicitudes_recientes->first();
    @endphp

    <div class="card fade-in" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap;">
            <div>
                <h3 style="font-weight:700; margin:0 0 4px; font-size:1rem;">Estado de tu solicitud más reciente</h3>
                <p style="margin:0; color:#64748b; font-size:13px;">
                    Así puedes revisar el avance sin abrir el detalle.
                </p>
            </div>

            @if($ultimaSolicitud)
                <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($ultimaSolicitud->estado) }}">
                    {{ \App\Models\Vacante::estadoLabel($ultimaSolicitud->estado) }}
                </span>
            @endif
        </div>

        @if($ultimaSolicitud)
            <div style="display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; margin-top:16px; padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                <div>
                    <div style="font-weight:600;">{{ $ultimaSolicitud->titulo }}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">
                        {{ \App\Models\Vacante::tiposServicio()[$ultimaSolicitud->tipo_servicio] ?? '—' }}
                        @if($ultimaSolicitud->fecha_publicacion)
                            · {{ $ultimaSolicitud->fecha_publicacion->format('d/m/Y') }}
                        @endif
                    </div>
                </div>
                <a href="{{ route('empresa.solicitudes.ver', $ultimaSolicitud) }}" class="btn btn-secondary btn-sm">Ver detalle</a>
            </div>
        @else
            <div style="margin-top:16px; padding:16px; border:1px dashed var(--border); border-radius:10px; color:#64748b; font-size:13px;">
                Todavía no has publicado solicitudes. Cuando crees una, aquí verás su estatus.
            </div>
        @endif
    </div>

    <div class="card fade-in" style="margin-top:24px;">
        <div class="candidate-inline-meta" style="margin-bottom:16px;">
            <h3 style="font-weight:600; margin:0;">Solicitudes recientes</h3>
            <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary btn-sm">+ Nueva solicitud</a>
        </div>

        @php
            $tipos = \App\Models\Vacante::tiposServicio();
        @endphp

        @if($solicitudes_recientes->isEmpty())
            <div style="text-align:center; padding:40px 0;">
                <p style="color:#64748b; font-size:0.9rem;">Aun no has enviado ninguna solicitud.</p>
                <a href="{{ route('empresa.solicitudes.crear') }}" class="btn btn-primary" style="margin-top:12px;">Hacer tu primera solicitud</a>
            </div>
        @else
            <div class="desktop-only table-scroll">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left; padding:10px 8px; color:var(--text-muted); font-weight:500;">Solicitud</th>
                            <th style="text-align:left; padding:10px 8px; color:var(--text-muted); font-weight:500;">Tipo</th>
                            <th style="text-align:center; padding:10px 8px; color:var(--text-muted); font-weight:500;">Contratados</th>
                            <th style="text-align:left; padding:10px 8px; color:var(--text-muted); font-weight:500;">Estado</th>
                            <th style="text-align:right; padding:10px 8px; color:var(--text-muted); font-weight:500;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes_recientes as $sol)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px 8px; font-weight:500;">{{ $sol->titulo }}</td>
                                <td style="padding:10px 8px; font-size:0.8rem; color:#94a3b8;">{{ $tipos[$sol->tipo_servicio] ?? '—' }}</td>
                                <td style="padding:10px 8px; text-align:center; font-weight:600;">{{ $sol->postulaciones_count }}</td>
                                <td style="padding:10px 8px;">
                                    <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($sol->estado) }}" style="font-size:12px;">
                                        {{ \App\Models\Vacante::estadoLabel($sol->estado) }}
                                    </span>
                                </td>
                                <td style="padding:10px 8px; text-align:right;">
                                    <a href="{{ route('empresa.solicitudes.ver', $sol) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Ver &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($solicitudes_recientes as $sol)
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div>
                                    <h4 class="candidate-mobile-card-title">{{ $sol->titulo }}</h4>
                                    <p class="candidate-mobile-card-subtitle">{{ $tipos[$sol->tipo_servicio] ?? '—' }}</p>
                                </div>
                                <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($sol->estado) }}">
                                    {{ \App\Models\Vacante::estadoLabel($sol->estado) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Contratados</p>
                                    <p class="candidate-mobile-meta-value">{{ $sol->postulaciones_count }}</p>
                                </div>
                            </div>

                            <div class="toolbar-wrap mt-4">
                                <a href="{{ route('empresa.solicitudes.ver', $sol) }}" class="btn btn-secondary btn-sm">Ver solicitud</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:12px; text-align:right;">
                <a href="{{ route('empresa.solicitudes') }}" style="font-size:13px; color:var(--accent); text-decoration:none;">Ver todas mis solicitudes &rarr;</a>
            </div>
        @endif
    </div>
</x-app-layout>
