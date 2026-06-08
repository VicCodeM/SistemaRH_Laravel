<x-app-layout>
    @php
        $tiposCatalogo = \App\Models\CatalogoServicio::tipos();
        $nivelesCatalogo = \App\Models\CatalogoServicio::nivelesJerarquicosFormulario();
        $usaFiltroNivel = ($usaFiltroNivel ?? false) && $nivelesDisponibles->isNotEmpty();
        $tieneAccionSecundaria = is_array($accionSecundaria ?? null)
            && ! empty($accionSecundaria['href'])
            && ! empty($accionSecundaria['label']);
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ $rutaInicio }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Servicios disponibles</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Servicios disponibles</h1>
                <p class="page-subtitle">{{ $subtituloPagina }}</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="metrics-grid" style="margin-bottom:20px;">
        @foreach($metricasTarjetas as $key => $cfg)
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">{{ $cfg['label'] }}</span>
                </div>
                <div class="metric-value" style="color:{{ $cfg['color'] }};">{{ $stats[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <div class="servicio-lista-shell">
        <div class="card servicio-lista-card">
            <div class="servicio-lista-toolbar">
                <form method="GET" action="{{ $rutaListado }}" class="form-inline" style="width:100%;">
                    @if($tipo !== '')
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                    @endif
                    @if($usaFiltroNivel && $nivel !== '')
                        <input type="hidden" name="nivel" value="{{ $nivel }}">
                    @endif

                    <input type="text"
                           name="buscar"
                           value="{{ request('buscar') }}"
                           placeholder="Buscar servicio..."
                           class="form-input"
                           style="min-width:220px; flex:1;"
                           spellcheck="true"
                           autocorrect="on"
                           autocapitalize="sentences"
                           lang="es">

                    <button type="submit" class="btn btn-primary">Buscar</button>

                    @if(request()->filled('buscar'))
                        <a href="{{ $rutaListado }}" class="btn btn-secondary">Limpiar</a>
                    @endif
                </form>

                @if($tieneAccionSecundaria)
                    <a href="{{ $accionSecundaria['href'] }}" class="btn btn-secondary">{{ $accionSecundaria['label'] }}</a>
                @endif
            </div>

            @if($tiposDisponibles->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
                    <div>
                        <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Categorias</p>
                        <div class="toolbar-wrap">
                            <a href="{{ $rutaListado . '?' . http_build_query(array_filter([
                                'buscar' => request('buscar'),
                                'nivel' => $usaFiltroNivel ? $nivel : null,
                            ])) }}"
                               class="btn {{ $tipo === '' ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                Todas
                            </a>

                            @foreach($tiposDisponibles as $tipoDisponible)
                                <a href="{{ $rutaListado . '?' . http_build_query(array_filter([
                                    'buscar' => request('buscar'),
                                    'tipo' => $tipoDisponible,
                                    'nivel' => $usaFiltroNivel ? $nivel : null,
                                ])) }}"
                                   class="btn {{ $tipo === $tipoDisponible ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                    {{ $tiposCatalogo[$tipoDisponible] ?? ucfirst(str_replace('_', ' ', $tipoDisponible)) }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if($usaFiltroNivel)
                        <div>
                            <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Nivel de atencion</p>
                            <div class="toolbar-wrap">
                                <a href="{{ $rutaListado . '?' . http_build_query(array_filter([
                                    'buscar' => request('buscar'),
                                    'tipo' => $tipo,
                                ])) }}"
                                   class="btn {{ $nivel === '' ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                    Todos
                                </a>

                                @foreach($nivelesDisponibles as $nivelDisponible)
                                    <a href="{{ $rutaListado . '?' . http_build_query(array_filter([
                                        'buscar' => request('buscar'),
                                        'tipo' => $tipo,
                                        'nivel' => $nivelDisponible,
                                    ])) }}"
                                       class="btn {{ $nivel === $nivelDisponible ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                        {{ $nivelesCatalogo[$nivelDisponible] ?? ucfirst(str_replace('_', ' ', $nivelDisponible)) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($catalogos->isEmpty())
                <x-estado-vacio
                    icono="SV"
                    :titulo="$estadoVacio['titulo']"
                    :mensaje="$estadoVacio['mensaje']"
                    :accion="$estadoVacio['accion']"
                    :href="$estadoVacio['href']" />
            @else
                <div class="servicio-lista">
                    @foreach($catalogos as $catalogo)
                        @include('partials.catalogo-servicio-item', [
                            'catalogo' => $catalogo,
                            'urlDetalle' => route($rutaDetalle, ['servicio_id' => $catalogo->id]),
                            'mostrarNivel' => $mostrarNivel ?? false,
                        ])
                    @endforeach
                </div>

                <div style="margin-top:14px;">{{ $catalogos->links() }}</div>
            @endif
        </div>

        <aside class="card servicio-side">
            <div class="servicio-side__panel">
                <div class="servicio-side__icon" aria-hidden="true">
                    @if(($rolServicio ?? '') === 'empresa')
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15M4.5 12h15M4.5 16.5h15" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9m-9 15h9" />
                        </svg>
                    @else
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9.75v4.5c0 1.38 3.03 3.75 6.75 3.75s6.75-2.37 6.75-3.75v-4.5" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h2 class="servicio-side__title">Haz clic en un servicio para ver su detalle</h2>
                    <p class="servicio-side__text">{{ $textoLateral }}</p>
                </div>
                <div class="servicio-side__actions">
                    <a href="{{ $rutaInicio }}" class="btn btn-secondary">Volver al panel</a>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
