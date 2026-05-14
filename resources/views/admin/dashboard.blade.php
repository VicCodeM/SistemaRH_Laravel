<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <span>Panel Admin</span>
        </nav>
        <h1 class="page-title">Panel de Administración</h1>
        <p class="page-subtitle">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }} — ResumenRH Consulting</p>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    {{-- Métricas clave --}}
    <div class="metrics-grid fade-in">
        <div class="metric-card" style="{{ $stats['empresas_pendientes'] > 0 ? 'border-color: rgba(245,158,11,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Empresas por aprobar</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['empresas_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:#64748b;font-size:12px;">{{ $stats['empresas_activas'] }} activas</span>
                <a href="{{ route('admin.empresas', ['estado' => 'pendiente']) }}" style="font-size:11px;color:#f59e0b;text-decoration:none;">Ver →</a>
            </div>
        </div>

        <div class="metric-card" style="{{ $stats['candidatos_pendientes'] > 0 ? 'border-color: rgba(96,165,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Solicitudes de candidato</span>
                <div class="metric-icon" style="background:rgba(96,165,250,.12);color:#60a5fa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['candidatos_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:#64748b;font-size:12px;">{{ $stats['candidatos_aprobados'] }} aprobados</span>
                <a href="{{ route('admin.candidatos', ['estado' => 'enviada']) }}" style="font-size:11px;color:#60a5fa;text-decoration:none;">Ver →</a>
            </div>
        </div>

        <div class="metric-card" style="{{ $stats['solicitudes_pendientes'] > 0 ? 'border-color: rgba(167,139,250,.4);' : '' }}">
            <div class="metric-top">
                <span class="metric-label">Solicitudes de servicio</span>
                <div class="metric-icon" style="background:rgba(167,139,250,.12);color:#a78bfa;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['solicitudes_pendientes'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:#64748b;font-size:12px;">{{ $stats['solicitudes_activas'] }} en proceso</span>
                <a href="{{ route('admin.vacantes') }}" style="font-size:11px;color:#a78bfa;text-decoration:none;">Ver →</a>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Personal ext. disponible</span>
                <div class="metric-icon" style="background:rgba(34,197,94,.12);color:#22c55e;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
            </div>
            <div class="metric-value">{{ $stats['personal_disponible'] }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="metric-change" style="color:#64748b;font-size:12px;">Consultores/capacitadores</span>
                <a href="{{ route('admin.personal-externo.index') }}" style="font-size:11px;color:#22c55e;text-decoration:none;">Ver →</a>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">

        {{-- Empresas pendientes --}}
        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Empresas por aprobar</h3>
                <a href="{{ route('admin.empresas', ['estado' => 'pendiente']) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todas →</a>
            </div>
            @forelse ($empresas_pendientes as $empresa)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="flex:1;min-width:0;">
                        <p style="font-weight:600;margin:0;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $empresa->nombre_empresa }}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $empresa->ciudad }} · {{ $empresa->usuario?->email }}</p>
                    </div>
                    <div style="display:flex;gap:6px;margin-left:10px;flex-shrink:0;">
                        <button onclick="rhModal('{{ route('admin.empresas.modal', $empresa) }}')"
                                title="Ver detalle"
                                style="width:28px;height:28px;background:var(--surface-2);border:1px solid var(--border);border-radius:6px;cursor:pointer;color:#94a3b8;display:flex;align-items:center;justify-content:center;">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}">
                            @csrf @method('PATCH')
                            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:11px;font-weight:600;">Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.empresas.rechazar', $empresa) }}">
                            @csrf @method('PATCH')
                            <button type="submit" style="padding:4px 10px;background:#ef4444;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:11px;font-weight:600;">Rechazar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 0;color:#475569;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p style="margin:0;font-size:13px;">Sin empresas pendientes</p>
                </div>
            @endforelse
        </div>

        {{-- Candidatos pendientes --}}
        <div class="card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Candidatos por revisar</h3>
                <a href="{{ route('admin.candidatos', ['estado' => 'enviada']) }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todos →</a>
            </div>
            @forelse ($candidatos_pendientes as $candidato)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="flex:1;min-width:0;">
                        <p style="font-weight:600;margin:0;font-size:13px;">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $candidato->puesto_deseado ?: $candidato->usuario?->email }}</p>
                    </div>
                    <div style="display:flex;gap:6px;margin-left:10px;flex-shrink:0;">
                        <button onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')"
                                title="Ver solicitud"
                                style="width:28px;height:28px;background:var(--surface-2);border:1px solid var(--border);border-radius:6px;cursor:pointer;color:#94a3b8;display:flex;align-items:center;justify-content:center;">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                            @csrf @method('PATCH')
                            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:11px;font-weight:600;">Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.candidatos.rechazar', $candidato) }}">
                            @csrf @method('PATCH')
                            <button type="submit" style="padding:4px 10px;background:#ef4444;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:11px;font-weight:600;">Rechazar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 0;color:#475569;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p style="margin:0;font-size:13px;">Sin solicitudes pendientes</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- Solicitudes de servicio recientes --}}
    @if ($solicitudes_recientes->isNotEmpty())
        <div class="card fade-in" style="margin-top:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-weight:700;margin:0;font-size:1rem;">Solicitudes de servicio recientes</h3>
                <a href="{{ route('admin.vacantes') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">Ver todas →</a>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Empresa</th>
                            <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Servicio / Puesto</th>
                            <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Nivel</th>
                            <th style="text-align:left;padding:8px 10px;color:#475569;font-weight:500;">Estado</th>
                            <th style="text-align:right;padding:8px 10px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitudes_recientes as $s)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:9px 10px;color:#94a3b8;">{{ $s->empresa?->nombre_empresa ?? '—' }}</td>
                                <td style="padding:9px 10px;font-weight:500;color:#e2e8f0;">{{ $s->titulo }}</td>
                                <td style="padding:9px 10px;color:#64748b;">{{ ucfirst($s->nivel_jerarquico) }}</td>
                                <td style="padding:9px 10px;">
                                    @php $sc=['pendiente'=>['#f59e0b','rgba(245,158,11,.12)'],'activa'=>['#22c55e','rgba(34,197,94,.12)']]; [$c,$b]=$sc[$s->estado]??['#64748b','rgba(100,116,139,.12)']; @endphp
                                    <span style="padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;color:{{ $c }};background:{{ $b }};">{{ ucfirst($s->estado) }}</span>
                                </td>
                                <td style="padding:9px 10px;text-align:right;">
                                    <a href="{{ route('admin.vacantes.matching', $s) }}" style="font-size:11px;color:#60a5fa;text-decoration:none;">Asignar →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-app-layout>
