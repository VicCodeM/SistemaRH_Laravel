<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Seguimiento</span>
        </nav>
        <h1 class="page-title">Seguimiento de solicitudes</h1>
        <p class="page-subtitle">Consulta en qué etapa va cada solicitud que enviaste.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success fade-in" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fade-in" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    @if($postulaciones->count())
        <div class="card fade-in desktop-only" style="padding:0; overflow:hidden;">
            <div class="table-wrap" style="border:none; box-shadow:none;">
                <table>
                    <thead>
                        <tr>
                            <th>Solicitud</th>
                            <th>Empresa</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($postulaciones as $p)
                            <tr>
                                <td>
                                    <span style="font-weight:500;">{{ $p->vacante?->titulo ?? '—' }}</span>
                                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $p->vacante?->requisitoResumen() ?? 'Sin requisitos' }}</div>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <x-avatar :src="$p->vacante?->empresa?->usuario?->avatar_url" :nombre="$p->vacante?->empresa?->nombre_empresa ?? '?'" :tamano="28" />
                                        <span class="text-muted">{{ $p->vacante?->empresa?->nombre_empresa ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted text-sm">{{ $p->fecha_postulacion?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($p->estado) }}">
                                        {{ \App\Models\Postulacion::estadoLabel($p->estado) }}
                                    </span>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <div class="toolbar-wrap" style="justify-content:flex-end;">
                                        @if($p->vacante)
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="rhModal('{{ route('candidato.vacantes.modal', $p->vacante) }}')">
                                                Ver detalle
                                            </button>
                                        @endif
                                        @if($p->estado === 'postulado')
                                            <form method="POST" action="{{ route('candidato.postulaciones.eliminar', $p) }}" style="display:inline;" onsubmit="return confirm('&iquest;Eliminar esta postulación? Se borrará permanentemente.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
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

        <div class="mobile-only">
            <div class="candidate-mobile-list">
                @foreach($postulaciones as $p)
                    <div class="candidate-mobile-card fade-in">
                        <h3 class="candidate-mobile-card-title">{{ $p->vacante?->titulo ?? '—' }}</h3>
                        <p class="candidate-mobile-card-subtitle">{{ $p->vacante?->empresa?->nombre_empresa ?? '—' }}</p>

                        <div class="candidate-mobile-meta">
                            <div>
                                <p class="candidate-mobile-meta-label">Resumen</p>
                                <p class="candidate-mobile-meta-value">{{ $p->vacante?->requisitoResumen() ?? 'Sin requisitos' }}</p>
                            </div>
                            <div>
                                <p class="candidate-mobile-meta-label">Fecha</p>
                                <p class="candidate-mobile-meta-value">{{ $p->fecha_postulacion?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="candidate-mobile-meta-label">Estado</p>
                                <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($p->estado) }}">
                                    {{ \App\Models\Postulacion::estadoLabel($p->estado) }}
                                </span>
                            </div>
                        </div>

                        <div class="candidate-actions" style="margin-top:14px;">
                            @if($p->vacante)
                                <button type="button" class="btn btn-secondary btn-sm" onclick="rhModal('{{ route('candidato.vacantes.modal', $p->vacante) }}')">
                                    Ver detalle
                                </button>
                            @endif
                            @if($p->estado === 'postulado')
                                <form method="POST" action="{{ route('candidato.postulaciones.eliminar', $p) }}" onsubmit="return confirm('&iquest;Eliminar esta postulación? Se borrará permanentemente.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <x-estado-vacio
            icono="📋"
            titulo="Aún no te has postulado a ninguna vacante"
            mensaje="Explora las vacantes disponibles y postúlate a las que te interesen. Aquí verás el avance de cada postulación."
            accion="Ver vacantes disponibles"
            :href="route('candidato.vacantes')" />
    @endif
</x-app-layout>
