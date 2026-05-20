<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.tareas.index') }}">Pedidos de servicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>#{{ $tarea->id }}</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</h1>
                <p class="page-subtitle">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
            </div>
            <div class="toolbar-wrap">
                @if($tarea->puedeAsignar())
                    <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-primary" style="font-size:13px;">Asignar interno</a>
                @else
                    <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-secondary" style="font-size:13px;">Ver matching</a>
                @endif
                <a href="{{ route('admin.tareas.editar', $tarea) }}" class="btn btn-secondary">Editar</a>
                <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'eliminar']) }}')" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; align-items:start;">
        <div class="card fade-in">
            <div class="candidate-inline-meta" style="margin-bottom:16px;">
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

            <div style="display:grid; gap:14px; font-size:14px;">
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Servicio</p>
                    <p style="margin:0; font-weight:600;">{{ $tarea->servicio?->nombre ?? '—' }}</p>
                    <p style="margin:4px 0 0; color:var(--text-muted); font-size:12px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</p>
                </div>

                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Beneficiario</p>
                    <p style="margin:0; font-weight:600;">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
                </div>

                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Responsable interno</p>
                    @if($tarea->asignadoA)
                        <div style="display:flex; align-items:center; gap:10px;">
                            <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="34" />
                            <div>
                                <p style="margin:0; font-weight:600;">{{ $tarea->asignadoA->name }}</p>
                                <p style="margin:2px 0 0; color:var(--text-muted); font-size:12px;">{{ $tarea->asignadoA->email }}</p>
                            </div>
                        </div>
                    @else
                        <span class="badge badge-orange" style="font-size:12px;">Pendiente de asignacion</span>
                    @endif
                </div>

                @if($tarea->estaPendiente() && ! $tarea->asignadoA)
                    <div style="padding:12px; background:#fffbeb; border:1px solid #fcd34d; border-radius:8px;">
                        <p style="margin:0 0 8px; font-size:13px; font-weight:500;">Asignar responsable</p>
                        <form method="POST" action="{{ route('admin.tareas.asignar', $tarea) }}" style="display:flex; gap:8px; flex-wrap:wrap;">
                            @csrf
                            <select name="asignado_a" required style="flex:1; min-width:220px; padding:8px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:white;">
                                <option value="">Seleccionar interno...</option>
                                @foreach(\App\Models\User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get() as $interno)
                                    <option value="{{ $interno->id }}">{{ $interno->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary" style="font-size:13px;">Asignar</button>
                        </form>
                    </div>
                @elseif($tarea->estaPendiente() && $tarea->asignadoA)
                    <div style="padding:12px; background:#eff6ff; border:1px solid #93c5fd; border-radius:8px;">
                        <p style="margin:0 0 6px; font-size:13px; font-weight:600; color:#1d4ed8;">Pedido pendiente con responsable ligado</p>
                        <p style="margin:0; font-size:0.84rem; color:#475569;">
                            Puedes activarlo desde matching o liberar al interno actual para reasignarlo.
                        </p>
                    </div>
                @endif

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

        <div style="display:grid; gap:20px;">
            @include('partials.pedido-comentarios', ['servicio' => $tarea])

            <div class="card fade-in">
                <h4 style="font-weight:600; font-size:14px; margin:0 0 14px;">Informacion</h4>
                <div style="display:grid; gap:10px; font-size:13px;">
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Solicitado por</p>
                        <span style="font-weight:500;">{{ $tarea->solicitadoPor?->name ?? '—' }}</span>
                    </div>
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
                    @if($tarea->vacante)
                        <div>
                            <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Vinculado a vacante</p>
                            <span style="color:var(--accent); font-weight:500;">{{ $tarea->vacante->titulo }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
