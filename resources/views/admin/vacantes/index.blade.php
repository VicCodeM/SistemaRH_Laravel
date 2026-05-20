<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $modo === 'reclutamiento' ? 'Vacantes' : 'Servicios solicitados' }}</span>
        </nav>
        <div class="toolbar-wrap" style="align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">{{ $modo === 'reclutamiento' ? 'Vacantes y reclutamiento' : 'Servicios solicitados' }}</h1>
                <p class="page-subtitle">
                    @if($modo === 'reclutamiento')
                        Solicitudes de contratación de personal. Los candidatos se postulan y se gestionan desde aquí.
                    @else
                        Capacitaciones, coaching, consultoría y otros servicios que requieren asignación a un interno.
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.vacantes.crear') }}" class="btn btn-primary">+ Nueva solicitud</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="toolbar-wrap" style="margin-bottom:20px; border-bottom:2px solid var(--border); padding-bottom:8px;">
        <a href="{{ route('admin.vacantes', ['modo' => 'reclutamiento']) }}"
           style="padding:10px 20px; border-radius:8px 8px 0 0; font-size:14px; font-weight:600; text-decoration:none;
                  background:{{ $modo === 'reclutamiento' ? 'var(--accent)' : 'transparent' }};
                  color:{{ $modo === 'reclutamiento' ? '#fff' : 'var(--text-muted)' }};
                  border-bottom:{{ $modo === 'reclutamiento' ? '3px solid var(--accent)' : 'none' }};">
            Vacantes
            @if($statsReclutamiento['pendientes'] > 0)
                <span style="margin-left:6px; background:rgba(255,255,255,0.3); color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $statsReclutamiento['pendientes'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.vacantes', ['modo' => 'servicios']) }}"
           style="padding:10px 20px; border-radius:8px 8px 0 0; font-size:14px; font-weight:600; text-decoration:none;
                  background:{{ $modo === 'servicios' ? 'var(--accent)' : 'transparent' }};
                  color:{{ $modo === 'servicios' ? '#fff' : 'var(--text-muted)' }};
                  border-bottom:{{ $modo === 'servicios' ? '3px solid var(--accent)' : 'none' }};">
            Servicios solicitados
            @if($statsServicios['pendientes'] > 0)
                <span style="margin-left:6px; background:rgba(255,255,255,0.3); color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $statsServicios['pendientes'] }}</span>
            @endif
        </a>
        <div class="toolbar-end">
            <a href="{{ route('admin.tareas.kanban') }}" class="btn btn-secondary" style="font-size:13px;">Ir al Kanban</a>
        </div>
    </div>

    @php
        $estadoActual = request('estado', '');
    @endphp

    <div class="toolbar-wrap" style="margin-bottom:18px;">
        <a href="{{ route('admin.vacantes', ['modo' => $modo]) }}"
           style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === '' ? 'var(--surface-2)' : 'transparent' }};
                  color:{{ $estadoActual === '' ? 'var(--text)' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === '' ? 'var(--border)' : 'transparent' }};">
            Todas
        </a>
        <a href="{{ route('admin.vacantes', ['modo' => $modo, 'estado' => 'pendiente']) }}"
           style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'pendiente' ? '#fffbeb' : 'transparent' }};
                  color:{{ $estadoActual === 'pendiente' ? '#d97706' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'pendiente' ? '#fcd34d' : 'transparent' }};">
            Por revisar
        </a>
        <a href="{{ route('admin.vacantes', ['modo' => $modo, 'estado' => 'activa']) }}"
           style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'activa' ? '#f0fdf4' : 'transparent' }};
                  color:{{ $estadoActual === 'activa' ? '#16a34a' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'activa' ? '#86efac' : 'transparent' }};">
            Activas
        </a>
        <a href="{{ route('admin.vacantes', ['modo' => $modo, 'estado' => 'cerrada']) }}"
           style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'cerrada' ? '#f8fafc' : 'transparent' }};
                  color:{{ $estadoActual === 'cerrada' ? '#64748b' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'cerrada' ? '#cbd5e1' : 'transparent' }};">
            Cerradas
        </a>

        <form method="GET" class="form-inline toolbar-end">
            <input type="hidden" name="modo" value="{{ $modo }}">
            @if($estadoActual)
                <input type="hidden" name="estado" value="{{ $estadoActual }}">
            @endif
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Título o empresa..."
                   style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); width:220px;">
            <button type="submit" class="btn btn-secondary" style="padding:8px 14px; font-size:13px;">Buscar</button>
            @if(request()->hasAny(['buscar', 'estado']))
                <a href="{{ route('admin.vacantes', ['modo' => $modo]) }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;" title="Limpiar filtros">&times;</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper table-scroll">
        @if($vacantes->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                @if($estadoActual === 'pendiente')
                    No hay {{ $modo === 'reclutamiento' ? 'vacantes' : 'servicios' }} pendientes de revisión. Todo al día.
                @else
                    No hay {{ $modo === 'reclutamiento' ? 'vacantes' : 'servicios' }} que coincidan.
                @endif
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Solicitud</th>
                        <th>Empresa</th>
                        @if($modo === 'reclutamiento')
                            <th>Requisitos</th>
                            <th>Candidatos</th>
                        @else
                            <th>Tipo de servicio</th>
                            <th>Tarea asignada</th>
                        @endif
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vacantes as $vacante)
                        @php
                            $tareaVinculada = $modo === 'servicios' && $vacante->servicios_asignados_count > 0
                                ? $vacante->serviciosAsignados()->latest()->first()
                                : null;
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--text);">{{ $vacante->titulo }}</div>
                                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</div>
                            </td>
                            <td style="font-size:0.85rem;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <x-avatar :src="$vacante->empresa?->usuario?->avatar_url" :nombre="$vacante->empresa?->nombre_empresa ?? '?'" :tamano="28" />
                                    <span>{{ $vacante->empresa?->nombre_empresa ?? '—' }}</span>
                                </div>
                            </td>

                            @if($modo === 'reclutamiento')
                                <td style="font-size:0.8rem; color:#64748b; line-height:1.5;">
                                    {{ $vacante->requisitoResumen() }}
                                </td>
                                <td>
                                    @if(($vacante->postulaciones_count ?? 0) === 0)
                                        <span style="color:#475569; font-size:0.8rem;">—</span>
                                    @else
                                        <div style="display:flex; flex-direction:column; gap:3px; font-size:0.75rem;">
                                            @if($vacante->seleccionados_count > 0)
                                                <span style="color:#22c55e; font-weight:700;">{{ $vacante->seleccionados_count }} seleccionado{{ $vacante->seleccionados_count > 1 ? 's' : '' }}</span>
                                            @endif
                                            @if($vacante->entrevista_count > 0)
                                                <span style="color:#f59e0b;">{{ $vacante->entrevista_count }} entrevista{{ $vacante->entrevista_count > 1 ? 's' : '' }}</span>
                                            @endif
                                            @if($vacante->postulados_count > 0)
                                                <span style="color:#60a5fa;">{{ $vacante->postulados_count }} en revisión</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @else
                                <td>
                                    <span class="badge badge-secondary" style="font-size:11px;">
                                        {{ \App\Models\Vacante::tiposServicio()[$vacante->tipo_servicio] ?? $vacante->tipo_servicio }}
                                    </span>
                                </td>
                                <td>
                                    @if($tareaVinculada)
                                        <a href="{{ route('admin.tareas.show', $tareaVinculada) }}" class="badge badge-blue" style="font-size:11px; text-decoration:none;">
                                            {{ \App\Models\ServicioAsignado::estadoLabel($tareaVinculada->estado) }}
                                        </a>
                                    @else
                                        <span class="badge badge-orange" style="font-size:11px;">Sin asignar</span>
                                    @endif
                                </td>
                            @endif

                            <td>
                                <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}">
                                    {{ \App\Models\Vacante::estadoLabel($vacante->estado) }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:flex-end;">
                                    <button type="button" onclick="rhModal('{{ route('admin.vacantes.modal', $vacante) }}')" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Ver</button>
                                    <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>

                                    @if($modo === 'reclutamiento')
                                        <a href="{{ route('admin.vacantes.matching', $vacante) }}" class="btn btn-primary" style="padding:4px 12px; font-size:0.8rem; white-space:nowrap;">Candidatos</a>
                                    @else
                                        @if($tareaVinculada)
                                            <a href="{{ route('admin.tareas.show', $tareaVinculada) }}" class="btn btn-primary" style="padding:4px 12px; font-size:0.8rem;">Ver tarea</a>
                                        @else
                                            <form method="POST" action="{{ route('admin.vacantes.tarea', $vacante) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" style="padding:4px 12px; font-size:0.8rem; white-space:nowrap;">Crear tarea</button>
                                            </form>
                                        @endif
                                    @endif

                                    @if($vacante->estado === 'pendiente')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Activar</button>
                                        </form>
                                        <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'rechazar']) }}')" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Rechazar</button>
                                    @elseif($vacante->estado === 'activa')
                                        <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'cerrar']) }}')" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Cerrar</button>
                                    @elseif($vacante->estado === 'cerrada')
                                        <form method="POST" action="{{ route('admin.vacantes.reabrir', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Reabrir</button>
                                        </form>
                                    @elseif($vacante->estado === 'rechazada')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Reactivar</button>
                                        </form>
                                    @endif

                                    <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'eliminar']) }}')" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;" title="Eliminar">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397M4.772 5.79c.342-.052.682-.107 1.022-.166m1.022.165l.346 9"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">
                {{ $vacantes->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
