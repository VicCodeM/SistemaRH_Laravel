@php
    $urlDetalle = $urlDetalle ?? '#';
    $mostrarNivel = $mostrarNivel ?? false;
    $mostrarFlujo = $mostrarFlujo ?? true;
    $tipoServicio = \App\Models\CatalogoServicio::tipos()[$catalogo->tipo] ?? ucfirst(str_replace('_', ' ', (string) $catalogo->tipo));
    $resumen = trim((string) ($catalogo->descripcion ?? ''));
    $nivelJerarquico = \App\Models\CatalogoServicio::nivelJerarquicoLabel($catalogo->nivel_jerarquico);
@endphp

<article class="servicio-item">
    <a href="{{ $urlDetalle }}" class="servicio-item__overlay" aria-label="Ver detalle de {{ $catalogo->nombre }}"></a>

    <div class="servicio-item__icon" aria-hidden="true">
        {{ \Illuminate\Support\Str::of($catalogo->nombre)->squish()->substr(0, 2)->upper() }}
    </div>

    <div class="servicio-item__body">
        <h3 class="servicio-item__name">{{ $catalogo->nombre }}</h3>

        <div class="servicio-item__meta">
            <span>{{ $tipoServicio }}</span>

            @if($mostrarNivel && $catalogo->usaNivelJerarquicoPara('empresa') && $catalogo->nivel_jerarquico !== 'todos')
                <span>&bull;</span>
                <span>Nivel: {{ $nivelJerarquico }}</span>
            @endif

            @if($mostrarFlujo && $catalogo->esFlujoVacante())
                <span>&bull;</span>
                <span>Flujo de vacante</span>
            @endif
        </div>
    </div>

    <div class="servicio-item__end">
        @if($mostrarFlujo && $catalogo->esFlujoVacante())
            <span class="badge badge-gray">Vacante</span>
        @endif

        <span class="servicio-item__cta">
            Ver detalle
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </div>

    <div class="servicio-item__tooltip">
        <div class="servicio-item__tooltip-label">Vista rapida</div>
        <p class="servicio-item__tooltip-text">{{ $resumen !== '' ? $resumen : 'Sin descripcion todavia.' }}</p>
    </div>
</article>
