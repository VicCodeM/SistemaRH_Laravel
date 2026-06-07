@php
    $rutaDetalle = $rutaDetalle ?? null;
    $rutaEliminar = $rutaEliminar ?? null;
    $labelResponsable = $labelResponsable ?? 'Responsable';
    $labelSolicitadoPor = $labelSolicitadoPor ?? 'Solicitado por';
    $mostrarSolicitadoPor = $mostrarSolicitadoPor ?? false;
    $mostrarNivelJerarquico = $mostrarNivelJerarquico ?? true;
    $mostrarFecha = $mostrarFecha ?? true;
    $servicioCatalogo = $servicio->servicio;
    $nombreServicio = $servicioCatalogo?->nombre ?? 'Servicio';
    $tipoServicio = $servicioCatalogo
        ? (\App\Models\CatalogoServicio::tipos()[$servicioCatalogo->tipo] ?? ucfirst(str_replace('_', ' ', (string) $servicioCatalogo->tipo)))
        : 'Servicio';
    $nivelJerarquico = $servicio->nivel_jerarquico
        ? \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico)
        : null;
    $descripcion = trim((string) ($servicioCatalogo?->descripcion ?? ''));
    $resumen = $descripcion !== ''
        ? \Illuminate\Support\Str::limit($descripcion, 110)
        : 'El administrador todavía no agrega detalles adicionales.';
    $detalleTooltip = $descripcion !== ''
        ? $descripcion
        : 'El administrador todavía no agrega detalles adicionales.';
    $solicitante = $servicio->solicitadoPor;
    $responsable = $servicio->asignadoA;
    $icono = \Illuminate\Support\Str::of($nombreServicio)->squish()->substr(0, 2)->upper();
    $puedeEliminar = $rutaEliminar
        && $servicio->estado === 'pendiente'
        && (int) $servicio->solicitado_por === (int) auth()->id();
@endphp

<article class="servicio-item">
    @if($rutaDetalle)
        <a href="{{ route($rutaDetalle, $servicio) }}" class="servicio-item__overlay" aria-label="Ver detalle de {{ $nombreServicio }}"></a>
    @endif

    <div class="servicio-item__icon" aria-hidden="true">{{ $icono }}</div>

    <div class="servicio-item__body">
        <h3 class="servicio-item__name">{{ $nombreServicio }}</h3>

        <div class="servicio-item__meta">
            <span>{{ $tipoServicio }}</span>
            @if($mostrarNivelJerarquico && $nivelJerarquico)
                <span>•</span>
                <span>Nivel: {{ $nivelJerarquico }}</span>
            @endif
            @if($mostrarFecha && $servicio->created_at)
                <span>•</span>
                <span>{{ $servicio->created_at->format('d/m/Y') }}</span>
            @endif
        </div>

        <p class="servicio-item__summary">{{ $resumen }}</p>
    </div>

    <div class="servicio-item__end">
        <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($servicio->estado) }}">
            {{ \App\Models\ServicioAsignado::estadoLabel($servicio->estado) }}
        </span>

        @if($rutaDetalle)
            <span class="servicio-item__cta">
                Ver detalle
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif

        <div style="font-size:.75rem; color:var(--text-muted); text-align:right;">
            <strong style="display:block; font-weight:700; color:var(--text-secondary);">{{ $labelResponsable }}</strong>
            <span>{{ $responsable?->name ?? 'Pendiente de asignación' }}</span>
        </div>

        @if($mostrarSolicitadoPor)
            <div style="font-size:.75rem; color:var(--text-muted); text-align:right;">
                <strong style="display:block; font-weight:700; color:var(--text-secondary);">{{ $labelSolicitadoPor }}</strong>
                <span>
                    @if($solicitante?->id === auth()->id())
                        Tú
                    @elseif($solicitante)
                        {{ $solicitante->name }}
                    @else
                        —
                    @endif
                </span>
            </div>
        @endif

        @if($puedeEliminar)
            <div class="servicio-item__acciones">
                <form method="POST" action="{{ route($rutaEliminar, $servicio) }}" onsubmit="return confirm('¿Eliminar esta solicitud? Se borrará permanentemente.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626; position:relative; z-index:2;">Eliminar</button>
                </form>
            </div>
        @endif
    </div>

    <div class="servicio-item__tooltip">
        <div class="servicio-item__tooltip-label">Detalle del servicio</div>
        <p class="servicio-item__tooltip-text">{{ $detalleTooltip }}</p>
    </div>
</article>
