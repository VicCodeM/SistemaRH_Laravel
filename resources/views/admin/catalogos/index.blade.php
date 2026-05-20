@php
    $tabActivo = $tabActivo ?? request('tab', 'servicios');
    $baseQuery = request()->except(['tab', 'page', 'grupo', 'buscar', 'buscar_servicio']);

    $tabs = [
        'servicios' => ['icono' => 'Servicios', 'label' => 'Servicios'],
        'vacantes' => ['icono' => 'Vacantes', 'label' => 'Vacantes'],
        'empresas' => ['icono' => 'Empresas', 'label' => 'Empresas'],
    ];

    $hints = [
        'servicios' => 'Servicios que tu empresa ofrece y los tipos que se pueden solicitar.',
        'vacantes' => 'Listas que usa el formulario de vacantes: areas, niveles de estudio, contratos y jerarquias.',
        'empresas' => 'Datos de las empresas clientes: sectores economicos.',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Personalizar el sistema</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Personalizar el sistema</h1>
                <p class="page-subtitle">Decide que servicios ofreces y que opciones aparecen en los formularios.</p>
            </div>
            @if($tabActivo === 'servicios')
                <a href="{{ route('admin.catalogo.create') }}" class="btn btn-primary" style="font-size:14px; padding:10px 18px;">+ Nuevo servicio</a>
            @else
                @php $gruposModulo = \App\Models\CatalogoOpcion::gruposDelModulo($tabActivo); @endphp
                <a href="{{ route('admin.catalogos.create', ['grupo' => request('grupo', $gruposModulo[0] ?? null), 'tab' => $tabActivo]) }}" class="btn btn-primary" style="font-size:14px; padding:10px 18px;">+ Nueva opcion</a>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div style="display:flex; gap:0; margin-bottom:18px; border-bottom:2px solid var(--border); flex-wrap:wrap;">
        @foreach($tabs as $key => $cfg)
            <a href="{{ route('admin.catalogos.index', array_merge($baseQuery, ['tab' => $key])) }}"
               style="padding:14px 24px; text-decoration:none; font-weight:600; font-size:1rem; border-bottom:3px solid {{ $tabActivo === $key ? 'var(--accent)' : 'transparent' }}; color:{{ $tabActivo === $key ? 'var(--accent)' : 'var(--text-muted)' }}; margin-bottom:-2px;">
                {{ $cfg['label'] }}
            </a>
        @endforeach
    </div>

    @if($tabActivo === 'servicios')
        <div class="metrics-grid" style="margin-bottom:18px;">
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Servicios</span>
                </div>
                <div class="metric-value">{{ $serviciosStats['total'] }}</div>
                <span class="metric-change text-muted">{{ $serviciosStats['activos'] }} visibles</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Empresa</span>
                </div>
                <div class="metric-value">{{ $serviciosStats['empresa'] }}</div>
                <span class="metric-change text-muted">Solo empresas</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Candidato</span>
                </div>
                <div class="metric-value">{{ $serviciosStats['candidato'] }}</div>
                <span class="metric-change text-muted">Solo candidatos</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Ambos</span>
                </div>
                <div class="metric-value">{{ $serviciosStats['ambos'] }}</div>
                <span class="metric-change text-muted">Disponibles para ambos</span>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <p style="margin:0 0 16px; font-size:13px; color:#64748b;">
                Aqui defines los servicios que tu empresa puede dar: capacitaciones, coaching, mantenimiento y otros.
            </p>

            <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                <input type="hidden" name="tab" value="servicios">
                <input type="text" name="buscar_servicio" class="form-input" value="{{ request('buscar_servicio') }}" placeholder="Buscar servicio..." style="flex:1; min-width:220px; padding:10px 14px;">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                @if(request('buscar_servicio'))
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="btn btn-ghost">Limpiar</a>
                @endif
            </form>

            @if($servicios->isEmpty())
                <div style="text-align:center; padding:60px 20px;">
                    <p style="font-size:1rem; margin:0 0 6px;">Aun no hay servicios.</p>
                    <p style="font-size:13px; color:#94a3b8; margin:0 0 16px;">Agrega el primero para que empresas y candidatos puedan solicitarlo.</p>
                    <a href="{{ route('admin.catalogo.create') }}" class="btn btn-primary">+ Agregar el primero</a>
                </div>
            @else
                <div class="desktop-only table-scroll">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Nombre del servicio</th>
                                <th style="width:120px;">Para quien</th>
                                <th style="width:100px; text-align:center;">Visible</th>
                                <th style="width:160px; text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicios as $servicio)
                                @php
                                    $pq = ['empresa' => 'Empresas', 'candidato' => 'Candidatos', 'ambos' => 'Ambos'];
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:600; font-size:14px;">{{ $servicio->nombre }}</div>
                                        @if($servicio->descripcion)
                                            <div style="font-size:12px; color:#94a3b8; margin-top:2px;">{{ \Illuminate\Support\Str::limit($servicio->descripcion, 90) }}</div>
                                        @endif
                                    </td>
                                    <td><span style="font-size:12px;">{{ $pq[$servicio->para_quien] ?? 'Ambos' }}</span></td>
                                    <td style="text-align:center;">
                                        <form method="POST" action="{{ route('admin.catalogo.toggle', $servicio) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    style="padding:5px 12px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; border:none;
                                                           background:{{ $servicio->activo ? 'var(--success-light)' : 'var(--surface-2)' }};
                                                           color:{{ $servicio->activo ? 'var(--success)' : 'var(--text-muted)' }};"
                                                    title="{{ $servicio->activo ? 'Clic para ocultarlo' : 'Clic para mostrarlo' }}">
                                                {{ $servicio->activo ? 'Si' : 'No' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td style="text-align:right; white-space:nowrap;">
                                        <a href="{{ route('admin.catalogo.edit', $servicio) }}" class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">Editar</a>
                                        <button type="button" onclick="rhModal('{{ route('admin.catalogo.accion.modal', [$servicio, 'eliminar']) }}')" class="btn btn-danger" style="padding:5px 12px; font-size:12px;">Borrar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-only">
                    <div class="candidate-mobile-list">
                        @foreach($servicios as $servicio)
                            @php
                                $pq = ['empresa' => 'Empresas', 'candidato' => 'Candidatos', 'ambos' => 'Ambos'];
                            @endphp
                            <article class="candidate-mobile-card">
                                <div class="candidate-inline-meta">
                                    <div>
                                        <h3 class="candidate-mobile-card-title">{{ $servicio->nombre }}</h3>
                                        <p class="candidate-mobile-card-subtitle">{{ $pq[$servicio->para_quien] ?? 'Ambos' }}</p>
                                    </div>
                                    <span class="badge {{ $servicio->activo ? 'badge-green' : 'badge-gray' }}">{{ $servicio->activo ? 'Visible' : 'Oculto' }}</span>
                                </div>

                                <div class="candidate-mobile-meta">
                                    <div>
                                        <p class="candidate-mobile-meta-label">Tipo</p>
                                        <p class="candidate-mobile-meta-value">{{ \App\Models\CatalogoServicio::tipos()[$servicio->tipo] ?? $servicio->tipo }}</p>
                                    </div>
                                    <div>
                                        <p class="candidate-mobile-meta-label">Jerarquia</p>
                                        <p class="candidate-mobile-meta-value">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico) }}</p>
                                    </div>
                                    <div>
                                        <p class="candidate-mobile-meta-label">Descripcion</p>
                                        <p class="candidate-mobile-meta-value">{{ $servicio->descripcion ?: 'Sin descripcion' }}</p>
                                    </div>
                                </div>

                                <div class="candidate-actions" style="margin-top:14px;">
                                    <form method="POST" action="{{ route('admin.catalogo.toggle', $servicio) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-secondary btn-sm">{{ $servicio->activo ? 'Ocultar' : 'Mostrar' }}</button>
                                    </form>
                                    <a href="{{ route('admin.catalogo.edit', $servicio) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    <button type="button" onclick="rhModal('{{ route('admin.catalogo.accion.modal', [$servicio, 'eliminar']) }}')" class="btn btn-danger btn-sm">Borrar</button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div style="margin-top:16px;">{{ $servicios->links() }}</div>
            @endif
        </div>
    @else
        <div class="metrics-grid" style="margin-bottom:18px;">
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Opciones</span>
                </div>
                <div class="metric-value">{{ $stats['opciones_total'] }}</div>
                <span class="metric-change text-muted">Total del sistema</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Grupos</span>
                </div>
                <div class="metric-value">{{ $stats['grupos_activos'] }}</div>
                <span class="metric-change text-muted">Listas configurables</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Sistema</span>
                </div>
                <div class="metric-value">{{ $stats['opciones_sistema'] }}</div>
                <span class="metric-change text-muted">Bloqueadas</span>
            </div>
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Personalizadas</span>
                </div>
                <div class="metric-value">{{ $stats['opciones_personalizadas'] }}</div>
                <span class="metric-change text-muted">Editables</span>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <p style="margin:0 0 16px; font-size:13px; color:#64748b;">{{ $hints[$tabActivo] ?? '' }}</p>

            @php
                $todosLosGrupos = \App\Models\CatalogoOpcion::gruposGestionables();
                $gruposModulo = \App\Models\CatalogoOpcion::gruposDelModulo($tabActivo);
            @endphp

            <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                <input type="hidden" name="tab" value="{{ $tabActivo }}">
                <select name="grupo" class="form-input" onchange="this.form.submit()" style="min-width:280px; padding:10px 14px; font-size:14px;">
                    <option value="">Ver todas las listas de {{ $tabs[$tabActivo]['label'] }}</option>
                    @foreach($gruposModulo as $clave)
                        @if(isset($todosLosGrupos[$clave]))
                            <option value="{{ $clave }}" {{ request('grupo') === $clave ? 'selected' : '' }}>{{ $todosLosGrupos[$clave] }}</option>
                        @endif
                    @endforeach
                </select>
                <input type="text" name="buscar" class="form-input" value="{{ request('buscar') }}" placeholder="Buscar..." style="flex:1; min-width:200px; padding:10px 14px;">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                @if(request()->hasAny(['grupo', 'buscar']))
                    <a href="{{ route('admin.catalogos.index', ['tab' => $tabActivo]) }}" class="btn btn-ghost">Limpiar</a>
                @endif
            </form>

            @forelse($catalogosPorGrupo as $grupo => $items)
                @php $grupoLabel = \App\Models\CatalogoOpcion::grupoLabel($grupo); @endphp
                <div style="margin-bottom:24px; padding:18px; border:1px solid var(--border); border-radius:10px; background:var(--surface);">
                    <div class="candidate-inline-meta" style="margin-bottom:12px;">
                        <div>
                            <h3 style="margin:0; font-size:1rem; font-weight:700;">{{ $grupoLabel }}</h3>
                            <p style="margin:2px 0 0; font-size:12px; color:#94a3b8;">{{ $items->count() }} opcion(es) en esta lista</p>
                        </div>
                        <a href="{{ route('admin.catalogos.create', ['grupo' => $grupo, 'tab' => $tabActivo]) }}" class="btn btn-secondary" style="font-size:12px;">+ Agregar opcion</a>
                    </div>

                    <div class="desktop-only table-scroll">
                        <table class="table" style="width:100%; margin:0;">
                            <thead>
                                <tr>
                                    <th>Lo que ve el usuario</th>
                                    <th style="width:100px; text-align:center;">Visible</th>
                                    <th style="width:160px; text-align:right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $opcion)
                                    <tr>
                                        <td>
                                            <div style="font-weight:500;">{{ $opcion->valor }}</div>
                                            @if($opcion->descripcion)
                                                <div style="font-size:11px; color:#94a3b8;">{{ \Illuminate\Support\Str::limit($opcion->descripcion, 80) }}</div>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if($opcion->es_sistema)
                                                <span style="padding:5px 12px; border-radius:20px; font-size:11px; background:var(--surface-2); color:var(--text-muted);" title="Esta opcion la usa el sistema, no se puede ocultar">Fija</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.catalogos.toggle', $opcion) }}" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            style="padding:5px 12px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; border:none;
                                                                   background:{{ $opcion->activo ? 'var(--success-light)' : 'var(--surface-2)' }};
                                                                   color:{{ $opcion->activo ? 'var(--success)' : 'var(--text-muted)' }};">
                                                        {{ $opcion->activo ? 'Si' : 'No' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td style="text-align:right; white-space:nowrap;">
                                            <a href="{{ route('admin.catalogos.edit', $opcion) }}" class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">Editar</a>
                                            @if(! $opcion->es_sistema)
                                                <button type="button" onclick="rhModal('{{ route('admin.catalogos.accion.modal', [$opcion, 'eliminar']) }}')" class="btn btn-danger" style="padding:5px 12px; font-size:12px;">Borrar</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mobile-only">
                        <div class="candidate-mobile-list">
                            @foreach($items as $opcion)
                                <article class="candidate-mobile-card">
                                    <div class="candidate-inline-meta">
                                        <div>
                                            <h3 class="candidate-mobile-card-title">{{ $opcion->valor }}</h3>
                                            <p class="candidate-mobile-card-subtitle">{{ $opcion->clave }}</p>
                                        </div>
                                        @if($opcion->es_sistema)
                                            <span class="badge badge-gray">Fija</span>
                                        @else
                                            <span class="badge {{ $opcion->activo ? 'badge-green' : 'badge-gray' }}">{{ $opcion->activo ? 'Visible' : 'Oculta' }}</span>
                                        @endif
                                    </div>

                                    <div class="candidate-mobile-meta">
                                        <div>
                                            <p class="candidate-mobile-meta-label">Grupo</p>
                                            <p class="candidate-mobile-meta-value">{{ $grupoLabel }}</p>
                                        </div>
                                        <div>
                                            <p class="candidate-mobile-meta-label">Descripcion</p>
                                            <p class="candidate-mobile-meta-value">{{ $opcion->descripcion ?: 'Sin descripcion' }}</p>
                                        </div>
                                    </div>

                                    <div class="candidate-actions" style="margin-top:14px;">
                                        @if(! $opcion->es_sistema)
                                            <form method="POST" action="{{ route('admin.catalogos.toggle', $opcion) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-secondary btn-sm">{{ $opcion->activo ? 'Ocultar' : 'Mostrar' }}</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.catalogos.edit', $opcion) }}" class="btn btn-secondary btn-sm">Editar</a>
                                        @if(! $opcion->es_sistema)
                                            <button type="button" onclick="rhModal('{{ route('admin.catalogos.accion.modal', [$opcion, 'eliminar']) }}')" class="btn btn-danger btn-sm">Borrar</button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:60px 20px;">
                    <p style="font-size:1rem; margin:0 0 6px;">No hay opciones que mostrar.</p>
                    <p style="font-size:13px; color:#94a3b8; margin:0 0 16px;">Elige una lista del menu o agrega una nueva opcion.</p>
                </div>
            @endforelse
        </div>
    @endif
</x-app-layout>
