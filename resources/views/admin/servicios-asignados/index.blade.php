<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <div>
                <nav class="breadcrumbs">
                    <a href="{{ route('admin.dashboard') }}">Administración</a>
                    <span class="breadcrumb-sep">›</span>
                    <span>Tareas de servicio</span>
                </nav>
                <h1 class="page-title">Tareas de servicio</h1>
                <p class="page-subtitle">{{ $tareas->total() }} tarea(s) registradas.</p>
            </div>
            <a href="{{ route('admin.tareas.crear') }}" class="btn btn-primary">+ Asignar servicio</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
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
            <div class="metric-change" style="color:#64748b;font-size:12px;">Pendientes de toma</div>
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

    <div class="card fade-in" style="margin-top:24px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:18px;">
            <div style="flex:1; min-width:220px;">
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px;">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Servicio, interno o asignado..."
                    style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
            </div>
            <div>
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px;">Estado</label>
                <select name="estado" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    <option value="">Todos</option>
                    @foreach(\App\Models\ServicioAsignado::estados() as $key => $label)
                        <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:12px; color:var(--text-muted); margin-bottom:4px;">Jerarquía</label>
                <select name="nivel" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                    <option value="">Todas</option>
                    @foreach(\App\Models\CatalogoServicio::nivelesJerarquicosFormulario(true) as $key => $label)
                        <option value="{{ $key }}" {{ request('nivel') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" style="padding:8px 16px; background: var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px;">Filtrar</button>
            @if(request()->hasAny(['buscar', 'estado', 'nivel']))
                <a href="{{ route('admin.tareas.index') }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;">Limpiar</a>
            @endif
        </form>

        @if($tareas->isEmpty())
            <p class="text-muted text-sm" style="text-align:center; padding:40px 0;">No hay tareas registradas.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border);">
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Servicio</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Objetivo</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Interno</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Estado</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Creada</th>
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
                                    <div style="font-size:13px;">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }}</div>
                                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $tarea->asignableNombre() }}</div>
                                </td>
                                <td style="padding:12px; font-size:13px;">{{ $tarea->asignadoA?->name ?? '—' }}</td>
                                <td style="padding:12px;">
                                    <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                        {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                    </span>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">{{ $tarea->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td style="padding:12px; text-align:right;">
                                    <div style="display:flex; gap:6px; justify-content:flex-end; align-items:center;">
                                        <a href="{{ route('admin.tareas.show', $tarea) }}" style="font-size:12px; color: var(--accent); text-decoration:none; font-weight:500;">Ver →</a>
                                        <a href="{{ route('admin.tareas.editar', $tarea) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:12px;">Editar</a>
                                        <form method="POST" action="{{ route('admin.tareas.eliminar', $tarea) }}" onsubmit="return confirm('¿Eliminar esta tarea permanentemente?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:12px;" title="Eliminar">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $tareas->links() }}</div>
        @endif
    </div>
</x-app-layout>
