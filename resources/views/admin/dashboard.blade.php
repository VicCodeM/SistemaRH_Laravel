<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Panel admin</span>
        </nav>
        <h1 class="page-title">Panel de administración</h1>
        <p class="page-subtitle">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }} &mdash; Resumen RH Consulting</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    @if(!empty($alertas))
        <div style="display:grid; gap:10px; margin-bottom:20px;">
            @foreach($alertas as $alerta)
                <div class="alert alert-{{ $alerta['tipo'] }} fade-in" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <span>{{ $alerta['mensaje'] }}</span>
                    <a href="{{ $alerta['link'] }}" style="font-weight:600; text-decoration:underline; white-space:nowrap;">Ver &rarr;</a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- BLOQUE 1: ACCESO A LA PLATAFORMA --}}
    <div style="margin-bottom:8px; padding:14px 18px; background:linear-gradient(135deg, rgba(245,158,11,.06), transparent); border-left:4px solid #f59e0b; border-radius:8px;">
        <h2 style="font-size:1rem; font-weight:700; margin:0; color:var(--text-primary);">🔐 Acceso a la plataforma</h2>
        <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Aprobar o rechazar cuentas de empresas y candidatos. Estas decisiones les dan acceso al sistema.</p>
    </div>

    <div class="metrics-grid metrics-grid-2 fade-in" style="margin-top:14px;">
        <div class="metric-card" style="{{ $stats['empresas_pendientes'] > 0 ? 'border-color: rgba(245,158,11,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Empresas por aprobar acceso</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['empresas_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:var(--text-muted);">{{ $stats['empresas_activas'] }} con acceso activo</span>
                <a href="{{ route('admin.empresas', ['estado' => 'pendiente']) }}" style="font-size:11px;color:#f59e0b;text-decoration:none;">Ver &rarr;</a>
            </div>
        </div>

        <div class="metric-card" style="{{ $stats['candidatos_pendientes'] > 0 ? 'border-color: rgba(96,165,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Candidatos por revisar perfil</span>
                <div class="metric-icon" style="background:rgba(96,165,250,.12);color:#60a5fa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['candidatos_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:var(--text-muted);">{{ $stats['candidatos_aprobados'] }} aprobados</span>
                <a href="{{ route('admin.candidatos', ['estado' => 'enviada']) }}" style="font-size:11px;color:#60a5fa;text-decoration:none;">Ver &rarr;</a>
            </div>
        </div>
    </div>

    {{-- BLOQUE 2: GESTIÓN OPERATIVA --}}
    <div style="margin-top:28px; margin-bottom:8px; padding:14px 18px; background:linear-gradient(135deg, rgba(167,139,250,.06), transparent); border-left:4px solid #a78bfa; border-radius:8px;">
        <h2 style="font-size:1rem; font-weight:700; margin:0; color:var(--text-primary);">🎯 Gestión operativa</h2>
        <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Vacantes con candidatos y pedidos de servicio que necesitan asignación de personal interno. Distinto del acceso a la plataforma.</p>
    </div>

    <div class="metrics-grid metrics-grid-3 fade-in" style="margin-top:14px;">
        <div class="metric-card" style="{{ $stats['solicitudes_pendientes'] > 0 ? 'border-color: rgba(167,139,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Vacantes por asignar candidato</span>
                <div class="metric-icon" style="background:rgba(167,139,250,.12);color:#a78bfa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:var(--text-muted);">{{ $stats['solicitudes_activas'] }} en proceso</span>
                <a href="{{ route('admin.vacantes') }}" style="font-size:11px;color:#a78bfa;text-decoration:none;">Ver &rarr;</a>
            </div>
        </div>

        <div class="metric-card" style="{{ $stats['tareas_activas'] > 0 ? 'border-color: rgba(14,165,233,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Pedidos de servicio por asignar interno</span>
                <div class="metric-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['tareas_activas'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:var(--text-muted);">Capacitación, coaching, etc.</span>
                <a href="{{ route('admin.tareas.index') }}" style="font-size:11px;color:#0ea5e9;text-decoration:none;">Ver &rarr;</a>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Personal interno disponible</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['internos_activos'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:var(--text-muted);">Para asignar a pedidos</span>
                <a href="{{ route('admin.personal-interno.index') }}" style="font-size:11px;color:#22c55e;text-decoration:none;">Ver &rarr;</a>
            </div>
        </div>
    </div>

    {{-- Detalle acceso a la plataforma --}}
    <h3 style="font-size:0.95rem;font-weight:700;margin:24px 0 12px;color:var(--text-primary);">Cuentas pendientes de aprobación</h3>
    <div class="content-grid-2" style="gap:20px;">
        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Empresas por aprobar</h3>
                <a href="{{ route('admin.empresas', ['estado' => 'pendiente']) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todas &rarr;</a>
            </div>
            @forelse ($empresas_pendientes as $empresa)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border-light);gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                        <x-avatar :src="$empresa->usuario?->avatar_url" :nombre="$empresa->nombre_empresa" :tamano="32" />
                        <div style="flex:1;min-width:0;">
                            <p style="font-weight:600;margin:0;font-size:13px;color:var(--text-primary);">{{ $empresa->nombre_empresa }}</p>
                            <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $empresa->ciudad }} &middot; {{ $empresa->usuario?->email }}</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-left:10px;flex-shrink:0;">
                        <button onclick="rhModal('{{ route('admin.empresas.modal', $empresa) }}')" title="Ver detalle" class="btn btn-ghost btn-sm" style="padding:5px 7px;">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                        </form>
                        <button type="button" onclick="rhModal('{{ route('admin.empresas.rechazar.modal', $empresa) }}')" class="btn btn-danger btn-sm">Rechazar</button>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 0;color:var(--text-muted);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p style="margin:0;font-size:13px;">Sin empresas pendientes</p>
                </div>
            @endforelse
        </div>

        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Candidatos por revisar</h3>
                <a href="{{ route('admin.candidatos', ['estado' => 'enviada']) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todos &rarr;</a>
            </div>
            @forelse ($candidatos_pendientes as $candidato)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border-light);gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                        <x-avatar :src="$candidato->usuario?->avatar_url" :nombre="$candidato->nombre . ' ' . ($candidato->apellido_paterno ?? '')" :tamano="32" />
                        <div style="flex:1;min-width:0;">
                            <p style="font-weight:600;margin:0;font-size:13px;color:var(--text-primary);">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }}</p>
                            <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $candidato->puesto_deseado ?: $candidato->usuario?->email }}</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-left:10px;flex-shrink:0;">
                        <button onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')" title="Ver solicitud" class="btn btn-ghost btn-sm" style="padding:5px 7px;">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                        </form>
                        <button type="button" onclick="rhModal('{{ route('admin.candidatos.rechazar.modal', $candidato) }}')" class="btn btn-danger btn-sm">Rechazar</button>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 0;color:var(--text-muted);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p style="margin:0;font-size:13px;">Sin solicitudes pendientes</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Operaciones recientes (no es lo mismo que aprobación de acceso) --}}
    <h3 style="font-size:0.95rem;font-weight:700;margin:24px 0 12px;color:var(--text-primary);">Operaciones recientes (no son aprobaciones de acceso)</h3>
    <div class="card fade-in">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-weight:700;margin:0;font-size:1rem;">Vacantes recientes</h3>
            <a href="{{ route('admin.vacantes') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todas &rarr;</a>
        </div>
        @if($solicitudes_recientes->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Empresa</th><th>Solicitud</th><th>Nivel</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach($solicitudes_recientes as $s)
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <x-avatar :src="$s->empresa?->usuario?->avatar_url" :nombre="$s->empresa?->nombre_empresa ?? '?'" :tamano="26" />
                                        <span>{{ $s->empresa?->nombre_empresa ?? '—' }}</span>
                                    </div>
                                </td>
                                <td style="font-weight:500;color:var(--text-primary);">{{ $s->titulo }}</td>
                                <td>{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($s->nivel_jerarquico) }}</td>
                                <td><span class="badge {{ \App\Models\Vacante::estadoBadgeClass($s->estado) }}">{{ \App\Models\Vacante::estadoLabel($s->estado) }}</span></td>
                                <td style="text-align:right;"><a href="{{ route('admin.vacantes.matching', $s) }}" style="font-size:11px;color:var(--accent);text-decoration:none;">Asignar &rarr;</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center;padding:24px 0;color:var(--text-muted);"><p style="margin:0;font-size:13px;">No hay solicitudes de servicio recientes.</p></div>
        @endif
    </div>

    <div class="card fade-in" style="margin-top:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-weight:700;margin:0;font-size:1rem;">Tareas de servicio recientes</h3>
            <a href="{{ route('admin.tareas.index') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todas &rarr;</a>
        </div>
        @if($tareas_recientes->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Servicio</th><th>Objetivo</th><th>Interno</th><th>Estado</th><th>Creada</th><th></th></tr></thead>
                    <tbody>
                        @foreach($tareas_recientes as $tarea)
                            @php
                                $nivelServicio = $tarea->servicio?->nivel_jerarquico;
                                $avatarSolicitante = match($tarea->asignable_type) {
                                    \App\Models\Empresa::class, \App\Models\Candidato::class => $tarea->asignable?->usuario?->avatar_url,
                                    \App\Models\User::class => $tarea->asignable?->avatar_url,
                                    default => null,
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-weight:500;color:var(--text-primary);">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</span>
                                    @if($nivelServicio)<div style="font-size:11px;color:var(--text-muted);">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($nivelServicio) }}</div>@endif
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <x-avatar :src="$avatarSolicitante" :nombre="$tarea->asignableNombre()" :tamano="26" />
                                        <div>
                                            <div style="font-size:13px;">{{ $tarea->asignableNombre() }}</div>
                                            <div style="font-size:11px; color:var(--text-muted);">{{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($tarea->asignadoA)
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="26" />
                                            <span>{{ $tarea->asignadoA->name }}</span>
                                        </div>
                                    @else
                                        <span style="color:var(--text-muted);font-size:12px;">Sin asignar</span>
                                    @endif
                                </td>
                                <td><span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">{{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}</span></td>
                                <td style="color:var(--text-muted);">{{ optional($tarea->created_at)->format('d/m/Y H:i') }}</td>
                                <td style="text-align:right;"><a href="{{ route('admin.tareas.show', $tarea) }}" style="font-size:11px;color:var(--accent);text-decoration:none;">Abrir &rarr;</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center;padding:24px 0;color:var(--text-muted);"><p style="margin:0;font-size:13px;">Todavía no hay tareas de servicio registradas.</p></div>
        @endif
    </div>
</x-app-layout>
