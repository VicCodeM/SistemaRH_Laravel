<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <span>Solicitudes de Servicio</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 class="page-title">Solicitudes de Servicio</h1>
                <p class="page-subtitle">{{ $vacantes->total() }} solicitud(es) en el sistema.</p>
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

    {{-- Tabs de estado --}}
    @php
        $totalPendientes = \App\Models\Vacante::where('estado','pendiente')->count();
        $totalActivas    = \App\Models\Vacante::where('estado','activa')->count();
        $totalCerradas   = \App\Models\Vacante::where('estado','cerrada')->count();
        $estadoActual    = request('estado','');
        $tipoActual      = request('tipo','');
        $tipos           = \App\Models\Vacante::tiposServicio();
    @endphp

    {{-- Fila 1: Tabs de estado --}}
    <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('admin.vacantes') }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === '' ? 'var(--accent)' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === '' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === '' ? 'var(--accent)' : 'var(--border)' }};">
            Todas
        </a>
        <a href="{{ route('admin.vacantes', ['estado'=>'pendiente']) }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === 'pendiente' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--border)' }};">
            Por revisar
            @if($totalPendientes > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'pendiente' ? 'rgba(255,255,255,0.3)' : '#f59e0b' }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalPendientes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.vacantes', ['estado'=>'activa']) }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'activa' ? '#22c55e' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === 'activa' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'activa' ? '#22c55e' : 'var(--border)' }};">
            Activas
            @if($totalActivas > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'activa' ? 'rgba(255,255,255,0.3)' : '#22c55e' }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalActivas }}</span>
            @endif
        </a>
        @if($totalCerradas > 0)
            <a href="{{ route('admin.vacantes', ['estado'=>'cerrada']) }}"
               style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                      background:{{ $estadoActual === 'cerrada' ? '#64748b' : 'var(--surface-2)' }};
                      color:{{ $estadoActual === 'cerrada' ? '#fff' : 'var(--text-muted)' }};
                      border:1px solid {{ $estadoActual === 'cerrada' ? '#64748b' : 'var(--border)' }};">
                Cerradas <span style="margin-left:6px; background:rgba(100,116,139,0.2); color:#94a3b8; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalCerradas }}</span>
            </a>
        @endif

        {{-- Búsqueda --}}
        <form method="GET" style="margin-left:auto; display:flex; gap:8px;">
            @if($estadoActual) <input type="hidden" name="estado" value="{{ $estadoActual }}"> @endif
            @if($tipoActual)   <input type="hidden" name="tipo"   value="{{ $tipoActual }}">   @endif
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Título o empresa..."
                   style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); width:200px;">
            <button type="submit" class="btn btn-secondary" style="padding:8px 14px; font-size:13px;">Buscar</button>
            @if(request()->hasAny(['buscar','estado','tipo']))
                <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;" title="Limpiar filtros">✕</a>
            @endif
        </form>
    </div>

    {{-- Fila 2: Filtro por catálogo --}}
    @php
        $tiposEnCatalogo = \App\Models\CatalogoServicio::where('activo', true)
            ->orderBy('orden')->orderBy('nombre')
            ->get(['tipo', 'nombre'])
            ->unique('tipo');
        $countsPorTipo = \App\Models\Vacante::selectRaw('tipo_servicio, COUNT(*) as total')
            ->groupBy('tipo_servicio')->pluck('total', 'tipo_servicio');
    @endphp
    <form method="GET" style="display:flex; gap:10px; align-items:center; margin-bottom:18px; flex-wrap:wrap;">
        @if($estadoActual) <input type="hidden" name="estado" value="{{ $estadoActual }}"> @endif
        @if(request('buscar')) <input type="hidden" name="buscar" value="{{ request('buscar') }}"> @endif

        <label style="font-size:0.8rem; color:#64748b; font-weight:500; white-space:nowrap;">Tipo de servicio:</label>

        <select name="tipo" onchange="this.form.submit()"
                style="padding:7px 12px; border:1px solid {{ $tipoActual ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; font-size:0.85rem; background:var(--surface); color:var(--text); min-width:220px;">
            <option value="">— Todos los servicios —</option>
            @foreach($tiposEnCatalogo as $cat)
                <option value="{{ $cat->tipo }}" {{ $tipoActual === $cat->tipo ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                    @if(($countsPorTipo[$cat->tipo] ?? 0) > 0)
                        ({{ $countsPorTipo[$cat->tipo] }})
                    @endif
                </option>
            @endforeach
            {{-- Tipos con solicitudes que no están en catálogo activo --}}
            @foreach($countsPorTipo as $tipo => $count)
                @if(!$tiposEnCatalogo->pluck('tipo')->contains($tipo))
                    <option value="{{ $tipo }}" {{ $tipoActual === $tipo ? 'selected' : '' }}>
                        {{ \App\Models\Vacante::tiposServicio()[$tipo] ?? $tipo }} ({{ $count }})
                    </option>
                @endif
            @endforeach
        </select>

        @if($tipoActual)
            <a href="{{ route('admin.vacantes', array_filter(['estado'=>$estadoActual,'buscar'=>request('buscar')])) }}"
               style="font-size:0.8rem; color:#64748b; text-decoration:none; padding:4px 8px; border:1px solid var(--border); border-radius:6px;">
                ✕ Limpiar
            </a>
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
                        <th>Tipo de servicio</th>
                        <th>Nivel</th>
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
                                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">
                                    {{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}
                                </div>
                            </td>
                            <td style="font-size:0.85rem;">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</td>
                            <td>
                                <span class="badge badge-secondary" style="font-size:0.75rem;">
                                    {{ $tipos[$vacante->tipo_servicio] ?? $vacante->tipo_servicio ?? '—' }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem; color:#94a3b8;">
                                {{ ucfirst(str_replace('_',' ', $vacante->nivel_jerarquico ?? '—')) }}
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
                                            <span style="color:#f59e0b;">⬤ {{ $vacante->entrevista_count }} en entrevista</span>
                                        @endif
                                        @if($vacante->postulados_count > 0)
                                            <span style="color:#60a5fa;">⬤ {{ $vacante->postulados_count }} en revisión</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sc = ['pendiente'=>'warning','activa'=>'success','cerrada'=>'secondary'];
                                    $sl = ['pendiente'=>'Por revisar','activa'=>'Activa','cerrada'=>'Cerrada'];
                                @endphp
                                <span class="badge badge-{{ $sc[$vacante->estado] ?? 'secondary' }}">
                                    {{ $sl[$vacante->estado] ?? ucfirst($vacante->estado) }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex; gap:6px; align-items:center; justify-content:flex-end;">
                                    <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                    @php
                                        $btnColor = '#1e3a5f'; $btnTxt = '#60a5fa'; $btnBg = 'transparent';
                                        $btnLabel = 'Candidatos';
                                        if (($vacante->seleccionados_count ?? 0) > 0) {
                                            $btnColor = '#22c55e'; $btnTxt = '#fff'; $btnBg = '#22c55e'; $btnLabel = '✓ Cubierta';
                                        } elseif (($vacante->entrevista_count ?? 0) > 0) {
                                            $btnColor = '#f59e0b'; $btnTxt = '#fff'; $btnBg = '#f59e0b'; $btnLabel = 'En entrevista (' . $vacante->entrevista_count . ')';
                                        } elseif (($vacante->postulados_count ?? 0) > 0) {
                                            $btnColor = 'var(--accent)'; $btnTxt = '#fff'; $btnBg = 'var(--accent)'; $btnLabel = 'Candidatos (' . $vacante->postulados_count . ')';
                                        }
                                    @endphp
                                    <a href="{{ route('admin.vacantes.matching', $vacante) }}"
                                       style="padding:4px 12px; border:1px solid {{ $btnColor }}; color:{{ $btnTxt }}; background:{{ $btnBg }}; border-radius:6px; font-size:0.8rem; text-decoration:none; white-space:nowrap;">
                                        {{ $btnLabel }}
                                    </a>
                                    @if($vacante->estado === 'pendiente')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Activar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.vacantes.cerrar', $vacante) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Rechazar</button>
                                        </form>
                                    @elseif($vacante->estado === 'activa')
                                        <form method="POST" action="{{ route('admin.vacantes.cerrar', $vacante) }}" onsubmit="return confirm('¿Cerrar esta solicitud?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Cerrar</button>
                                        </form>
                                    @elseif($vacante->estado === 'cerrada')
                                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:4px 10px; font-size:0.8rem;">Reabrir</button>
                                        </form>
                                    @endif
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
