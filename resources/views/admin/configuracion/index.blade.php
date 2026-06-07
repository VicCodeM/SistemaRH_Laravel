@php
    $tabActivo = $tabActivo ?? request('tab', 'usuarios');
    $baseQuery = request()->except(['tab', 'page']);
    $tabs = [
        'usuarios' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'usuarios'])),
            'titulo' => 'Usuarios',
            'detalle' => 'Alta, edicion, bloqueo y recuperacion.',
        ],
        'bitacora' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'bitacora'])),
            'titulo' => 'Bitacora',
            'detalle' => 'Trazabilidad de acciones importantes.',
        ],
        'seguridad' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'seguridad'])),
            'titulo' => 'Seguridad',
            'detalle' => 'Acceso, estados y recuperacion.',
        ],
        'parametros' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'parametros'])),
            'titulo' => 'Parametros',
            'detalle' => 'Reglas base del sistema y acceso por municipio.',
        ],
        'sitio' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'sitio'])),
            'titulo' => 'Sitio web',
            'detalle' => 'Nombre, logo, favicon, landing y paginas legales.',
        ],
        'catalogos' => [
            'ruta' => route('admin.configuracion', array_merge($baseQuery, ['tab' => 'catalogos'])),
            'titulo' => 'Catalogos',
            'detalle' => 'Accesos rapidos a catalogos y opciones.',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Configuracion</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Configuracion</h1>
                <p class="page-subtitle">Usuarios, accesos, bitacora, parametros y catalogos en un solo hub.</p>
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
                        <div style="font-size:0.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#64748b;">Seccion {{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</div>
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
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-input" placeholder="Nombre o correo..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
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
                        <div class="toolbar-wrap">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            @if(request()->hasAny(['buscar', 'rol', 'estado']))
                                <a href="{{ route('admin.configuracion', ['tab' => 'usuarios']) }}" class="btn btn-secondary">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-wrapper">
                    @if($usuarios->isEmpty())
                        <div style="text-align:center; padding:36px; color:#64748b;">
                            No hay usuarios que coincidan.
                        </div>
                    @else
                        <div class="desktop-only table-scroll">
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
                                    @foreach($usuarios as $usuario)
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <x-avatar :src="$usuario->avatar_url" :nombre="$usuario->name" :tamano="32" />
                                                    <div>
                                                        <div style="font-weight:600; color:var(--text);">{{ $usuario->name }}</div>
                                                        <div style="font-size:0.82rem; color:#64748b;">{{ $usuario->email }}</div>
                                                    </div>
                                                </div>
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
                                                <div class="toolbar-wrap" style="justify-content:flex-end;">
                                                    <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.modal', $usuario) }}')" class="btn btn-secondary btn-sm">Editar</button>
                                                    <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.accion.modal', [$usuario, $usuario->estado === 'bloqueado' ? 'desbloquear' : 'bloquear']) }}')" class="btn btn-secondary btn-sm">
                                                        {{ $usuario->estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear' }}
                                                    </button>
                                                    <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.accion.modal', [$usuario, 'recuperar']) }}')" class="btn btn-primary btn-sm">Reenviar acceso</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mobile-only">
                            <div class="candidate-mobile-list">
                                @foreach($usuarios as $usuario)
                                    <article class="candidate-mobile-card">
                                        <div class="candidate-inline-meta">
                                            <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                                <x-avatar :src="$usuario->avatar_url" :nombre="$usuario->name" :tamano="40" />
                                                <div style="min-width:0;">
                                                    <h3 class="candidate-mobile-card-title">{{ $usuario->name }}</h3>
                                                    <p class="candidate-mobile-card-subtitle">{{ $usuario->email }}</p>
                                                </div>
                                            </div>
                                            <span class="badge {{ \App\Models\User::estadoBadgeClass($usuario->estado) }}">{{ \App\Models\User::estadoLabel($usuario->estado) }}</span>
                                        </div>

                                        <div class="candidate-mobile-meta">
                                            <div>
                                                <p class="candidate-mobile-meta-label">Rol</p>
                                                <p class="candidate-mobile-meta-value">{{ \App\Models\User::rolLabel($usuario->rol) }}</p>
                                            </div>
                                            <div>
                                                <p class="candidate-mobile-meta-label">Perfil</p>
                                                <p class="candidate-mobile-meta-value">
                                                    @if($usuario->empresa)
                                                        Empresa: {{ $usuario->empresa->nombre_empresa }}
                                                    @elseif($usuario->candidato)
                                                        Candidato: {{ $usuario->candidato->nombreCompleto() }}
                                                    @else
                                                        Sin perfil vinculado
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="candidate-mobile-meta-label">Acceso</p>
                                                <p class="candidate-mobile-meta-value">{{ $usuario->email_verified_at ? 'Verificado' : 'Pendiente' }}</p>
                                            </div>
                                        </div>

                                        <div class="candidate-actions" style="margin-top:14px;">
                                            <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.modal', $usuario) }}')" class="btn btn-secondary btn-sm">Editar</button>
                                            <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.accion.modal', [$usuario, $usuario->estado === 'bloqueado' ? 'desbloquear' : 'bloquear']) }}')" class="btn btn-secondary btn-sm">
                                                {{ $usuario->estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear' }}
                                            </button>
                                            <button type="button" onclick="rhModal('{{ route('admin.configuracion.usuarios.accion.modal', [$usuario, 'recuperar']) }}')" class="btn btn-primary btn-sm">Reenviar acceso</button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div style="margin-top:18px;">
                    {{ $usuarios->links() }}
                </div>
            @elseif($tabActivo === 'bitacora')
                <div class="card" style="margin-bottom:20px;">
                    <div class="candidate-inline-meta">
                        <div>
                            <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Bitacora reciente</h2>
                            <p style="margin:0; color:#64748b; font-size:0.84rem;">Acciones importantes registradas por el sistema.</p>
                        </div>
                        <span class="badge badge-blue">{{ $bitacoras->count() }} evento(s)</span>
                    </div>
                </div>

                <div class="table-wrapper">
                    @if($bitacoras->isEmpty())
                        <div style="text-align:center; padding:36px; color:#64748b;">Todavia no hay eventos registrados.</div>
                    @else
                        <div class="desktop-only table-scroll">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Modulo</th>
                                        <th>Accion</th>
                                        <th>Usuario</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bitacoras as $evento)
                                        <tr>
                                            <td style="font-size:0.82rem; color:#64748b;">{{ $evento->created_at?->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge badge-blue">{{ $evento->modulo }}</span></td>
                                            <td style="font-weight:600;">{{ $evento->accion }}</td>
                                            <td>
                                                @if($evento->usuario)
                                                    <div style="display:flex; align-items:center; gap:6px;">
                                                        <x-avatar :src="$evento->usuario->avatar_url" :nombre="$evento->usuario->name" :tamano="22" />
                                                        <span style="font-size:0.82rem;">{{ $evento->usuario->name }}</span>
                                                    </div>
                                                @else
                                                    <span style="font-size:0.82rem; color:#94a3b8;">Sistema</span>
                                                @endif
                                            </td>
                                            <td style="font-size:0.82rem; color:#64748b;">{{ $evento->detalle ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mobile-only">
                            <div class="candidate-mobile-list">
                                @foreach($bitacoras as $evento)
                                    <article class="candidate-mobile-card">
                                        <div class="candidate-inline-meta">
                                            <div>
                                                <h3 class="candidate-mobile-card-title">{{ $evento->accion }}</h3>
                                                <p class="candidate-mobile-card-subtitle">{{ $evento->created_at?->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <span class="badge badge-blue">{{ $evento->modulo }}</span>
                                        </div>

                                        <div class="candidate-mobile-meta">
                                            <div>
                                                <p class="candidate-mobile-meta-label">Usuario</p>
                                                <p class="candidate-mobile-meta-value">{{ $evento->usuario?->name ?? 'Sistema' }}</p>
                                            </div>
                                            <div>
                                                <p class="candidate-mobile-meta-label">Detalle</p>
                                                <p class="candidate-mobile-meta-value">{{ $evento->detalle ?: '—' }}</p>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($tabActivo === 'seguridad')
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Recuperacion de acceso</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Desde el modal de cada usuario puedes reenviar el enlace de restablecimiento de contrasena.
                        </p>
                    </div>
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Control de estados</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Puedes bloquear, desbloquear o reactivar cuentas sin crear duplicados.
                        </p>
                    </div>
                    <div class="card">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Auditoria</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">
                            Cada cambio importante se registra en la bitacora para mantener trazabilidad.
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
                    <div class="candidate-inline-meta">
                        <div>
                            <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Parametros del sistema</h2>
                            <p style="margin:0; color:#64748b; font-size:0.84rem; line-height:1.6;">
                                Ajustes cortos que cambian el comportamiento del sistema sin tocar codigo.
                            </p>
                        </div>
                        <span class="badge badge-blue">Configuracion</span>
                    </div>

                    <div style="margin-top:18px; padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-2);">
                        <label style="display:flex; gap:12px; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" name="candidato_requiere_aprobacion" value="1" {{ $parametros['candidato_requiere_aprobacion'] ? 'checked' : '' }} style="margin-top:4px; width:18px; height:18px;">
                            <div>
                                <div style="font-weight:700; color:var(--text);">Requerir aprobacion previa de candidatos</div>
                                <div style="margin-top:4px; color:#64748b; font-size:0.84rem; line-height:1.6;">
                                    Si se activa, cada candidato quedara en revision al registrarse y no podra completar su solicitud hasta que el admin lo active.
                                    Si se desactiva, seguira el flujo actual y podra avanzar directamente a su solicitud.
                                </div>
                            </div>
                        </label>
                    </div>


                    <div style="margin-top:18px; padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-2);">
                        <label style="display:flex; gap:12px; align-items:flex-start; cursor:pointer;">
                            <input type="checkbox" name="acceso_municipios_todos" value="1" {{ $parametros['acceso_municipios_todos'] ? 'checked' : '' }} style="margin-top:4px; width:18px; height:18px;">
                            <div>
                                <div style="font-weight:700; color:var(--text);">Permitir acceso desde todos los municipios</div>
                                <div style="margin-top:4px; color:#64748b; font-size:0.84rem; line-height:1.6;">
                                    Si se desactiva, solo podran entrar los municipios que escribas abajo.
                                </div>
                            </div>
                        </label>
                    </div>

                    <div style="margin-top:16px; padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-2);">
                        <div style="font-weight:700; color:var(--text); margin-bottom:6px;">Municipios permitidos</div>
                        <p style="margin:0 0 10px; color:#64748b; font-size:0.84rem; line-height:1.6;">Escribe uno por linea. Solo aplica cuando la opcion anterior esta desactivada.</p>
                        <textarea name="acceso_municipios_permitidos" class="form-input" rows="6" placeholder="Monterrey&#10;Guadalupe&#10;Apodaca" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('acceso_municipios_permitidos', $parametros['acceso_municipios_permitidos_texto']) }}</textarea>
                    </div>
                    <div class="toolbar-wrap" style="margin-top:18px;">
                        <button type="submit" class="btn btn-primary">Guardar parametros</button>
                    </div>
                </form>
            @elseif($tabActivo === 'sitio')
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        Revisa los campos: {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.configuracion.sitio.guardar') }}" enctype="multipart/form-data" data-no-spa style="max-width:920px;">
                    @csrf

                    {{-- Identidad / SEO --}}
                    <div class="card" style="margin-bottom:16px;">
                        <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Identidad y SEO</h2>
                        <p style="margin:0 0 16px; color:#64748b; font-size:0.84rem;">Nombre del sitio, descripcion para buscadores, logo y favicon.</p>

                        <div style="display:grid; gap:14px;">
                            <div>
                                <label class="form-label">Nombre del sitio</label>
                                <input type="text" name="sitio_nombre" class="form-input" value="{{ old('sitio_nombre', $sitio['sitio_nombre']) }}" maxlength="120" required spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                <p style="margin:6px 0 0; font-size:0.78rem; color:#94a3b8;">La segunda parte se muestra en azul (ej. "Sistema<strong>RH</strong>" o pon un espacio: "Mi Empresa RH").</p>
                            </div>
                            <div>
                                <label class="form-label">Subtitulo / eslogan</label>
                                <input type="text" name="sitio_subtitulo" class="form-input" value="{{ old('sitio_subtitulo', $sitio['sitio_subtitulo']) }}" maxlength="120" placeholder="Ej. Gestion de talento" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                <p style="margin:6px 0 0; font-size:0.78rem; color:#94a3b8;">Aparece bajo el nombre en login y registro.</p>
                            </div>
                            <div>
                                <label class="form-label">Descripcion (SEO)</label>
                                <textarea name="sitio_descripcion" class="form-input" rows="2" maxlength="300" placeholder="Frase corta que aparece en buscadores..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('sitio_descripcion', $sitio['sitio_descripcion']) }}</textarea>
                            </div>
                            <div>
                                @php
                                    $logoSitioActual = !empty($sitio['sitio_logo'])
                                        ? $sitio['sitio_logo']
                                        : (!empty($sitio['sitio_favicon']) ? $sitio['sitio_favicon'] : null);
                                @endphp
                                <label class="form-label">Logo para correos y marca</label>
                                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                                    @if($logoSitioActual)
                                        <img src="{{ asset('storage/' . $logoSitioActual) }}" alt="logo actual"
                                             style="width:64px; height:64px; border-radius:14px; border:1px solid var(--border); object-fit:contain; background:#fff;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                        <span style="display:none; font-size:0.78rem; color:var(--danger, #dc2626);">No se pudo cargar. Ejecuta: php artisan storage:link</span>
                                    @else
                                        <span style="font-size:0.82rem; color:#94a3b8;">Sin logo configurado</span>
                                    @endif
                                    <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg,.ico" class="form-input" style="max-width:320px;">
                                </div>
                                <p style="margin:6px 0 0; font-size:0.78rem; color:#94a3b8;">PNG, JPG, WEBP, SVG o ICO. Maximo 1 MB. Este logo se usa primero en los correos; si no hay uno, se toma el favicon.</p>
                                @if(!empty($sitio['sitio_logo']))
                                    <label style="display:flex; align-items:center; gap:8px; margin-top:8px; font-size:0.84rem; cursor:pointer;">
                                        <input type="checkbox" name="quitar_logo" value="1"> Quitar el logo actual
                                    </label>
                                @endif
                            </div>
                            <div>
                                <label class="form-label">Favicon (icono de la pestana)</label>
                                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                                    @if(!empty($sitio['sitio_favicon']))
                                        <img src="{{ asset('storage/' . $sitio['sitio_favicon']) }}" alt="favicon actual"
                                             style="width:40px; height:40px; border-radius:8px; border:1px solid var(--border); object-fit:contain; background:#fff;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                        <span style="display:none; font-size:0.78rem; color:var(--danger, #dc2626);">No se pudo cargar. Ejecuta: php artisan storage:link</span>
                                    @else
                                        <span style="font-size:0.82rem; color:#94a3b8;">Sin favicon</span>
                                    @endif
                                    <input type="file" name="favicon" accept=".png,.jpg,.jpeg,.webp,.svg,.ico" class="form-input" style="max-width:320px;">
                                </div>
                                <p style="margin:6px 0 0; font-size:0.78rem; color:#94a3b8;">PNG, JPG, WEBP, SVG o ICO. Maximo 1 MB. Ideal cuadrado (ej. 64x64).</p>
                                @if(!empty($sitio['sitio_favicon']))
                                    <label style="display:flex; align-items:center; gap:8px; margin-top:8px; font-size:0.84rem; cursor:pointer;">
                                        <input type="checkbox" name="quitar_favicon" value="1"> Quitar el favicon actual
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Hero del landing --}}
                    <div class="card" style="margin-bottom:16px;">
                        <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Pagina de inicio: encabezado</h2>
                        <p style="margin:0 0 16px; color:#64748b; font-size:0.84rem;">El bloque grande superior de la pagina publica.</p>

                        <div style="display:grid; gap:14px;">
                            <div>
                                <label class="form-label">Etiqueta superior</label>
                                <input type="text" name="landing_hero_badge" class="form-input" value="{{ old('landing_hero_badge', $sitio['landing_hero_badge']) }}" maxlength="120" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            </div>
                            <div>
                                <label class="form-label">Titulo principal</label>
                                <textarea name="landing_hero_titulo" class="form-input" rows="2" maxlength="200" required spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('landing_hero_titulo', $sitio['landing_hero_titulo']) }}</textarea>
                                <p style="margin:6px 0 0; font-size:0.78rem; color:#94a3b8;">Cada salto de linea se respeta en la pagina.</p>
                            </div>
                            <div>
                                <label class="form-label">Frase destacada (en color)</label>
                                <input type="text" name="landing_hero_acento" class="form-input" value="{{ old('landing_hero_acento', $sitio['landing_hero_acento']) }}" maxlength="120" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            </div>
                            <div>
                                <label class="form-label">Subtitulo</label>
                                <textarea name="landing_hero_subtitulo" class="form-input" rows="2" maxlength="400" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('landing_hero_subtitulo', $sitio['landing_hero_subtitulo']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Caracteristicas --}}
                    <div class="card" style="margin-bottom:16px;">
                        <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Pagina de inicio: caracteristicas</h2>
                        <p style="margin:0 0 16px; color:#64748b; font-size:0.84rem;">Las 5 tarjetas. Deja un titulo vacio para ocultar esa tarjeta.</p>

                        <div style="display:grid; gap:14px;">
                            <div>
                                <label class="form-label">Titulo de la seccion</label>
                                <input type="text" name="landing_feat_label" class="form-input" value="{{ old('landing_feat_label', $sitio['landing_feat_label']) }}" maxlength="120" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            </div>
                            @for($n = 1; $n <= 5; $n++)
                                <div style="padding:14px; border:1px solid var(--border); border-radius:12px; background:var(--surface-2);">
                                    <div style="font-size:0.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Tarjeta {{ $n }}</div>
                                    <div style="display:grid; gap:10px;">
                                        <input type="text" name="landing_feat_{{ $n }}_titulo" class="form-input" value="{{ old('landing_feat_'.$n.'_titulo', $sitio['landing_feat_'.$n.'_titulo']) }}" maxlength="120" placeholder="Titulo" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                        <textarea name="landing_feat_{{ $n }}_texto" class="form-input" rows="2" maxlength="300" placeholder="Descripcion" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('landing_feat_'.$n.'_texto', $sitio['landing_feat_'.$n.'_texto']) }}</textarea>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card" style="margin-bottom:16px;">
                        <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Pie de pagina</h2>
                        <div style="margin-top:12px;">
                            <label class="form-label">Texto del footer (después del año)</label>
                            <input type="text" name="landing_footer" class="form-input" value="{{ old('landing_footer', $sitio['landing_footer']) }}" maxlength="200" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                        </div>
                    </div>

                    {{-- Paginas legales --}}
                    <div class="card" style="margin-bottom:16px;">
                        <h2 style="margin:0 0 4px; font-size:1rem; font-weight:700;">Paginas legales</h2>
                        <p style="margin:0 0 16px; color:#64748b; font-size:0.84rem;">Contenido publico. Texto simple: los saltos de linea se respetan.</p>

                        <div style="display:grid; gap:14px;">
                            <div>
                                <label class="form-label">Politicas de privacidad
                                    <a href="{{ route('paginas.privacidad') }}" target="_blank" style="font-weight:400; font-size:0.8rem; color:var(--accent); margin-left:6px;">ver p&aacute;gina</a>
                                </label>
                                <textarea name="privacidad_contenido" class="form-input" rows="8" maxlength="20000" placeholder="Escribe aqui las politicas de privacidad..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('privacidad_contenido', $sitio['privacidad_contenido']) }}</textarea>
                            </div>
                            <div>
                                <label class="form-label">Terminos del servicio
                                    <a href="{{ route('paginas.terminos') }}" target="_blank" style="font-weight:400; font-size:0.8rem; color:var(--accent); margin-left:6px;">ver p&aacute;gina</a>
                                </label>
                                <textarea name="terminos_contenido" class="form-input" rows="8" maxlength="20000" placeholder="Escribe aqui los terminos del servicio..." spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">{{ old('terminos_contenido', $sitio['terminos_contenido']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="toolbar-wrap" style="margin-top:4px;">
                        <button type="submit" class="btn btn-primary">Guardar configuracion del sitio</button>
                    </div>
                </form>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'servicios']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Catalogo de servicios</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Servicios primero, opciones reutilizables despues.</p>
                    </a>
                    <a href="{{ route('admin.catalogos.index', ['tab' => 'vacantes']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Opciones del sistema</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Estados, roles y catalogos generales en un solo hub.</p>
                    </a>
                    <a href="{{ route('admin.configuracion', ['tab' => 'usuarios', 'rol' => 'interno']) }}" class="card" style="text-decoration:none; color:inherit;">
                        <h3 style="margin:0 0 8px; font-size:1rem; font-weight:700;">Equipo interno</h3>
                        <p style="margin:0; color:#64748b; font-size:0.86rem; line-height:1.6;">Administra aqui los usuarios con rol <strong>Interno</strong>.</p>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
