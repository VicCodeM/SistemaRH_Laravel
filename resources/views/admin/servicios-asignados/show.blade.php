<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.tareas.index') }}">Tareas</a>
            <span class="breadcrumb-sep">›</span>
            <span>#{{ $tarea->id }}</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</h1>
                <p class="page-subtitle">
                    {{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }}
                    · {{ $tarea->asignableNombre() }}
                </p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.tareas.editar', $tarea) }}" class="btn btn-secondary">Editar</a>
                <form method="POST" action="{{ route('admin.tareas.eliminar', $tarea) }}" onsubmit="return confirm('¿Eliminar esta tarea permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
                <a href="{{ route('admin.tareas.crear') }}" class="btn btn-primary">+ Nueva tarea</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns: 1fr 300px; gap:20px; align-items:start;">
        <div class="card fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
                <div>
                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Estado</div>
                    <div style="margin-top:4px;">
                        <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                            {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                        </span>
                    </div>
                </div>
                <div style="text-align:right; font-size:13px; color:var(--text-muted);">
                    Creada el {{ $tarea->created_at?->format('d/m/Y H:i') ?? '—' }}
                </div>
            </div>

            <div style="display:grid; gap:12px; font-size:14px;">
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Servicio</p>
                    <p style="margin:0; font-weight:600;">{{ $tarea->servicio?->nombre ?? '—' }}</p>
                    <p style="margin:4px 0 0; color:var(--text-muted); font-size:12px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</p>
                </div>

                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Objetivo</p>
                    <p style="margin:0; font-weight:600;">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
                </div>

                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Responsable interno</p>
                    <p style="margin:0; font-weight:600;">{{ $tarea->asignadoA?->name ?? '—' }}</p>
                </div>

                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Notas</p>
                    <p style="margin:0; white-space:pre-wrap; line-height:1.6;">{{ $tarea->notas ?: 'Sin notas iniciales.' }}</p>
                </div>

                @if($tarea->cierre_resumen)
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Cierre</p>
                        <p style="margin:0; white-space:pre-wrap; line-height:1.6;">{{ $tarea->cierre_resumen }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card fade-in">
            <h4 style="font-weight:600; font-size:14px; margin:0 0 14px 0;">Información</h4>
            <div style="display:grid; gap:10px; font-size:13px;">
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Asignada por</p>
                    <span style="font-weight:500;">{{ $tarea->asignadoPor?->name ?? '—' }}</span>
                </div>
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Inicio</p>
                    <span>{{ $tarea->fecha_inicio?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Fin</p>
                    <span>{{ $tarea->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
