<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Solicitudes de servicio</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <h1 class="page-title">Solicitudes de servicio</h1>
                <p class="page-subtitle">{{ $vacantes->total() }} solicitud(es) en el sistema. Vacantes, reclutamientos y otros servicios solicitados por empresas ya aprobadas.</p>
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

    @php
        $totalPendientes = \App\Models\Vacante::where('estado', 'pendiente')->count();
        $totalActivas = \App\Models\Vacante::where('estado', 'activa')->count();
        $totalCerradas = \App\Models\Vacante::where('estado', 'cerrada')->count();
        $estadoActual = request('estado', '');
        $tipoActual = request('tipo', '');
        $tipos = \App\Models\Vacante::tiposServicio();
    @endphp

    <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('admin.vacantes') }}" style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; background:{{ $estadoActual === '' ? 'var(--accent)' : 'var(--surface-2)' }}; color:{{ $estadoActual === '' ? '#fff' : 'var(--text-muted)' }}; border:1px solid {{ $estadoActual === '' ? 'var(--accent)' : 'var(--border)' }};">Todas</a>
        <a href="{{ route('admin.vacantes', ['estado' => 'pendiente']) }}" style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; background:{{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--surface-2)' }}; color:{{ $estadoActual === 'pendiente' ? '#fff' : 'var(--text-muted)' }}; border:1px solid {{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--border)' }};">
            Por revisar
            @if($totalPendientes > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'pendiente' ? 'rgba(255,255,255,0.3)' : '#f59e0b' }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalPendientes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.vacantes', ['estado' => 'activa']) }}" style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; background:{{ $estadoActual === 'activa' ? '#22c55e' : 'var(--surface-2)' }}; color:{{ $estadoActual === 'activa' ? '#fff' : 'var(--text-muted)' }}; border:1px solid {{ $estadoActual === 'activa' ? '#22c55e' : 'var(--border)' }};">
            Activas
            @if($totalActivas > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'activa' ? 'rgba(255,255,255,0.3)' : '#22c55e' }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalActivas }}</span>
            @endif
        </a>
        @if($totalCerradas > 0)
            <a href="{{ route('admin.vacantes', ['estado' => 'cerrada']) }}" style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; background:{{ $estadoActual === 'cerrada' ? '#64748b' : 'var(--surface-2)' }}; color:{{ $estadoActual === 'cerrada' ? '#fff' : 'var(--text-muted)' }}; border:1px solid {{ $estadoActual === 'cerrada' ? '#64748b' : 'var(--border)' }};">
                Cerradas <span style="margin-left:6px; background:rgba(100,116,139,0.2); color:#94a3b8; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalCerradas }}</span>
            </a>
        @endif

        <form method="GET" style="margin-left:auto; display:flex; gap:8px;">
            @if($estadoActual) <input type="hidden" name="estado" value="{{ $estadoActual }}"> @endif
            @if($tipoActual) <input type="hidden" name="tipo" value="{{ $tipoActual }}"> @endif
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Título o empresa..."
                   style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); width:220px;">
            <button type="submit" class="btn btn-secondary" style="padding:8px 14px; font-size:13px;">Buscar</button>
            @if(request()->hasAny(['buscar', 'estado', 'tipo']))
                <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;" title="Limpiar filtros">&times;</a>
            @endif
        </form>
    </div>

    @php
        $tiposEnCatalogo = \App\Models\CatalogoServicio::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['tipo', 'nombre'])
            ->unique('tipo');
        $countsPorTipo = \App\Models\Vacante::selectRaw('tipo_servicio, COUNT(*) as total')
            ->groupBy('tipo_servicio')
            ->pluck('total', 'tipo_servicio');
    @endphp

    <form method="GET" style="display:flex; gap:10px; align-items:center; margin-bottom:18px; flex-wrap:wrap;">
        @if($estadoActual) <input type="hidden" name="estado" value="{{ $estadoActual }}"> @endif
        @if(request('buscar')) <input type="hidden" name="buscar" value="{{ request('buscar') }}"> @endif

        <label style="font-size:0.8rem; color:#64748b; font-weight:500; white-space:nowrap;">Tipo de servicio:</label>

        <select name="tipo" onchange="this.form.submit()" style="padding:7px 12px; border:1px solid {{ $tipoActual ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; font-size:0.85rem; background:var(--surface); color:var(--text); min-width:220px;">
            <option value="">Todos los servicios</option>
            @foreach($tiposEnCatalogo as $cat)
                <option value="{{ $cat->tipo }}" {{ $tipoActual === $cat->tipo ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                    @if(($countsPorTipo[$cat->tipo] ?? 0) > 0)
                        ({{ $countsPorTipo[$cat->tipo] }})
                    @endif
                </option>
            @endforeach
            @foreach($countsPorTipo as $tipo => $count)
                @if(! $tiposEnCatalogo->pluck('tipo')->contains($tipo))
                    <option value="{{ $tipo }}" {{ $tipoActual === $tipo ? 'selected' : '' }}>
                        {{ \App\Models\Vacante::tiposServicio()[$tipo] ?? $tipo }} ({{ $count }})
                    </option>
                @endif
            @endforeach
        </select>

        @if($tipoActual)
            <a href="{{ route('admin.vacantes', array_filter(['estado' => $estadoActual, 'buscar' => request('buscar')])) }}" style="font-size:0.8rem; color:#64748b; text-decoration:none; padding:4px 8px; border:1px solid var(--border); border-radius:6px;">&times; Limpiar</a>
        @endif
    </form>

    <div class="table-wrapper">
        @if($vacantes->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                @if($estadoActual === 'pendiente')
                    No hay solicitudes pendientes de revisión. ¡Todo al día!
                @else
                    No hay solicitudes que coincidan.
                @endif
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Solicitud</th>
                        <th>Empresa</th>
                        <th>Requisitos</th>
                        <th>Candidatos</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vacantes as $vacante)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--text);">{{ $vacante->titulo }}</div>
                                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</div>
                            </td>
                            <td style="font-size:0.85rem;">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</td>
                            <td style="font-size:0.8rem; color:#64748b; line-height:1.5;">
                                {{ $vacante->requisitoResumen() }}
                            </td>
                            <td>
                                @if(($vacante->postulaciones_count ?? 0) === 0)
                                    <span style="color:#475569; font-size:0.8rem;">—</span>
                                @else
                                    <div style="display:flex; flex-direction:column; gap:3px; font-size:0.75rem;">
                                        @if($vacante->seleccionados_count > 0)
                                            <span style="color:#22c55e; font-weight:700;">✓ {{ $vacante->seleccionados_count }} seleccionado{{ $vacante->seleccionados_count > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if($vacante->entrevista_count > 0)
                                            <span style="color:#f59e0b;">● {{ $vacante->entrevista_count }} ya entrevistado{{ $vacante->entrevista_count > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if($vacante->postulados_count > 0)
                                            <span style="color:#60a5fa;">● {{ $vacante->postulados_count }} en revisión</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}">
                                    {{ \App\Models\Vacante::estadoLabel($vacante->estado) }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex; gap:6px; align-items:center; justify-content:flex-end;">
                                    <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                    <a href="{{ route('admin.vacantes.matching', $vacante) }}" class="btn btn-primary" style="padding:4px 12px; font-size:0.8rem; white-space:nowrap;">Candidatos</a>
                                    @if($vacante->estado === 'pendiente')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Activar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.vacantes.cerrar', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Rechazar</button>
                                        </form>
                                    @elseif($vacante->estado === 'activa')
                                        <form method="POST" action="{{ route('admin.vacantes.cerrar', $vacante) }}" onsubmit="return confirm('¿Cerrar esta solicitud?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Cerrar</button>
                                        </form>
                                    @elseif($vacante->estado === 'cerrada')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Reabrir</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.vacantes.destroy', $vacante) }}" onsubmit="return confirm('¿Eliminar esta solicitud permanentemente? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;" title="Eliminar">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397M4.772 5.79c.342-.052.682-.107 1.022-.166m1.022.165l.346 9"/></svg>
                                        </button>
                                    </form>
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
