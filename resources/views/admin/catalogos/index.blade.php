@php
    $tabActivo = $tabActivo ?? request('tab', 'servicios');
    $baseQuery = request()->except(['tab', 'page']);
    $irOpciones = route('admin.catalogos.index', array_merge($baseQuery, ['tab' => 'opciones']));
    $irServicios = route('admin.catalogos.index', array_merge($baseQuery, ['tab' => 'servicios']));
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Catálogos del sistema</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">Catálogos del sistema</h1>
                <p class="page-subtitle">Catálogo de servicios primero y opciones reutilizables después, todo en una sola pantalla con pestañas internas.</p>
            </div>
            @if($tabActivo === 'servicios')
                <a href="{{ route('admin.catalogo.create') }}" class="btn btn-primary">+ Agregar servicio</a>
            @else
                <a href="{{ route('admin.catalogos.create', ['grupo' => request('grupo')]) }}" class="btn btn-primary">+ Nueva opción</a>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="metrics-grid fade-in">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Servicios totales</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">S</div>
            </div>
            <div class="metric-value">{{ $serviciosStats['total'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Servicios activos</span>
                <div class="metric-icon" style="background:rgba(168,85,247,.12);color:#a855f7;">A</div>
            </div>
            <div class="metric-value">{{ $serviciosStats['activos'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Opciones totales</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;">#</div>
            </div>
            <div class="metric-value">{{ $stats['opciones_total'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Grupos activos</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">G</div>
            </div>
            <div class="metric-value">{{ $stats['grupos_activos'] }}</div>
        </div>
    </div>

    <div class="card fade-in" style="margin-top:24px; padding:0; overflow:hidden;">
        <div style="display:flex; gap:8px; padding:14px 16px; border-bottom:1px solid var(--border); background:var(--surface-2); flex-wrap:wrap;">
            <a href="{{ $irServicios }}"
               style="padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; font-size:0.9rem; border:1px solid {{ $tabActivo === 'servicios' ? 'transparent' : 'var(--border)' }}; background:{{ $tabActivo === 'servicios' ? 'var(--accent)' : 'var(--surface)' }}; color:{{ $tabActivo === 'servicios' ? '#fff' : 'var(--text)' }};">
                Catálogo de servicios
            </a>
            <a href="{{ $irOpciones }}"
               style="padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; font-size:0.9rem; border:1px solid {{ $tabActivo === 'opciones' ? 'transparent' : 'var(--border)' }}; background:{{ $tabActivo === 'opciones' ? 'var(--accent)' : 'var(--surface)' }}; color:{{ $tabActivo === 'opciones' ? '#fff' : 'var(--text)' }};">
                Opciones del sistema
            </a>
        </div>

        <div style="padding:20px;">
            @if($tabActivo === 'opciones')
                <div class="card" style="margin-bottom:20px;">
                    <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                        <input type="hidden" name="tab" value="opciones">
                        <div class="form-group" style="margin:0; min-width:240px;">
                            <label class="form-label" for="grupo">Grupo</label>
                            <select id="grupo" name="grupo" class="form-input" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach(\App\Models\CatalogoOpcion::gruposGestionables() as $key => $label)
                                    <option value="{{ $key }}" {{ request('grupo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0; min-width:220px;">
                            <label class="form-label" for="estado">Estado</label>
                            <select id="estado" name="estado" class="form-input" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0; min-width:260px; flex:1;">
                            <label class="form-label" for="buscar">Buscar</label>
                            <input type="text" id="buscar" name="buscar" class="form-input" value="{{ request('buscar') }}" placeholder="Clave, valor o descripción">
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            @if(request()->hasAny(['grupo', 'estado', 'buscar']))
                                <a href="{{ route('admin.catalogos.index', ['tab' => 'opciones']) }}" class="btn btn-secondary">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>

                @forelse($catalogosPorGrupo as $grupo => $items)
                    @php
                        $grupoLabel = \App\Models\CatalogoOpcion::grupoLabel($grupo);
                        $resumen = $items->take(5)->pluck('valor')->implode(' · ');
                    @endphp
                    <div class="card fade-in" style="margin-top:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
                            <div>
                                <div style="display:inline-flex; align-items:center; gap:8px; padding:4px 10px; border-radius:999px; background:rgba(59,130,246,.08); color:#60a5fa; font-size:12px; font-weight:600;">
                                    {{ $items->count() }} opción(es)
                                </div>
                                <h2 style="margin:10px 0 4px; font-size:1.05rem; font-weight:700;">{{ $grupoLabel }}</h2>
                                <p style="margin:0; color:#64748b; font-size:0.85rem;">
                                    {{ $resumen ?: 'Sin opciones visibles en este grupo.' }}
                                </p>
                            </div>
                            <a href="{{ route('admin.catalogos.create', ['grupo' => $grupo]) }}" class="btn btn-secondary">+ Nueva opción</a>
                        </div>

                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                            @foreach($items->take(6) as $item)
                                <span class="badge {{ $item->es_sistema ? 'badge-blue' : ($item->activo ? 'badge-green' : 'badge-gray') }}">
                                    {{ $item->valor }}
                                </span>
                            @endforeach
                            @if($items->count() > 6)
                                <span class="badge badge-gray">+{{ $items->count() - 6 }} más</span>
                            @endif
                        </div>

                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Valor</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Orden</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $catalogo)
                                        <tr>
                                            <td style="font-size:0.85rem; color:#94a3b8;">{{ $catalogo->clave }}</td>
                                            <td>
                                                <div style="font-weight:600; color:var(--text);">{{ $catalogo->valor }}</div>
                                            </td>
                                            <td>
                                                @if($catalogo->descripcion)
                                                    <div style="font-size:0.82rem; color:#64748b;">{{ \Illuminate\Support\Str::limit($catalogo->descripcion, 90) }}</div>
                                                @else
                                                    <span style="color:#94a3b8;">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $catalogo->es_sistema ? 'badge-blue' : 'badge-gray' }}">
                                                    {{ $catalogo->es_sistema ? 'Sistema' : 'Personalizado' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($catalogo->es_sistema)
                                                    <span class="badge badge-green">Activo</span>
                                                @else
                                                    <form method="POST" action="{{ route('admin.catalogos.toggle', $catalogo) }}" style="display:inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="badge {{ $catalogo->activo ? 'badge-green' : 'badge-gray' }}" style="border:none; cursor:pointer;">
                                                            {{ $catalogo->activo ? 'Activo' : 'Inactivo' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td style="text-align:center; color:#64748b;">{{ $catalogo->orden }}</td>
                                            <td style="white-space:nowrap;">
                                                <div style="display:flex; gap:6px; justify-content:flex-end;">
                                                    <a href="{{ route('admin.catalogos.edit', $catalogo) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                                    @if(! $catalogo->es_sistema)
                                                        <form method="POST" action="{{ route('admin.catalogos.destroy', $catalogo) }}" onsubmit="return confirm('¿Eliminar esta opción?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Eliminar</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="card" style="text-align:center; padding:40px;">
                        <p style="margin:0 0 8px; color:#475569;">No hay opciones registradas.</p>
                        <a href="{{ route('admin.catalogos.create', ['grupo' => request('grupo')]) }}" class="btn btn-primary">Agregar la primera opción</a>
                    </div>
                @endforelse
            @else
                <div class="card" style="margin-bottom:20px;">
                    <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                        <input type="hidden" name="tab" value="servicios">
                        <div class="form-group" style="margin:0; min-width:220px;">
                            <label class="form-label" for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" class="form-input" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach(\App\Models\CatalogoServicio::tipos() as $key => $label)
                                    <option value="{{ $key }}" {{ request('tipo') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0; min-width:220px;">
                            <label class="form-label" for="nivel">Jerarquía</label>
                            <select id="nivel" name="nivel" class="form-input" onchange="this.form.submit()">
                                <option value="">Todas</option>
                                @foreach(\App\Models\CatalogoServicio::nivelesJerarquicos() as $key => $label)
                                    <option value="{{ $key }}" {{ request('nivel') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0; min-width:220px;">
                            <label class="form-label" for="para_quien">Para quién</label>
                            <select id="para_quien" name="para_quien" class="form-input" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="empresa" {{ request('para_quien') === 'empresa' ? 'selected' : '' }}>Empresas</option>
                                <option value="candidato" {{ request('para_quien') === 'candidato' ? 'selected' : '' }}>Candidatos</option>
                                <option value="ambos" {{ request('para_quien') === 'ambos' ? 'selected' : '' }}>Ambos</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0; min-width:260px; flex:1;">
                            <label class="form-label" for="buscar_servicio">Buscar</label>
                            <input type="text" id="buscar_servicio" name="buscar_servicio" class="form-input" value="{{ request('buscar_servicio') }}" placeholder="Nombre o descripción">
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            @if(request()->hasAny(['tipo', 'nivel', 'para_quien', 'buscar_servicio']))
                                <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="btn btn-secondary">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Tipo</th>
                                <th>Jerarquía</th>
                                <th>Para quién</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servicios as $servicio)
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:var(--text);">{{ $servicio->nombre }}</div>
                                        @if($servicio->descripcion)
                                            <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ \Illuminate\Support\Str::limit($servicio->descripcion, 80) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-blue" style="font-size:0.75rem;">
                                            {{ \App\Models\CatalogoServicio::tipos()[$servicio->tipo] ?? $servicio->tipo }}
                                        </span>
                                    </td>
                                    <td style="font-size:0.83rem; color:#94a3b8;">
                                        {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico) }}
                                    </td>
                                    <td style="font-size:0.8rem; color:#94a3b8;">
                                        @php $pq = ['empresa' => 'Empresa', 'candidato' => 'Candidato', 'ambos' => 'Ambos']; @endphp
                                        {{ $pq[$servicio->para_quien] ?? $servicio->para_quien }}
                                    </td>
                                    <td style="text-align:center; font-size:0.85rem; color:#64748b;">{{ $servicio->orden ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.catalogo.toggle', $servicio) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; border:none;
                                                           background:{{ $servicio->activo ? 'var(--success-light)' : 'var(--surface-2)' }};
                                                           color:{{ $servicio->activo ? 'var(--success)' : 'var(--text-muted)' }};"
                                                    title="{{ $servicio->activo ? 'Clic para desactivar' : 'Clic para activar' }}">
                                                {{ $servicio->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <div style="display:flex; gap:6px;">
                                            <a href="{{ route('admin.catalogo.edit', $servicio) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                            <form method="POST" action="{{ route('admin.catalogo.destroy', $servicio) }}" onsubmit="return confirm('¿Eliminar este servicio del catálogo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:48px; color:#475569;">
                                        No hay servicios en el catálogo.
                                        <a href="{{ route('admin.catalogo.create') }}" style="color:var(--accent);">Agregar el primero</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px;">
                    {{ $servicios->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
