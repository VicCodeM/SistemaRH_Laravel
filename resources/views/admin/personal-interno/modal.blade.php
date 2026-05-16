<div style="padding:28px;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;flex-shrink:0;">
            {{ strtoupper(substr($interno->name, 0, 1)) }}
        </div>
        <div>
            <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ $interno->name }}</h2>
            <p style="margin:3px 0 0;font-size:0.85rem;color:#64748b;">{{ $interno->email }}</p>
        </div>
        <div style="margin-left:auto;">
            <span class="badge badge-{{ $interno->estado === 'activo' ? 'success' : 'danger' }}">
                {{ $interno->estado === 'activo' ? 'Activo' : 'Bloqueado' }}
            </span>
        </div>
    </div>

    {{-- Métricas --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px;">
        <div style="padding:14px; border-radius:10px; background:var(--accent-light); text-align:center;">
            <div style="font-size:1.6rem; font-weight:700; color:var(--accent);">{{ $interno->tareas_activas }}</div>
            <div style="font-size:12px; color:#64748b; margin-top:2px;">Tareas activas</div>
        </div>
        <div style="padding:14px; border-radius:10px; background:#f0fdf4; text-align:center;">
            <div style="font-size:1.6rem; font-weight:700; color:#16a34a;">{{ $interno->tareas_completadas }}</div>
            <div style="font-size:12px; color:#64748b; margin-top:2px;">Completadas</div>
        </div>
    </div>

    {{-- Tareas recientes --}}
    @if($tareas->isNotEmpty())
        <div style="margin-bottom:20px;">
            <p style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin:0 0 10px;">Tareas en curso</p>
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($tareas as $tarea)
                    <div style="padding:10px 14px; border-radius:8px; background:var(--surface-2); border:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <div>
                            <div style="font-size:13px; font-weight:600; color:var(--text);">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</div>
                            <div style="font-size:11px; color:#64748b;">Asignada {{ $tarea->created_at?->diffForHumans() }}</div>
                        </div>
                        <span class="badge badge-{{ $tarea->estado === 'en_proceso' ? 'warning' : 'secondary' }}" style="font-size:11px;">
                            {{ $tarea->estado === 'en_proceso' ? 'En proceso' : 'Activa' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div style="padding:16px; border-radius:8px; background:var(--surface-2); text-align:center; color:#94a3b8; font-size:13px; margin-bottom:20px;">
            Sin tareas activas actualmente.
        </div>
    @endif

    {{-- Acciones --}}
    <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:16px; border-top:1px solid var(--border);">
        <button type="button" onclick="rhModalClose()" class="btn btn-secondary">Cerrar</button>
        <form method="POST" action="{{ route('admin.personal-interno.estado', $interno) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn {{ $interno->estado === 'activo' ? 'btn-danger' : 'btn-success' }}">
                {{ $interno->estado === 'activo' ? 'Bloquear acceso' : 'Activar acceso' }}
            </button>
        </form>
    </div>
</div>
