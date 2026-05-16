<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('interno.tareas.index') }}">Mis tareas</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
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
            <a href="{{ route('interno.tareas.index') }}" class="btn btn-secondary">← Volver</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
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

            <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
                @if($tarea->estado === 'activo')
                    <button type="button" class="btn btn-primary" onclick="rhModal('{{ route('interno.tareas.tomar.modal', $tarea) }}')">
                        Tomar tarea
                    </button>
                @endif

                @if(!in_array($tarea->estado, ['completado', 'cancelado'], true))
                    <button type="button" class="btn btn-secondary" onclick="rhModal('{{ route('interno.tareas.completar.modal', $tarea) }}')">
                        Completar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="rhModal('{{ route('interno.tareas.cancelar.modal', $tarea) }}')">
                        Cancelar
                    </button>
                @else
                    <div style="padding:14px 16px; background:var(--success-light); color:var(--success); border-radius:10px;">
                        Tarea cerrada el {{ $tarea->fecha_fin?->format('d/m/Y H:i') ?? '—' }}.
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
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Responsable</p>
                    <span>{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
