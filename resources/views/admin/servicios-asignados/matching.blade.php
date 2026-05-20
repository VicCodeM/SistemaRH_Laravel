<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <a href="{{ route('admin.tareas.index') }}">Pedidos de servicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Asignar interno</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">{{ $requisitos['servicio'] }}</h1>
                <p class="page-subtitle">{{ $requisitos['solicitante'] }} · {{ $requisitos['nivel'] }} · <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}">{{ $requisitos['estado_actual'] }}</span></p>
            </div>
            <div class="toolbar-wrap">
                <a href="{{ route('admin.tareas.show', $tarea) }}" class="btn btn-secondary">Ver pedido</a>
                <a href="{{ route('admin.tareas.editar', $tarea) }}" class="btn btn-secondary">Editar pedido</a>
                <a href="{{ route('admin.tareas.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    @if($tarea->asignado_a && in_array($tarea->estado, ['activo', 'en_proceso'], true))
        <div style="margin-bottom:18px; padding:14px 18px; background:#eff6ff; border-left:4px solid #3b82f6; border-radius:8px;">
            <strong style="color:#1d4ed8;">Pedido ya asignado a {{ $tarea->asignadoA?->name }}.</strong>
            <span style="color:#475569; font-size:0.9rem;"> Solo puede haber un interno responsable. Si necesitas cambiarlo, primero libera al interno actual.</span>
        </div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; align-items:start;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:8px; padding:4px 10px; border-radius:999px; background:rgba(59,130,246,.08); color:#60a5fa; font-size:12px; font-weight:600;">
                    Pedido de servicio
                </div>
                <h2 style="margin:10px 0 8px; font-size:1rem; font-weight:700;">Asignar interno responsable</h2>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <span class="badge badge-blue">Servicio: {{ $requisitos['servicio'] }}</span>
                    <span class="badge badge-blue">Nivel: {{ $requisitos['nivel'] }}</span>
                    <span class="badge badge-blue">Solicita: {{ $requisitos['solicitante'] }}</span>
                </div>
                @if($tarea->notas)
                    <div style="margin-top:12px; padding:10px 12px; background:var(--surface-2); border-radius:8px; font-size:0.85rem; color:#475569;">
                        <strong style="font-size:11px; text-transform:uppercase; color:#94a3b8;">Notas:</strong><br>
                        {{ $tarea->notas }}
                    </div>
                @endif
            </div>
            <div style="padding:14px 16px; background:var(--surface-2); border:1px solid var(--border); border-radius:10px; font-size:0.84rem; color:#64748b; line-height:1.6;">
                El sistema clasifica internos en <strong>Capacitados disponibles</strong>, <strong>Capacitados ocupados</strong> y <strong>No capacitados</strong>.
                Si no cumple, puedes forzar la asignacion dejando un motivo.
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="candidate-inline-meta" style="margin-bottom:16px;">
            <h2 style="font-weight:700; font-size:1rem; margin:0;">Estado del pedido</h2>
            <div class="toolbar-wrap">
                @if($tarea->estado === 'pendiente' && ! $tarea->asignado_a)
                    <span style="font-size:12px; color:#94a3b8; padding:6px 0;">Asigna un interno abajo para activar</span>
                @elseif($tarea->estado === 'pendiente' && $tarea->asignado_a)
                    <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="activo">
                        <button type="submit" class="btn btn-primary btn-sm">Activar</button>
                    </form>
                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'cancelar']) }}')" class="btn btn-secondary btn-sm">Cancelar</button>
                @elseif($tarea->estado === 'activo')
                    <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="en_proceso">
                        <button type="submit" class="btn btn-primary btn-sm">Iniciar trabajo</button>
                    </form>
                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'cancelar']) }}')" class="btn btn-secondary btn-sm">Cancelar</button>
                @elseif($tarea->estado === 'en_proceso')
                    <form method="POST" action="{{ route('admin.tareas.estado', $tarea) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="completado">
                        <button type="submit" class="btn btn-success btn-sm">Completar</button>
                    </form>
                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'cancelar']) }}')" class="btn btn-secondary btn-sm">Cancelar</button>
                @elseif(in_array($tarea->estado, ['completado', 'cancelado'], true))
                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'reabrir']) }}')" class="btn btn-secondary btn-sm">Reabrir</button>
                @endif

                @if($tarea->asignado_a && in_array($tarea->estado, ['pendiente', 'activo', 'en_proceso'], true))
                    <button type="button" onclick="rhModal('{{ route('admin.tareas.accion.modal', [$tarea, 'liberar']) }}')" class="btn btn-secondary btn-sm">Liberar interno</button>
                @endif
            </div>
        </div>

        @php
            $columnas = ['pendiente' => 'Pendiente', 'activo' => 'Activo', 'en_proceso' => 'En proceso', 'completado' => 'Completado'];
        @endphp
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
            @foreach($columnas as $estadoKey => $estadoLabel)
                @php
                    $esActual = $tarea->estado === $estadoKey;
                    $badgeClass = \App\Models\ServicioAsignado::estadoBadgeClass($estadoKey);
                @endphp
                <div style="background:{{ $esActual ? 'var(--surface)' : 'var(--surface-2)' }}; border-radius:10px; padding:14px; border:2px solid {{ $esActual ? 'var(--accent)' : 'var(--border)' }}; min-height:110px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <span class="badge {{ $badgeClass }}" style="font-size:0.72rem;">{{ $estadoLabel }}</span>
                        @if($esActual)
                            <span style="font-size:10px; color:var(--accent); font-weight:700;">ACTUAL</span>
                        @endif
                    </div>

                    @if($esActual && $tarea->asignadoA)
                        <div style="display:flex; align-items:center; gap:6px;">
                            <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="24" />
                            <div style="font-weight:600; font-size:0.85rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $tarea->asignadoA->name }}</div>
                        </div>
                        <div style="font-size:0.72rem; color:#64748b; margin-top:4px;">{{ $tarea->asignadoA->email }}</div>
                        @if($tarea->fecha_inicio)
                            <div style="font-size:0.7rem; color:#94a3b8; margin-top:4px;">Inicio: {{ $tarea->fecha_inicio->format('d/m/Y H:i') }}</div>
                        @endif
                    @elseif($esActual)
                        <div style="font-size:0.78rem; color:#64748b;">Sin responsable</div>
                    @else
                        <div style="font-size:0.78rem; color:#94a3b8;">—</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @php
        $secciones = [
            'aptos' => ['titulo' => 'Capacitados disponibles', 'texto' => 'Tienen la especialidad y capacidad para tomar el servicio.', 'clase' => 'badge-green'],
            'dudosos' => ['titulo' => 'Capacitados con carga alta', 'texto' => 'Tienen el conocimiento pero pueden estar saturados.', 'clase' => 'badge-yellow'],
            'no_aptos' => ['titulo' => 'No capacitados', 'texto' => 'No tienen el servicio en sus especialidades. Solo con excepcion.', 'clase' => 'badge-red'],
        ];
    @endphp

    @foreach($secciones as $clave => $config)
        <div class="card" style="margin-top:20px;">
            <div class="candidate-inline-meta" style="margin-bottom:14px;">
                <div>
                    <div class="badge {{ $config['clase'] }}" style="margin-bottom:8px;">{{ $config['titulo'] }}</div>
                    <h2 style="margin:0; font-size:1rem; font-weight:700;">{{ $config['texto'] }}</h2>
                </div>
                <div style="font-size:0.82rem; color:#64748b;">{{ $grupos[$clave]->count() }} interno(s)</div>
            </div>

            @if($grupos[$clave]->isEmpty())
                <div style="padding:18px; background:var(--surface-2); border:1px dashed var(--border); border-radius:10px; color:#64748b; font-size:0.85rem;">
                    No hay internos en esta categoria.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px;">
                    @foreach($grupos[$clave] as $item)
                        @php
                            $interno = $item['interno'];
                            $compatibilidad = $item['compatibilidad'];
                            $ocupacion = $interno->ocupacionPorcentaje();
                            $colorOcupacion = $ocupacion < 50 ? '#10b981' : ($ocupacion < 80 ? '#f59e0b' : '#ef4444');
                        @endphp
                        <div style="border:1px solid var(--border); border-radius:12px; padding:14px; background:var(--surface);">
                            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                <div style="display:flex; gap:10px; align-items:flex-start; flex:1; min-width:0;">
                                    <x-avatar :src="$interno->avatar_url" :nombre="$interno->name" :tamano="40" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:700; font-size:0.92rem;">{{ $interno->name }}</div>
                                        <div style="font-size:0.8rem; color:#64748b; margin-top:2px;">{{ $interno->email }}</div>
                                        @if($interno->departamento)
                                            <div style="font-size:0.78rem; color:#94a3b8; margin-top:2px;">Depto: {{ $interno->departamento }}</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge {{ $config['clase'] }}">{{ $compatibilidad['puntaje'] }}/100</span>
                            </div>

                            <div style="margin-top:10px;">
                                <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:#64748b; margin-bottom:4px;">
                                    <span>Ocupacion</span>
                                    <span style="color:{{ $colorOcupacion }}; font-weight:600;">{{ $ocupacion }}%</span>
                                </div>
                                <div style="width:100%; height:6px; background:var(--surface-2); border-radius:3px; overflow:hidden;">
                                    <div style="width:{{ min(100, $ocupacion) }}%; height:100%; background:{{ $colorOcupacion }};"></div>
                                </div>
                            </div>

                            <div style="margin-top:12px; font-size:0.8rem; color:#64748b; line-height:1.5;">
                                @foreach($compatibilidad['detalles'] as $detalle)
                                    <div>- {{ $detalle }}</div>
                                @endforeach
                                <div style="margin-top:8px; color:#475569;">{{ $compatibilidad['resumen'] }}</div>
                            </div>

                            @if($tarea->asignado_a)
                                <div style="margin-top:12px; padding:8px 12px; background:var(--surface-2); border-radius:8px; font-size:0.78rem; color:#64748b; text-align:center;">
                                    Libera al interno actual para asignar a este.
                                </div>
                            @elseif(in_array($tarea->estado, ['completado', 'cancelado'], true))
                                <div style="margin-top:12px; padding:8px 12px; background:var(--surface-2); border-radius:8px; font-size:0.78rem; color:#64748b; text-align:center;">
                                    Pedido cerrado.
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.tareas.asignar', $tarea) }}" style="margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; align-items:end;">
                                    @csrf
                                    <input type="hidden" name="asignado_a" value="{{ $interno->id }}">
                                    @if($clave === 'no_aptos')
                                        <input type="hidden" name="forzar" value="1">
                                        <div style="flex:1; min-width:200px;">
                                            <label class="form-label" style="font-size:0.75rem; margin-bottom:4px;">Motivo de excepcion</label>
                                            <input type="text" name="motivo_asignacion" class="form-input" maxlength="1000" placeholder="Ej. el cliente lo pidio" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Asignar con excepcion</button>
                                    @else
                                        <button type="submit" class="btn btn-primary">Asignar</button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</x-app-layout>
