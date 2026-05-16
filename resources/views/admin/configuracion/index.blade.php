@php
    $tabActivo = $tabActivo ?? request('tab', 'usuarios');
    $baseQuery = request()->except(['tab', 'page']);
    $tabs = [
        'usuarios' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'usuarios'])),
            'titulo' => 'Usuarios',
            'detalle' => 'Alta, edición, bloqueo y recuperación.',
        ],
        'bitacora' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'bitacora'])),
            'titulo' => 'Bitácora',
            'detalle' => 'Trazabilidad de acciones importantes.',
        ],
        'seguridad' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'seguridad'])),
            'titulo' => 'Seguridad',
            'detalle' => 'Acceso, estados y recuperación.',
        ],
        'parametros' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'parametros'])),
            'titulo' => 'Parámetros',
            'detalle' => 'Reglas base del sistema.',
        ],
        'catalogos' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'catalogos'])),
            'titulo' => 'Catálogos',
            'detalle' => 'Accesos rápidos a catálogos y opciones.',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Configuración</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">Configuración</h1>
                <p class="page-subtitle">Usuarios, accesos, bitácora, parámetros y catálogos en un solo hub.</p>
            </div>

            @if($tabActivo === 'usuarios')
                <button onclick="rhModal('{{ route('admin.configuracion.usuarios.nuevo') }}')" class="btn btn-primary">
                    + Nuevo usuario
                </button>
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
                <span class="metric-label">Usuarios totales</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;">U</div>
            </div>
            <div class="metric-value">{{ $stats['total'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Usuarios activos</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">A</div>
            </div>
            <div class="metric-value">{{ $stats['activos'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Usuarios bloqueados</span>
                <div class="metric-icon" style="background:rgba(239,68,68,.12);color:#ef4444;">B</div>
            </div>
            <div class="metric-value">{{ $stats['bloqueados'] }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Equipo interno</span>
                <div class="metric-icon" style="background:rgba(168,85,247,.12);color:#a855f7;">I</div>
            </div>
            <div class="metric-value">{{ $stats['internos'] }}</div>
        </div>
    </div>

    <div class="card fade-in" style="margin-top:24px; padding:0; overflow:hidden;">
        <div style="padding:16px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, rgba(15,23,42,.02), rgba(255,255,255,.85));">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                @foreach($tabs as $key => $tab)
                    <a href="{{ $tab['ruta'] }}"
                       style="display:block; padding:14px 16px; border-radius:16px; text-decoration:none; border:1px solid {{ $tabActivo === $key ? 'rgba(37,99,235,.28)' : 'var(--border)' }}; background:{{ $tabActivo === $key ? 'linear-gradient(135deg, rgba(37,99,235,.12), rgba(37,99,235,.04))' : 'var(--surface)' }}; box-shadow:{{ $tabActivo === $key ? '0 10px 24px rgba(37,99,235,.10)' : 'none' }}; color:var(--text);">
                        <div style="font-size:0.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#64748b;">Sección {{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                        <div style="margin-top:6px; font-weight:800; font-size:0.98rem;">{{ $tab['titulo'] }}</div>
                        <div style="margin-top:4px; font-size:0.84rem; color:#64748b;">{{ $tab['detalle'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div style="padding:20px;">
            @if($tabActivo === 'usuarios')
                <div class="card" style="margin-bottom:20px;">
                    <form method="GET" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
                        <input type="hidden" name="tab" value="usuarios">
                        <div>
                            <label class="form-label">Buscar</label>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-input" placeholder="Nombre o correo...">
                        </div>
                        <div>
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-input">
                                <option value="">Todos</option>
                                @foreach(\App\Models\User::roles() as $key => $label)
                                    <option value="{{ $key }}" {{ request('rol') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-input">
                                <option value="">Todos</option>
                                @foreach(\App\Models\User::estados() as $key => $label)
                                    <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            @if(request()->hasAny(['buscar', 'rol', 'estado']))
                                <a href="{{ route('admin.configuracion', ['tab' => 'usuarios']) }}" class="btn btn-secondary">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Perfil</th>
                                <th>Acceso</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:var(--text);">{{ $usuario->name }}</div>
                                        <div style="font-size:0.82rem; color:#64748b;">{{ $usuario->email }}</div>
                                    </td>
                                    <td><span class="badge badge-blue">{{ \App\Models\User::rolLabel($usuario->rol) }}</span></td>
                                    <td><span class="badge {{ \App\Models\User::estadoBadgeClass($usuario->estado) }}">{{ \App\Models\User::estadoLabel($usuario->estado) }}</span></td>
                                    <td style="font-size:0.82rem; color:#64748b;">
                                        @if($usuario->empresa)
                                            Empresa: {{ $usuario->empresa->nombre_empresa }}
                                        @elseif($usuario->candidato)
                                            Candidato: {{ $usuario->candidato->nombreCompleto() }}
                                        @else
                                            Sin perfil vinculado
                                        @endif
                                    </td>
                                    <td style="font-size:0.82rem; color:#64748b;">
                                        {{ $usuario->email_verified_at ? 'Verificado' : 'Pendiente' }}
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap;">
                                            <button onclick="rhModal('{{ route('admin.configuracion.usuarios.modal', $usuario) }}')" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</button>
                                            <form method="POST" action="{{ route('admin.configuracion.usuarios.estado', $usuario) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">
                                                    {{ $usuario->estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.configuracion.usuarios.recuperar', $usuario) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.8rem;">Reenviar acceso</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:36px; color:#64748b;">
                                        No hay usuarios que coincidan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px;">
                    {{ $usuarios->links() }}
                </div>
            @elseif($tabActivo === 'bitacora')
                <div class="card" style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Bitácora reciente</h2>
                            <p style="margin:0; color:#64748b; font-size:0.84rem;">Acciones importantes registradas por el sistema.</p>
                        </div>
                        <span class="badge badge-blue">{{ $bitacoras->count() }} evento(s)</span>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Usuario</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bitacoras as $evento)
                                <tr>
                                    <td style="font-size:0.82rem; color:#64748b;">{{ $evento->created_at?->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge badge-blue">{{ $evento->modulo }}</span></td>
                                    <td style="font-weight:600;">{{ $evento->accion }}</td>
                                    <td style="font-size:0.82rem; color:#64748b;">{{ $evento->usuario?->name ?? 'Sistema' }}</td>
                                    <td style="font-size:0.82rem; color:#64748b;">{{ $evento->detalle ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:36px; color:#64748b;">Todavía no hay eventos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($tabActivo === 'seguridad')
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Recuperación de acceso</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Desde el modal de cada usuario puedes reenviar el enlace de restablecimiento de contraseña.
                        </p>
                    </div>
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Control de estados</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Puedes bloquear, desbloquear o reactivar cuentas sin crear duplicados.
                        </p>
                    </div>
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Auditoría</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Cada cambio importante se registra en la bitácora para mantener trazabilidad.
                        </p>
                    </div>
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Equipo interno</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Filtra por rol <strong>Interno</strong> para administrar el personal operativo del sistema.
                        </p>
                    </div>
                </div>
            @elseif($tabActivo === 'parametros')
                <form method="POST" action="{{ route('admin.configuracion.parametros.guardar') }}" class="card" style="max-width:920px;">
                    @csrf
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Parámetros del sistema</h2>
                            <p style="margin:0; color:#64748b; font-size:0.84rem; line-height:1.6;">
                                Ajustes cortos que cambian el comportamiento del sistema sin tocar código.
                            </p>
                        </div>
                        <span class="badge badge-blue">Configuración</span>
                    </div>

                    <div style="margin-top:18px; padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-2);">
                        <label style="display:flex; gap:12px; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" name="candidato_requiere_aprobacion" value="1" {{ $parametros['candidato_requiere_aprobacion'] ? 'checked' : '' }} style="margin-top:4px; width:18px; height:18px;">
                            <div>
                                <div style="font-weight:700; color:var(--text);">Requerir aprobación previa de candidatos</div>
                                <div style="margin-top:4px; color:#64748b; font-size:0.84rem; line-height:1.6;">
                                    Si se activa, cada candidato quedará en revisión al registrarse y no podrá completar su solicitud hasta que el admin lo active.
                                    Si se desactiva, seguirá el flujo actual y podrá avanzar directamente a su solicitud.
                                </div>
                            </div>
                        </label>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:18px;">
                        <button type="submit" class="btn btn-primary">Guardar parámetros</button>
                    </div>
                </form>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Catálogo de servicios</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Servicios primero, opciones reutilizables después.</p>
                    </a>
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'opciones']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Opciones del sistema</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Estados, roles y catálogos generales en un solo hub.</p>
                    </a>
                    <a href="{{ route('admin.configuracion', ['tab' => 'usuarios', 'rol' => 'interno']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Equipo interno</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Administra aquí los usuarios con rol <strong>Interno</strong>.</p>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
