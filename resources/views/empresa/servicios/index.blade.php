<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('empresa.dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Servicios solicitados</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Servicios solicitados</h1>
                <p class="page-subtitle">Capacitaciones, coaching, consultoria y demas servicios que hemos pedido.</p>
            </div>
            <a href="{{ route('empresa.servicios.crear') }}" class="btn btn-primary">+ Solicitar servicio</a>
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
            'pendientes'  => ['label' => 'Pendientes',  'color' => '#f59e0b'],
            'activos'     => ['label' => 'Activos',     'color' => '#3b82f6'],
            'en_proceso'  => ['label' => 'En proceso',  'color' => '#a855f7'],
            'completados' => ['label' => 'Completados', 'color' => '#10b981'],
        ] as $key => $cfg)
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">{{ $cfg['label'] }}</span>
                </div>
                <div class="metric-value" style="color:{{ $cfg['color'] }};">{{ $stats[$key] }}</div>
            </div>
        @endforeach
    </div>

    <div class="card">
        @if($servicios->isEmpty())
            <x-estado-vacio
                icono="✨"
                titulo="Aun no has solicitado ningun servicio"
                mensaje="Capacitaciones, coaching, consultoria... pidenos lo que necesites para tu equipo y te asignaremos al mejor responsable."
                accion="+ Solicitar primer servicio"
                :href="route('empresa.servicios.crear')" />
        @else
            <div class="desktop-only table-scroll">
                <table class="table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Responsable asignado</th>
                            <th>Estado</th>
                            <th>Solicitado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicios as $s)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $s->servicio?->nombre ?? 'Servicio' }}</div>
                                    @if($s->nivel_jerarquico)
                                        <span class="badge badge-blue" style="font-size:10px; margin-top:3px; display:inline-block;">
                                            Nivel: {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($s->nivel_jerarquico) }}
                                        </span>
                                    @endif
                                    @if($s->notas)
                                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ \Illuminate\Support\Str::limit($s->notas, 80) }}</div>
                                    @endif
                                </td>
                                <td style="font-size:13px;">
                                    @if($s->asignadoA)
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$s->asignadoA->avatar_url" :nombre="$s->asignadoA->name" :tamano="28" />
                                            <div>
                                                <div style="font-weight:500;">{{ $s->asignadoA->name }}</div>
                                                <div style="font-size:11px; color:#94a3b8;">{{ $s->asignadoA->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span style="color:#94a3b8; font-size:12px;">Sin asignar aun</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($s->estado) }}">
                                        {{ \App\Models\ServicioAsignado::estadoLabel($s->estado) }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:#64748b;">{{ $s->created_at?->format('d/m/Y') }}</td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <div class="toolbar-wrap" style="justify-content:flex-end;">
                                        <a href="{{ route('empresa.servicios.ver', $s) }}" class="btn btn-secondary btn-sm">Ver avance</a>
                                        @if($s->estado === 'pendiente')
                                            <form method="POST" action="{{ route('empresa.servicios.eliminar', $s) }}" style="display:inline;" onsubmit="return confirm('&iquest;Eliminar esta solicitud? Se borrará permanentemente.')">
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

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach($servicios as $s)
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div>
                                    <h3 class="candidate-mobile-card-title">{{ $s->servicio?->nombre ?? 'Servicio' }}</h3>
                                    <p class="candidate-mobile-card-subtitle">
                                        @if($s->nivel_jerarquico)
                                            Nivel: {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($s->nivel_jerarquico) }}
                                        @else
                                            Solicitud de servicio
                                        @endif
                                    </p>
                                </div>
                                <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($s->estado) }}">
                                    {{ \App\Models\ServicioAsignado::estadoLabel($s->estado) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Responsable asignado</p>
                                    @if($s->asignadoA)
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <x-avatar :src="$s->asignadoA->avatar_url" :nombre="$s->asignadoA->name" :tamano="28" />
                                            <div>
                                                <p class="candidate-mobile-meta-value">{{ $s->asignadoA->name }}</p>
                                                <p class="candidate-mobile-card-subtitle">{{ $s->asignadoA->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="candidate-mobile-meta-value">Sin asignar aun</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Solicitado</p>
                                    <p class="candidate-mobile-meta-value">{{ $s->created_at?->format('d/m/Y') }}</p>
                                </div>
                                @if($s->notas)
                                    <div>
                                        <p class="candidate-mobile-meta-label">Notas</p>
                                        <p class="candidate-mobile-meta-value">{{ \Illuminate\Support\Str::limit($s->notas, 120) }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="candidate-actions" style="margin-top:14px;">
                                <a href="{{ route('empresa.servicios.ver', $s) }}" class="btn btn-secondary btn-sm">Ver avance</a>
                                @if($s->estado === 'pendiente')
                                    <form method="POST" action="{{ route('empresa.servicios.eliminar', $s) }}" onsubmit="return confirm('&iquest;Eliminar esta solicitud? Se borrará permanentemente.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:14px;">{{ $servicios->links() }}</div>
        @endif
    </div>
</x-app-layout>
