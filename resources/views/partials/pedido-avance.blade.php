{{--
    Partial reutilizable: muestra el avance de un pedido de servicio.
    Variables esperadas:
      $servicio  = ServicioAsignado
      $rutaListado = ruta de regreso al listado del rol actual
--}}
<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ $rutaListado }}">Mis servicios</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $servicio->servicio?->nombre ?? 'Pedido' }}</span>
        </nav>
        <h1 class="page-title">{{ $servicio->servicio?->nombre ?? 'Pedido de servicio' }}</h1>
        <p class="page-subtitle">Sigue el avance de tu solicitud aquí.</p>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div style="max-width:900px;">
        {{-- Resumen --}}
        <div class="card" style="padding:20px; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 6px; font-size:1rem; font-weight:700;">{{ $servicio->servicio?->nombre ?? 'Servicio' }}</h2>
                    @if($servicio->nivel_jerarquico)
                        <span class="badge badge-blue" style="font-size:0.75rem;">Nivel: {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico) }}</span>
                    @endif
                </div>
                <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($servicio->estado) }}" style="font-size:0.85rem;">
                    {{ \App\Models\ServicioAsignado::estadoLabel($servicio->estado) }}
                </span>
            </div>

            <div style="margin-top:14px; padding:12px 14px; background:var(--surface-2); border-radius:8px;">
                <p style="margin:0 0 6px; font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase;">Lo que solicitaste</p>
                <p style="margin:0; font-size:14px; color:var(--text); white-space:pre-wrap;">{{ $servicio->notas ?? '—' }}</p>
            </div>
        </div>

        {{-- Pipeline del avance --}}
        <div class="card" style="padding:20px; margin-bottom:18px;">
            <h3 style="margin:0 0 14px; font-size:0.95rem; font-weight:700;">Avance de tu pedido</h3>
            @php
                $columnas = ['pendiente' => 'Recibido', 'activo' => 'Asignado', 'en_proceso' => 'En ejecución', 'completado' => 'Completado'];
            @endphp
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px;">
                @foreach($columnas as $estadoKey => $estadoLabel)
                    @php
                        $esActual = $servicio->estado === $estadoKey;
                        $yaPasado = match($servicio->estado) {
                            'activo'      => in_array($estadoKey, ['pendiente']),
                            'en_proceso'  => in_array($estadoKey, ['pendiente', 'activo']),
                            'completado'  => in_array($estadoKey, ['pendiente', 'activo', 'en_proceso']),
                            default       => false,
                        };
                    @endphp
                    <div style="background:{{ $esActual ? 'var(--accent-light)' : ($yaPasado ? '#f0fdf4' : 'var(--surface-2)') }}; border:2px solid {{ $esActual ? 'var(--accent)' : ($yaPasado ? '#86efac' : 'var(--border)') }}; border-radius:10px; padding:12px; text-align:center;">
                        <div style="font-size:18px; margin-bottom:4px;">
                            @if($yaPasado) ✓
                            @elseif($esActual) ●
                            @else ○
                            @endif
                        </div>
                        <div style="font-size:0.78rem; font-weight:600; color:{{ $esActual ? 'var(--accent)' : ($yaPasado ? '#16a34a' : '#94a3b8') }};">
                            {{ $estadoLabel }}
                        </div>
                    </div>
                @endforeach
            </div>

            @if($servicio->estado === 'cancelado')
                <div style="margin-top:14px; padding:10px 14px; background:#fef2f2; color:#dc2626; border-radius:8px; font-size:0.85rem;">
                    Este pedido fue cancelado.
                </div>
            @endif
        </div>

        {{-- Responsable asignado --}}
        <div class="card" style="padding:20px; margin-bottom:18px;">
            <h3 style="margin:0 0 12px; font-size:0.95rem; font-weight:700;">Responsable</h3>
            @if($servicio->asignadoA)
                <div style="display:flex; align-items:center; gap:12px;">
                    <x-avatar :src="$servicio->asignadoA->avatar_url" :nombre="$servicio->asignadoA->name" :tamano="44" />
                    <div>
                        <div style="font-weight:600;">{{ $servicio->asignadoA->name }}</div>
                        <div style="font-size:0.8rem; color:#64748b;">{{ $servicio->asignadoA->email }}</div>
                    </div>
                </div>
            @else
                <p style="margin:0; color:#94a3b8; font-size:0.9rem;">
                    Aún estamos buscando al responsable adecuado para tu pedido. Te avisaremos en cuanto lo asignemos.
                </p>
            @endif
        </div>

        {{-- Fechas --}}
        <div class="card" style="padding:20px; margin-bottom:18px;">
            <h3 style="margin:0 0 12px; font-size:0.95rem; font-weight:700;">Fechas</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; font-size:0.85rem;">
                <div>
                    <p style="margin:0 0 4px; font-size:11px; color:#94a3b8; text-transform:uppercase;">Solicitado el</p>
                    <p style="margin:0;">{{ $servicio->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p style="margin:0 0 4px; font-size:11px; color:#94a3b8; text-transform:uppercase;">Comenzó</p>
                    <p style="margin:0;">{{ $servicio->fecha_inicio?->format('d/m/Y H:i') ?? 'Aún no comienza' }}</p>
                </div>
                <div>
                    <p style="margin:0 0 4px; font-size:11px; color:#94a3b8; text-transform:uppercase;">Terminó</p>
                    <p style="margin:0;">{{ $servicio->fecha_fin?->format('d/m/Y H:i') ?? 'Aún no termina' }}</p>
                </div>
            </div>
        </div>

        @if($servicio->cierre_resumen)
            <div class="card" style="padding:20px; margin-bottom:18px;">
                <h3 style="margin:0 0 12px; font-size:0.95rem; font-weight:700;">Resumen final</h3>
                <p style="margin:0; font-size:0.9rem; color:var(--text); white-space:pre-wrap;">{{ $servicio->cierre_resumen }}</p>
            </div>
        @endif

        {{-- Comentarios / actualizaciones --}}
        @include('partials.pedido-comentarios', ['servicio' => $servicio])

        <div style="text-align:right;">
            <a href="{{ $rutaListado }}" class="btn btn-secondary">&larr; Volver a mis servicios</a>
        </div>
    </div>
</x-app-layout>
