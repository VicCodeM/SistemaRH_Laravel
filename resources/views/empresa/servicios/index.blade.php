<x-app-layout>
    @php
        $tiposCatalogo = \App\Models\CatalogoServicio::tipos();
        $nivelesCatalogo = \App\Models\CatalogoServicio::nivelesJerarquicosFormulario();
    @endphp

    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Servicios disponibles</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Servicios disponibles</h1>
                <p class="page-subtitle">Explora los servicios que tu empresa puede solicitar y abre cada uno para ver su presentacion.</p>
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
            'vacantes' => ['label' => 'Vacantes', 'color' => '#8b5cf6'],
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
                <form method="GET" action="{{ route('empresa.servicios.index') }}" class="form-inline" style="width:100%;">
                    @if($tipo !== '')
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                    @endif
                    @if($nivel !== '')
                        <input type="hidden" name="nivel" value="{{ $nivel }}">
                    @endif

                    <input type="text"
                           name="buscar"
                           value="{{ request('buscar') }}"
                           placeholder="Buscar servicio..."
                           class="form-input"
                           style="min-width:220px; flex:1;">

                    <button type="submit" class="btn btn-primary">Buscar</button>

                    @if(request()->filled('buscar'))
                        <a href="{{ route('empresa.servicios.index') }}" class="btn btn-secondary">Limpiar</a>
                    @endif
                </form>

                <a href="{{ route('empresa.solicitudes') }}" class="btn btn-secondary">Ver mis vacantes</a>
            </div>

            @if($tiposDisponibles->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
                    <div>
                        <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Categorias</p>
                        <div class="toolbar-wrap">
                            <a href="{{ route('empresa.servicios.index', array_filter(['buscar' => request('buscar'), 'nivel' => $nivel])) }}"
                               class="btn {{ $tipo === '' ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                Todas
                            </a>

                            @foreach($tiposDisponibles as $tipoDisponible)
                                <a href="{{ route('empresa.servicios.index', array_filter(['buscar' => request('buscar'), 'tipo' => $tipoDisponible, 'nivel' => $nivel])) }}"
                                   class="btn {{ $tipo === $tipoDisponible ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                    {{ $tiposCatalogo[$tipoDisponible] ?? ucfirst(str_replace('_', ' ', $tipoDisponible)) }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if($nivelesDisponibles->isNotEmpty())
                        <div>
                            <p style="margin:0 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">Nivel de atencion</p>
                            <div class="toolbar-wrap">
                                <a href="{{ route('empresa.servicios.index', array_filter(['buscar' => request('buscar'), 'tipo' => $tipo])) }}"
                                   class="btn {{ $nivel === '' ? 'btn-primary' : 'btn-secondary btn-sm' }}">
                                    Todos
                                </a>

                                @foreach($nivelesDisponibles as $nivelDisponible)
                                    <a href="{{ route('empresa.servicios.index', array_filter(['buscar' => request('buscar'), 'tipo' => $tipo, 'nivel' => $nivelDisponible])) }}"
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
                    titulo="Aun no hay servicios para mostrar"
                    mensaje="Cuando el administrador publique servicios para empresas, apareceran aqui con su detalle y presentacion."
                    accion="Ir al inicio"
                    :href="route('empresa.dashboard')" />
            @else
                <div class="servicio-lista">
                    @foreach($catalogos as $catalogo)
                        @include('partials.catalogo-servicio-item', [
                            'catalogo' => $catalogo,
                            'urlDetalle' => route('empresa.servicios.crear', ['servicio_id' => $catalogo->id]),
                            'mostrarNivel' => true,
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15M4.5 12h15M4.5 16.5h15" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9m-9 15h9" />
                    </svg>
                </div>
                <div>
                    <h2 class="servicio-side__title">Haz clic en un servicio para ver su detalle</h2>
                    <p class="servicio-side__text">La lista solo muestra servicios activos para tu empresa. Pasa el mouse para ver una vista rapida y entra al detalle para solicitarlo o abrir el flujo de vacante.</p>
                </div>
                <div class="servicio-side__actions">
                    <a href="{{ route('empresa.dashboard') }}" class="btn btn-secondary">Volver al panel</a>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
