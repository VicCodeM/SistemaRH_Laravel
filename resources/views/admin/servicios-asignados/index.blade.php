@php
    $estadoActual = request('estado', '');

    $linkTab = fn ($estado) => route('admin.tareas.index', array_merge(
        request()->except(['page', 'estado']),
        $estado ? ['estado' => $estado] : []
    ));

    $linkOrden = function (string $campo) {
        $sortActual = request('sort');
        $dirActual = request('dir', 'desc');
        $nuevaDir = ($sortActual === $campo && $dirActual === 'asc') ? 'desc' : 'asc';

        return route('admin.tareas.index', array_merge(
            request()->except(['page', 'sort', 'dir']),
            ['sort' => $campo, 'dir' => $nuevaDir]
        ));
    };

    $flecha = function (string $campo) use ($sort, $dir) {
        if ($sort !== $campo) {
            return '';
        }

        return $dir === 'asc' ? ' ^' : ' v';
    };

    $tabs = [
        '' => ['label' => 'Todas', 'count' => null, 'bg' => 'var(--surface-2)', 'fg' => 'var(--text)', 'br' => 'var(--border)'],
        'pendiente' => ['label' => 'Pendientes', 'count' => $stats['pendientes'], 'bg' => '#fffbeb', 'fg' => '#d97706', 'br' => '#fcd34d'],
        'activo' => ['label' => 'Activas', 'count' => $stats['activas'], 'bg' => '#eff6ff', 'fg' => '#2563eb', 'br' => '#93c5fd'],
        'en_proceso' => ['label' => 'En proceso', 'count' => $stats['en_proceso'], 'bg' => '#fef3c7', 'fg' => '#92400e', 'br' => '#fbbf24'],
        'completado' => ['label' => 'Completadas', 'count' => $stats['completadas'], 'bg' => '#f0fdf4', 'fg' => '#16a34a', 'br' => '#86efac'],
        'cancelado' => ['label' => 'Canceladas', 'count' => $stats['canceladas'], 'bg' => '#f8fafc', 'fg' => '#64748b', 'br' => '#cbd5e1'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Pedidos de servicio</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Pedidos de servicio</h1>
                <p class="page-subtitle">Capacitaciones, coaching y otros servicios solicitados por empresas o candidatos.</p>
            </div>
            <div class="toolbar-wrap">
                <a href="{{ route('admin.tareas.exportar.csv', request()->query()) }}" class="btn btn-secondary" style="font-size:13px;">Excel</a>
                <a href="{{ route('admin.tareas.exportar.pdf', request()->query()) }}" target="_blank" class="btn btn-secondary" style="font-size:13px;">PDF</a>
                <a href="{{ route('admin.tareas.kanban', request()->only(['servicio_id', 'solicitante_tipo', 'interno_id', 'buscar'])) }}" class="btn btn-secondary" style="font-size:13px;">Kanban</a>
                <a href="{{ route('admin.tareas.crear') }}" class="btn btn-primary">+ Registrar pedido</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div style="display:flex; gap:6px; margin-bottom:14px; flex-wrap:wrap;">
        @foreach($tabs as $estadoKey => $cfg)
            @php $activo = $estadoActual === $estadoKey; @endphp
            <a href="{{ $linkTab($estadoKey) }}"
               style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none;
                      background:{{ $activo ? $cfg['bg'] : 'transparent' }};
                      color:{{ $activo ? $cfg['fg'] : 'var(--text-muted)' }};
                      border:1px solid {{ $activo ? $cfg['br'] : 'transparent' }};">
                {{ $cfg['label'] }}
                @if($cfg['count'] !== null && $cfg['count'] > 0)
                    <span style="margin-left:6px; background:{{ $cfg['fg'] }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $cfg['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <form method="GET" style="background:var(--surface-2); border:1px solid var(--border); border-radius:10px; padding:14px; margin-bottom:14px; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px; align-items:end;">
        @if($estadoActual)
            <input type="hidden" name="estado" value="{{ $estadoActual }}">
        @endif
        @if(request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if(request('dir'))
            <input type="hidden" name="dir" value="{{ request('dir') }}">
        @endif

        <div>
            <label class="form-label" style="font-size:11px;">Servicio</label>
            <select name="servicio_id" class="form-input" style="font-size:13px; padding:7px 10px;" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($serviciosCatalogo as $s)
                    <option value="{{ $s->id }}" @selected(request('servicio_id') == $s->id)>{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" style="font-size:11px;">Solicitante</label>
            <select name="solicitante_tipo" class="form-input" style="font-size:13px; padding:7px 10px;" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="empresa" @selected(request('solicitante_tipo') === 'empresa')>Empresas</option>
                <option value="candidato" @selected(request('solicitante_tipo') === 'candidato')>Candidatos</option>
                <option value="interno" @selected(request('solicitante_tipo') === 'interno')>Internos</option>
            </select>
        </div>

        <div>
            <label class="form-label" style="font-size:11px;">Responsable interno</label>
            <select name="interno_id" class="form-input" style="font-size:13px; padding:7px 10px;" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="sin" @selected(request('interno_id') === 'sin')>Sin asignar</option>
                @foreach($internosLista as $i)
                    <option value="{{ $i->id }}" @selected(request('interno_id') == $i->id)>{{ $i->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" style="font-size:11px;">Urgencia (sin avanzar)</label>
            <select name="urgencia" class="form-input" style="font-size:13px; padding:7px 10px;" onchange="this.form.submit()">
                <option value="">Todas</option>
                <option value="3" @selected(request('urgencia') === '3')>Mas de 3 dias</option>
                <option value="7" @selected(request('urgencia') === '7')>Mas de 7 dias</option>
                <option value="14" @selected(request('urgencia') === '14')>Mas de 14 dias</option>
            </select>
        </div>

        <div style="grid-column:span 2;">
            <label class="form-label" style="font-size:11px;">Buscar</label>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Servicio, interno, empresa..." class="form-input" style="flex:1; min-width:200px; font-size:13px; padding:7px 10px;" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                <button type="submit" class="btn btn-primary" style="font-size:13px;">Buscar</button>
                @if(request()->hasAny(['buscar', 'servicio_id', 'solicitante_tipo', 'interno_id', 'urgencia']))
                    <a href="{{ route('admin.tareas.index', $estadoActual ? ['estado' => $estadoActual] : []) }}" class="btn btn-secondary" style="font-size:13px;">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="table-wrapper">
        @if($tareas->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                @if($estadoActual === 'pendiente')
                    No hay pedidos pendientes. Todo al dia.
                @else
                    No hay pedidos que coincidan con los filtros.
                @endif
            </div>
        @else
            <div class="desktop-only table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th><a href="{{ $linkOrden('servicio') }}" style="color:inherit; text-decoration:none;">Servicio{{ $flecha('servicio') }}</a></th>
                            <th>Solicitante</th>
                            <th>Responsable</th>
                            <th><a href="{{ $linkOrden('estado') }}" style="color:inherit; text-decoration:none;">Estado{{ $flecha('estado') }}</a></th>
                            <th style="text-align:center;"><a href="{{ $linkOrden('dias') }}" style="color:inherit; text-decoration:none;">Dias{{ $flecha('dias') }}</a></th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareas as $tarea)
                            @php
                                $dias = $tarea->created_at ? (int) $tarea->created_at->diffInDays(now()) : 0;
                                $urgente = in_array($tarea->estado, ['pendiente', 'activo'], true) && $dias >= 3;
                                $tipo = \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type);
                                $avatarSolicitante = match($tarea->asignable_type) {
                                    \App\Models\Empresa::class, \App\Models\Candidato::class => $tarea->asignable?->usuario?->avatar_url,
                                    \App\Models\User::class => $tarea->asignable?->avatar_url,
                                    default => null,
                                };
                            @endphp
                            <tr style="{{ $urgente ? 'background:rgba(254,226,226,.25);' : '' }}">
                                <td>
                                    <div style="font-weight:600;">
                                        @if($urgente)
                                            <span title="Urgente: {{ $dias }} dias sin avanzar" style="color:#dc2626;">!</span>
                                        @endif
                                        {{ $tarea->servicio?->nombre ?? 'Servicio' }}
                                    </div>
                                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</div>
                                </td>
                                <td style="font-size:13px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <x-avatar :src="$avatarSolicitante" :nombre="$tarea->asignableNombre()" :tamano="28" />
                                        <div style="min-width:0;">
                                            <div style="font-weight:500;">{{ $tarea->asignableNombre() }}</div>
                                            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ $tipo }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:13px;">
                                    @if($tarea->asignadoA)
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="28" />
                                            <div style="font-weight:500;">{{ $tarea->asignadoA->name }}</div>
                                        </div>
                                    @else
                                        <span class="badge badge-orange" style="font-size:11px;">Sin asignar</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                        {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                    </span>
                                </td>
                                <td style="text-align:center; font-size:12px;">
                                    <span style="color:{{ $urgente ? '#dc2626' : '#64748b' }}; font-weight:{{ $urgente ? '600' : 'normal' }};">{{ $dias }}d</span>
                                    <div style="font-size:10px; color:#94a3b8;">{{ $tarea->created_at?->format('d/m/Y') ?? '—' }}</div>
                                </td>
                                <td style="white-space:nowrap; text-align:right;">
                                    <div class="toolbar-wrap" style="justify-content:flex-end;">
                                        @if($tarea->estado === 'pendiente' && ! $tarea->asignado_a)
                                            <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-primary btn-sm">Asignar</a>
                                        @elseif($tarea->estado === 'activo')
                                            <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="en_proceso">
                                                <button type="submit" class="btn btn-primary btn-sm">Iniciar</button>
                                            </form>
                                        @elseif($tarea->estado === 'en_proceso')
                                            <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="completado">
                                                <button type="submit" class="btn btn-success btn-sm">Completar</button>
                                            </form>
                                        @elseif(in_array($tarea->estado, ['completado', 'cancelado'], true))
                                            <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'reabrir']) }}')" class="btn btn-secondary btn-sm">Reabrir</button>
                                        @endif

                                        @if($tarea->estado !== 'pendiente' || $tarea->asignado_a)
                                            <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-secondary btn-sm">Interno</a>
                                        @endif

                                        <a href="{{ route('admin.tareas.show', $tarea) }}" class="btn btn-ghost" style="padding:4px 10px; font-size:12px;">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($tareas as $tarea)
                        @php
                            $dias = $tarea->created_at ? (int) $tarea->created_at->diffInDays(now()) : 0;
                            $urgente = in_array($tarea->estado, ['pendiente', 'activo'], true) && $dias >= 3;
                            $tipo = \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type);
                        @endphp
                        <article class="candidate-mobile-card" style="{{ $urgente ? 'border-color:rgba(239,68,68,.35); box-shadow:0 0 0 1px rgba(239,68,68,.06);' : '' }}">
                            <div class="candidate-inline-meta">
                                <div style="min-width:0;">
                                    <h3 class="candidate-mobile-card-title">
                                        @if($urgente)
                                            <span style="color:#dc2626;">!</span>
                                        @endif
                                        {{ $tarea->servicio?->nombre ?? 'Servicio' }}
                                    </h3>
                                    <p class="candidate-mobile-card-subtitle">{{ $tipo }} · {{ $tarea->asignableNombre() }}</p>
                                </div>
                                <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">
                                    {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Nivel</p>
                                    <p class="candidate-mobile-meta-value">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($tarea->servicio?->nivel_jerarquico) }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Solicitante</p>
                                    <p class="candidate-mobile-meta-value">{{ $tarea->asignableNombre() }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Responsable</p>
                                    <p class="candidate-mobile-meta-value">{{ $tarea->asignadoA?->name ?? 'Sin asignar' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Dias abiertos</p>
                                    <p class="candidate-mobile-meta-value">{{ $dias }} dia(s)</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Creado</p>
                                    <p class="candidate-mobile-meta-value">{{ $tarea->created_at?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                            </div>

                            @if($urgente)
                                <div style="margin-top:12px; padding:10px 12px; border-radius:10px; background:rgba(254,226,226,.45); color:#b91c1c; font-size:0.82rem;">
                                    Este pedido lleva {{ $dias }} dias sin avanzar.
                                </div>
                            @endif

                            <div class="candidate-actions" style="margin-top:14px;">
                                @if($tarea->estado === 'pendiente' && ! $tarea->asignado_a)
                                    <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-primary btn-sm">Asignar</a>
                                @elseif($tarea->estado === 'activo')
                                    <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="en_proceso">
                                        <button type="submit" class="btn btn-primary btn-sm">Iniciar</button>
                                    </form>
                                @elseif($tarea->estado === 'en_proceso')
                                    <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="completado">
                                        <button type="submit" class="btn btn-success btn-sm">Completar</button>
                                    </form>
                                @elseif(in_array($tarea->estado, ['completado', 'cancelado'], true))
                                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'reabrir']) }}')" class="btn btn-secondary btn-sm">Reabrir</button>
                                @endif

                                @if($tarea->estado !== 'pendiente' || $tarea->asignado_a)
                                    <a href="{{ route('admin.tareas.matching', $tarea) }}" class="btn btn-secondary btn-sm">Interno</a>
                                @endif

                                <a href="{{ route('admin.tareas.show', $tarea) }}" class="btn btn-secondary btn-sm">Ver</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-size:12px; color:#64748b;">Mostrando {{ $tareas->firstItem() }}-{{ $tareas->lastItem() }} de {{ $tareas->total() }}</span>
                {{ $tareas->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
