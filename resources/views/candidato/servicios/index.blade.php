<x-app-layout>
    @php
        $tiposCatalogo = \App\Models\CatalogoServicio::tipos();
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('candidato.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Servicios disponibles</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Servicios disponibles</h1>
                <p class="page-subtitle">Explora los servicios que puedes solicitar y abre cada uno para ver su presentacion.</p>
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
        @foreach([
            'disponibles' => ['label' => 'Disponibles', 'color' => '#3b82f6'],
            'categorias' => ['label' => 'Categorias', 'color' => '#64748b'],
            'solicitados' => ['label' => 'Solicitudes', 'color' => '#10b981'],
        ] as $key => $cfg)
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">{{ $cfg['label'] }}</span>
                </div>
                <div class="metric-value" style="color:{{ $cfg['color'] }};">{{ $stats[$key] }}</div>
            </div>
        @endforeach
    </div>

    <div class="servicio-lista-shell">
        <div class="card servicio-lista-card">
            <div class="servicio-lista-toolbar">
                <form method="GET" action="{{ route('candidato.servicios.index') }}" class="form-inline" style="width:100%;">
                    @if($tipo !== '')
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                    @endif

                    <input type="text"
                           name="buscar"
                           value="{{ request('buscar') }}"
                           placeholder="Buscar servicio..."
                           class="form-input"
                           style="min-width:220px; flex:1;">

                    <button type="submit" class="btn btn-primary">Buscar</button>

                    @if(request()->filled('buscar'))
                        <a href="{{ route('candidato.servicios.index') }}" class="btn btn-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            @if($tiposDisponibles->isNotEmpty())
                <div style="margin-bottom:16px;">
                    <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Categorias</p>
                    <div class="toolbar-wrap">
                        <a href="{{ route('candidato.servicios.index', array_filter(['buscar' => request('buscar')])) }}"
                           class="btn {{ $tipo === '' ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                            Todas
                        </a>

                        @foreach($tiposDisponibles as $tipoDisponible)
                            <a href="{{ route('candidato.servicios.index', array_filter(['buscar' => request('buscar'), 'tipo' => $tipoDisponible])) }}"
                               class="btn {{ $tipo === $tipoDisponible ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                {{ $tiposCatalogo[$tipoDisponible] ?? ucfirst(str_replace('_', ' ', $tipoDisponible)) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($catalogos->isEmpty())
                <x-estado-vacio
                    icono="SV"
                    titulo="Aun no hay servicios para mostrar"
                    mensaje="Cuando el administrador publique servicios para candidatos, apareceran aqui con su detalle y presentacion."
                    accion="Ir al inicio"
                    :href="route('candidato.dashboard')" />
            @else
                <div class="servicio-lista">
                    @foreach($catalogos as $catalogo)
                        @include('partials.catalogo-servicio-item', [
                            'catalogo' => $catalogo,
                            'urlDetalle' => route('candidato.servicios.crear', ['servicio_id' => $catalogo->id]),
                        ])
                    @endforeach
                </div>

                <div style="margin-top:14px;">{{ $catalogos->links() }}</div>
            @endif
        </div>

        <aside class="card servicio-side">
            <div class="servicio-side__panel">
                <div class="servicio-side__icon" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9.75v4.5c0 1.38 3.03 3.75 6.75 3.75s6.75-2.37 6.75-3.75v-4.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="servicio-side__title">Haz clic en un servicio para ver su detalle</h2>
                    <p class="servicio-side__text">Aqui solo veras servicios activos para candidatos. Pasa el mouse para una vista rapida y entra al detalle para ver la presentacion completa antes de solicitarlo.</p>
                </div>
                <div class="servicio-side__actions">
                    <a href="{{ route('candidato.dashboard') }}" class="btn btn-secondary">Volver al panel</a>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
