<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">›</span>
            <span>Personal interno</span>
        </nav>
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Personal interno</h1>
                <p class="page-subtitle">Colaboradores del equipo que ejecutan servicios y atienden tickets.</p>
            </div>
            <a href="{{ route('admin.personal-interno.crear') }}" class="btn btn-primary">+ Nuevo interno</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="metrics-grid" style="margin-bottom:24px;">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Total</span>
                <div class="metric-icon" style="background:rgba(37,99,235,.1);color:var(--accent);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['total'] }}</div>
            <span class="metric-change text-muted">Personal registrado</span>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Activos</span>
                <div class="metric-icon" style="background:rgba(16,185,129,.1);color:var(--success);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="metric-value" style="color:var(--success);">{{ $stats['activos'] }}</div>
            <span class="metric-change text-muted">Con acceso habilitado</span>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Tareas abiertas</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.1);color:var(--warning);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375"/></svg>
                </div>
            </div>
            <div class="metric-value" style="color:var(--warning);">{{ $stats['tareas_abiertas'] }}</div>
            <span class="metric-change text-muted">Activas y en proceso</span>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
        <select name="estado" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface);">
            <option value="">Todos los estados</option>
            <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activos</option>
            <option value="bloqueado" {{ request('estado') === 'bloqueado' ? 'selected' : '' }}>Bloqueados</option>
        </select>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o email..."
               style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); width:220px;">
        <button type="submit" class="btn btn-secondary" style="padding:8px 14px; font-size:13px;">Buscar</button>
        @if(request('buscar') || request('estado'))
            <a href="{{ route('admin.personal-interno.index') }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;">✕ Limpiar</a>
        @endif
    </form>

    {{-- Tabla --}}
    <div class="table-wrapper">
        @if($internos->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                No hay personal interno que coincida con los filtros.
                <br><a href="{{ route('admin.personal-interno.crear') }}" class="btn btn-primary" style="margin-top:16px; display:inline-block;">Agregar el primero</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Tareas activas</th>
                        <th style="text-align:center;">Completadas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($internos as $interno)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px;height:34px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(substr($interno->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight:600;">{{ $interno->name }}</span>
                                </div>
                            </td>
                            <td style="font-size:13px; color:#64748b;">{{ $interno->email }}</td>
                            <td>
                                <span class="badge badge-{{ $interno->estado === 'activo' ? 'success' : 'danger' }}">
                                    {{ $interno->estado === 'activo' ? 'Activo' : 'Bloqueado' }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($interno->tareas_activas > 0)
                                    <span style="background:var(--accent-light);color:var(--accent);border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">{{ $interno->tareas_activas }}</span>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center; color:#64748b; font-size:13px;">{{ $interno->tareas_completadas }}</td>
                            <td>
                                <div style="display:flex; gap:6px; align-items:center; justify-content:flex-end;">
                                    <button onclick="rhModal('{{ route('admin.personal-interno.modal', $interno) }}')"
                                            title="Ver detalle" class="btn btn-ghost" style="width:30px;height:30px;padding:0;">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.personal-interno.estado', $interno) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $interno->estado === 'activo' ? 'Bloquear' : 'Activar' }}"
                                                class="btn btn-ghost" style="width:30px;height:30px;padding:0;color:{{ $interno->estado === 'activo' ? '#f59e0b' : '#22c55e' }};">
                                            @if($interno->estado === 'activo')
                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">
                {{ $internos->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
