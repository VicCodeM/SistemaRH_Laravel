<x-app-layout>
    @php
        $estado = $candidato->solicitud_estado ?? 'borrador';
        $badgeClass = \App\Models\Candidato::solicitudEstadoBadgeClass($estado) ?? 'badge-gray';
        $estadoLabel = \App\Models\Candidato::solicitudEstadoLabel($estado);
        $aprobado = $estado === 'aprobada';
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Mi cuenta</span>
        </nav>
        <h1 class="page-title">Panel del candidato</h1>
        <p class="page-subtitle">Revisa tu estado, corrige tu solicitud y consulta tus solicitudes aprobadas.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    @isset($acciones)
        <x-acciones-pendientes titulo="¿Qué sigue?" :acciones="$acciones" />
    @endisset

    <div class="metrics-grid fade-in">
        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Estado de la solicitud</span>
                <div class="metric-icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value">
                <span class="badge {{ $badgeClass }}">{{ $estadoLabel }}</span>
            </div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">
                {{ $aprobado ? 'Ya puedes consultar y solicitar vacantes.' : 'Primero termina el proceso de revisión.' }}
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Postulaciones</span>
                <div class="metric-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value">{{ $postulacionesRecientes->count() }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">
                Últimas {{ min(4, $postulacionesRecientes->count()) }} solicitudes visibles
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-top">
                <span class="metric-label">Vacantes activas</span>
                <div class="metric-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value">{{ $vacantesRecientes->count() }}</div>
            <div class="metric-change" style="color:#64748b;font-size:12px;">
                Solo visible después de tu aprobación
            </div>
        </div>
    </div>

    @php
        $ultimaPostulacion = $postulacionesRecientes->first();
    @endphp

    <div class="card fade-in" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap;">
            <div>
                <h3 style="font-weight:700; margin:0 0 4px; font-size:1rem;">Estado de tu última postulación</h3>
                <p style="margin:0; color:#64748b; font-size:13px;">
                    Revisa aquí el avance más reciente sin abrir el detalle.
                </p>
            </div>

            @if($ultimaPostulacion)
                <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($ultimaPostulacion->estado) }}">
                    {{ \App\Models\Postulacion::estadoLabel($ultimaPostulacion->estado) }}
                </span>
            @endif
        </div>

        @if($ultimaPostulacion)
            <div style="display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; margin-top:16px; padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                <div>
                    <div style="font-weight:600;">{{ $ultimaPostulacion->vacante?->titulo ?? 'Vacante' }}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">
                        {{ $ultimaPostulacion->vacante?->empresa?->nombre_empresa ?? 'Empresa' }}
                        @if($ultimaPostulacion->fecha_postulacion)
                            · {{ $ultimaPostulacion->fecha_postulacion->format('d/m/Y H:i') }}
                        @endif
                    </div>
                </div>
                <a href="{{ route('candidato.postulaciones') }}" class="btn btn-secondary btn-sm">Ver seguimiento</a>
            </div>
        @else
            <div style="margin-top:16px; padding:16px; border:1px dashed var(--border); border-radius:10px; color:#64748b; font-size:13px;">
                Todavía no has enviado postulaciones. Cuando apliques a una vacante, aquí verás su estatus.
            </div>
        @endif
    </div>

    <div class="candidate-actions">
        <a href="{{ route('candidato.solicitud') }}" class="btn btn-primary">Mi solicitud</a>
        @if($aprobado)
            <a href="{{ route('candidato.vacantes') }}" class="btn btn-secondary">Solicitudes disponibles</a>
            <a href="{{ route('candidato.postulaciones') }}" class="btn btn-secondary">Seguimiento</a>
        @endif
    </div>

    <div class="content-split" style="margin-top:24px;">
        <div class="card fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-weight:700; margin:0; font-size:1rem;">Progreso de tu perfil</h3>
                <span style="font-size:12px; color:#64748b;">{{ $candidato->nombreCompleto() ?: 'Perfil sin nombre' }}</span>
            </div>

            <div style="display:grid; gap:12px;">
                <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:600;">Solicitud capturada</div>
                            <div style="font-size:12px; color:#64748b;">Datos personales, contacto y perfil laboral.</div>
                        </div>
                        <span class="badge badge-blue">Listo</span>
                    </div>
                </div>

                <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:600;">Revisión administrativa</div>
                            <div style="font-size:12px; color:#64748b;">Admin valida tu perfil y te da acceso a solicitudes.</div>
                        </div>
                        <span class="badge {{ $aprobado ? 'badge-green' : 'badge-yellow' }}">{{ $aprobado ? 'Aprobado' : 'En revisión' }}</span>
                    </div>
                </div>

                <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--surface-2);">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:600;">Solicitudes y seguimiento</div>
                            <div style="font-size:12px; color:#64748b;">Solo se activa cuando tu perfil ya está aprobado.</div>
                        </div>
                        <span class="badge {{ $aprobado ? 'badge-green' : 'badge-gray' }}">{{ $aprobado ? 'Activo' : 'Bloqueado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-weight:700; margin:0; font-size:1rem;">Datos rápidos</h3>
                <span style="font-size:12px; color:#64748b;">Actualizado {{ $candidato->updated_at?->diffForHumans() ?? 'hace poco' }}</span>
            </div>

            <div style="display:grid; gap:10px; font-size:13px;">
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Correo</p>
                    <span style="font-weight:500;">{{ auth()->user()->email }}</span>
                </div>
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Estudios</p>
                    <span>{{ \App\Models\Vacante::nivelEstudiosLabel($candidato->escolaridad) }}</span>
                </div>
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Experiencia</p>
                    <span>{{ (int) ($candidato->experiencia_anios ?? 0) }} año(s)</span>
                </div>
                <div>
                    <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Aspiración</p>
                    <span>{{ $candidato->puesto_deseado ?: 'Sin definir' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card fade-in" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
            <h3 style="font-weight:700; margin:0; font-size:1rem;">Últimas postulaciones</h3>
            <a href="{{ route('candidato.postulaciones') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Ver todo →</a>
        </div>

        @if($postulacionesRecientes->isEmpty())
            <div style="text-align:center; padding:36px 0; color:#64748b;">
                Aún no tienes postulaciones registradas.
            </div>
        @else
            <div class="desktop-only table-scroll">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Solicitud</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Empresa</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Estado</th>
                            <th style="text-align:left; padding:8px 10px; color:#475569; font-weight:500;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($postulacionesRecientes as $postulacion)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:9px 10px;">
                                    <div style="font-weight:600;">{{ $postulacion->vacante?->titulo ?? '—' }}</div>
                                    <div style="font-size:11px; color:#64748b;">{{ $postulacion->vacante?->requisitoResumen() ?? 'Sin requisitos' }}</div>
                                </td>
                                <td style="padding:9px 10px; color:#64748b;">
                                    {{ $postulacion->vacante?->empresa?->nombre_empresa ?? '—' }}
                                </td>
                                <td style="padding:9px 10px;">
                                    <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($postulacion->estado) }}">
                                        {{ \App\Models\Postulacion::estadoLabel($postulacion->estado) }}
                                    </span>
                                </td>
                                <td style="padding:9px 10px; color:#64748b; font-size:12px;">
                                    {{ $postulacion->fecha_postulacion?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($postulacionesRecientes as $postulacion)
                        <div class="candidate-mobile-card">
                            <h4 class="candidate-mobile-card-title">{{ $postulacion->vacante?->titulo ?? '—' }}</h4>
                            <p class="candidate-mobile-card-subtitle">{{ $postulacion->vacante?->empresa?->nombre_empresa ?? '—' }}</p>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Requisitos</p>
                                    <p class="candidate-mobile-meta-value">{{ $postulacion->vacante?->requisitoResumen() ?? 'Sin requisitos' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Estado</p>
                                    <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($postulacion->estado) }}">
                                        {{ \App\Models\Postulacion::estadoLabel($postulacion->estado) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Fecha</p>
                                    <p class="candidate-mobile-meta-value">{{ $postulacion->fecha_postulacion?->format('d/m/Y H:i') ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($aprobado && $vacantesRecientes->isNotEmpty())
        <div class="card fade-in" style="margin-top:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
                <h3 style="font-weight:700; margin:0; font-size:1rem;">Solicitudes activas recomendadas</h3>
                <span style="font-size:12px; color:#64748b;">Disponible para ti</span>
            </div>

            <div class="candidate-compact-list">
                @foreach($vacantesRecientes as $vacante)
                    <div class="candidate-compact-item">
                        <div class="candidate-inline-meta">
                            <div style="min-width:0;">
                                <div class="candidate-compact-item-title">{{ $vacante->titulo }}</div>
                                <div class="candidate-compact-item-subtitle" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $vacante->empresa?->nombre_empresa ?? 'Empresa' }}
                                </div>
                            </div>
                            <div style="flex-shrink:0;">
                                <div class="candidate-compact-item-trailing">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }}</div>
                                <div style="margin-top:4px;">
                                    <button type="button" onclick="rhModal('{{ route('candidato.vacantes.modal', $vacante) }}')" class="btn btn-secondary" style="padding:4px 10px; font-size:12px;">Ver detalle</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
