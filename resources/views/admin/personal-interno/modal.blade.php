<div style="padding:28px;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
        <x-avatar :src="$interno->avatar_url" :nombre="$interno->name" :tamano="48" />
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

    {{-- Capacidades --}}
    <div style="margin-bottom:24px; padding:16px; background:var(--surface-2); border-radius:10px; border:1px solid var(--border);">
        <p style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin:0 0 12px;">🎓 Especialidades</p>
        @if($serviciosCapacitados)
            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px;">
                @foreach($servicios->whereIn('id', $serviciosCapacitados) as $s)
                    <span class="badge badge-success" style="font-size:12px;">✓ {{ $s->nombre }}</span>
                @endforeach
            </div>
        @else
            <p style="font-size:13px; color:#64748b; margin:0 0 8px;">Sin especialidades asignadas aún.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.personal-interno.capacidades', $interno) }}">
        @csrf
        <div style="margin-bottom:20px;">
            <p style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin:0 0 10px;">Editar capacidades</p>
            @if($servicios->isNotEmpty())
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    @foreach($servicios as $servicio)
                        <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid var(--border); border-radius:8px; background:var(--surface); cursor:pointer; font-size:13px; transition:all 0.2s;">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                                {{ in_array($servicio->id, $serviciosCapacitados) ? 'checked' : '' }}
                                style="width:16px;height:16px; accent-color:var(--accent);">
                            <span>{{ $servicio->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <div style="padding:12px; border-radius:8px; background:var(--surface-2); text-align:center; color:#94a3b8; font-size:13px;">
                    No hay servicios activos en el <strong>Catálogo de Servicios</strong>.
                </div>
            @endif
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:12px; border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary" style="font-size:13px;">💾 Guardar cambios</button>
        </div>
    </form>

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
        <a href="{{ route('admin.personal-interno.pdf', $interno) }}" target="_blank" class="btn btn-secondary">📄 PDF</a>
        <form method="POST" action="{{ route('admin.personal-interno.estado', $interno) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn {{ $interno->estado === 'activo' ? 'btn-danger' : 'btn-success' }}">
                {{ $interno->estado === 'activo' ? 'Bloquear acceso' : 'Activar acceso' }}
            </button>
        </form>
    </div>
</div>
