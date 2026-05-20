<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Mis tareas</span>
        </nav>
        <h1 class="page-title">Mis tareas asignadas</h1>
        <p class="page-subtitle">Lista simple de lo que debo tomar, resolver y cerrar.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    <div class="metrics-grid fade-in">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Activas</span>
                <div class="metric-icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['activas'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Pendientes de tomar</div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">En proceso</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['en_proceso'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Ya tomadas</div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Completadas</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['completadas'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">Cerradas con nota</div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Canceladas</span>
                <div class="metric-icon" style="background:rgba(100,116,139,.12);color:#64748b;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['canceladas'] }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">No procede</div>
        </div>
    </div>

    @php
        $usuario = auth()->user();
        $ocupacion = $usuario->ocupacionPorcentaje();
        $horasLibres = $usuario->capacidad_maxima_horas - $usuario->carga_trabajo_horas;
        $estadoWorkload = $ocupacion < 50 ? 'Muy disponible' : ($ocupacion < 80 ? 'Moderado' : 'Muy ocupado');
        $colorWorkload = $ocupacion < 50 ? '#10b981' : ($ocupacion < 80 ? '#f59e0b' : '#ef4444');
    @endphp
    <div class="card fade-in" style="margin-top:24px; padding:20px; background:linear-gradient(135deg, rgba(59,130,246,0.05), rgba(139,92,246,0.05)); border-left:4px solid var(--accent);">
        <h2 style="margin:0 0 16px; font-size:1rem; font-weight:600;">Mi Capacidad de Trabajo</h2>
        <div class="content-grid-2">
            <div>
                <div style="font-size:0.9rem; color:#64748b; margin-bottom:8px;">Ocupacion</div>
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <div style="font-size:28px; font-weight:bold; color:{{ $colorWorkload }};">{{ round($ocupacion, 1) }}%</div>
                    <div style="font-size:0.85rem; color:{{ $colorWorkload }}; font-weight:500;">{{ $estadoWorkload }}</div>
                </div>
                <div style="width:100%; height:8px; background:var(--surface-2); border-radius:4px; margin-top:8px; overflow:hidden;">
                    <div style="width:{{ min(100, $ocupacion) }}%; height:100%; background:{{ $colorWorkload }}; transition:width 0.3s;"></div>
                </div>
                <div style="font-size:0.8rem; color:#94a3b8; margin-top:4px;">
                    {{ $usuario->carga_trabajo_horas }}/{{ $usuario->capacidad_maxima_horas }} horas
                </div>
            </div>
            <div>
                <div style="padding:12px; background:var(--surface-2); border-radius:8px; margin-bottom:8px;">
                    <div style="font-size:0.8rem; color:#64748b; margin-bottom:4px;">Horas disponibles</div>
                    <div style="font-size:24px; font-weight:bold; color:#10b981;">{{ $horasLibres }} h</div>
                </div>
                <div style="padding:12px; background:var(--surface-2); border-radius:8px;">
                    <div style="font-size:0.8rem; color:#64748b; margin-bottom:4px;">Tareas activas</div>
                    <div style="font-size:24px; font-weight:bold; color:#3b82f6;">{{ $stats['activas'] + $stats['en_proceso'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fade-in" style="margin-top:24px;">
        <form method="GET" class="form-inline" style="align-items:flex-end; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px;">Estado</label>
                <select name="estado" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    <option value="">Todos</option>
                    @foreach(\App\Models\ServicioAsignado::estados() as $key => $label)
                        <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->has('estado'))
                <a href="{{ route('interno.tareas.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        @if($tareas->isEmpty())
            <x-estado-vacio
                icono="🎯"
                titulo="No tienes tareas asignadas"
                mensaje="Cuando el administrador te asigne un pedido aparecera aqui. Mientras tanto, revisa que tus capacidades esten al dia para que te lleguen las tareas correctas." />
        @else
            <div class="desktop-only table-scroll">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Servicio</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Objetivo</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Estado</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Asignada</th>
                            <th style="text-align:right; padding:10px 12px; color:var(--text-muted); font-weight:500;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareas as $tarea)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:12px;">
                                    <div style="font-weight:600;">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</div>
                                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</div>
                                </td>
                                <td style="padding:12px;">
                                    @php
                                        $avatarSolicitante = match($tarea->asignable_type) {
                                            \App\Models\Empresa::class, \App\Models\Candidato::class => $tarea->asignable?->usuario?->avatar_url,
                                            \App\Models\User::class => $tarea->asignable?->avatar_url,
                                            default => null,
                                        };
                                    @endphp
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <x-avatar :src="$avatarSolicitante" :nombre="$tarea->asignableNombre()" :tamano="28" />
                                        <div>
                                            <div style="font-size:13px;">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }}</div>
                                            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $tarea->asignableNombre() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:12px;">
                                    <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                        {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                    </span>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">
                                    {{ $tarea->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td style="padding:12px; text-align:right;">
                                    <div style="display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
                                        @if($tarea->estado === 'activo')
                                            <button type="button" class="btn btn-primary" style="padding:5px 12px; font-size:0.78rem;" onclick="rhModal('{{ route('interno.tareas.tomar.modal', $tarea) }}')">
                                                Tomar
                                            </button>
                                        @endif
                                        <a href="{{ route('interno.tareas.show', $tarea) }}" class="btn btn-secondary" style="padding:5px 12px; font-size:0.78rem;">Abrir</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($tareas as $tarea)
                        @php
                            $avatarSolicitante = match($tarea->asignable_type) {
                                \App\Models\Empresa::class, \App\Models\Candidato::class => $tarea->asignable?->usuario?->avatar_url,
                                \App\Models\User::class => $tarea->asignable?->avatar_url,
                                default => null,
                            };
                        @endphp
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div>
                                    <h3 class="candidate-mobile-card-title">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</h3>
                                    <p class="candidate-mobile-card-subtitle">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</p>
                                </div>
                                <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                    {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                </span>
                            </div>

                            <div style="display:flex; align-items:center; gap:10px; margin-top:12px;">
                                <x-avatar :src="$avatarSolicitante" :nombre="$tarea->asignableNombre()" :tamano="32" />
                                <div>
                                    <p class="candidate-mobile-meta-label" style="margin-bottom:2px;">Objetivo</p>
                                    <p class="candidate-mobile-card-subtitle">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
                                </div>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Asignada</p>
                                    <p class="candidate-mobile-meta-value">{{ $tarea->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="candidate-actions" style="margin-top:14px;">
                                @if($tarea->estado === 'activo')
                                    <button type="button" class="btn btn-primary btn-sm" onclick="rhModal('{{ route('interno.tareas.tomar.modal', $tarea) }}')">
                                        Tomar
                                    </button>
                                @endif
                                <a href="{{ route('interno.tareas.show', $tarea) }}" class="btn btn-secondary btn-sm">Abrir</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:16px;">{{ $tareas->links() }}</div>
        @endif
    </div>
</x-app-layout>
