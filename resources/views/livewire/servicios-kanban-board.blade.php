<div>
    <div class="flex items-center justify-between mb-4" style="gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <select wire:model.live="servicioId" class="form-input" style="min-width: 220px; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
                <option value="">— Todos los servicios ({{ $serviciosCatalogo->count() }}) —</option>
                @foreach($serviciosCatalogo as $s)
                    <option value="{{ $s->id }}">
                        {{ $s->nombre }}@if($s->nivel_jerarquico) · {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($s->nivel_jerarquico) }}@endif
                    </option>
                @endforeach
            </select>

            <select wire:model.live="objetivoTipo" wire:change="$set('objetivoId', '')" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
                <option value="">Todos los objetivos</option>
                <option value="empresa">Empresa</option>
                <option value="candidato">Candidato</option>
                <option value="interno">Personal interno</option>
            </select>

            @if($objetivoTipo === 'empresa')
                <select wire:model.live="objetivoId" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
                    <option value="">Todas las empresas</option>
                    @foreach($empresas as $e)
                        <option value="{{ $e->id }}">{{ $e->nombre_empresa }}</option>
                    @endforeach
                </select>
            @elseif($objetivoTipo === 'candidato')
                <select wire:model.live="objetivoId" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
                    <option value="">Todos los candidatos</option>
                    @foreach($candidatos as $c)
                        <option value="{{ $c->id }}">{{ $c->nombreCompleto() }}</option>
                    @endforeach
                </select>
            @elseif($objetivoTipo === 'interno')
                <select wire:model.live="objetivoId" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
                    <option value="">Todos los internos</option>
                    @foreach($internosObjetivo as $i)
                        <option value="{{ $i->id }}">{{ $i->name }}</option>
                    @endforeach
                </select>
            @endif

            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar..." class="form-input" style="width: 220px; padding: 7px 12px; font-size: 0.85rem;">
        </div>
    </div>

    @php
        $columnas = [
            'pendientes'  => ['estado' => 'pendiente', 'label' => 'Pendiente'],
            'activos'     => ['estado' => 'activo', 'label' => 'Activo'],
            'en_proceso'  => ['estado' => 'en_proceso', 'label' => 'En proceso'],
            'completados' => ['estado' => 'completado', 'label' => 'Completado'],
        ];
    @endphp

    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px;">
        @foreach($columnas as $key => $config)
            @php
                $estado = $config['estado'];
                $items = $$key;
                $total = $totales[$key] ?? $items->count();
                $sobran = max(0, $total - $items->count());
                $estadoBadge = \App\Models\ServicioAsignado::estadoBadgeClass($estado);
            @endphp
            <div style="background:var(--surface-2); border-radius:10px; padding:14px; border:1px solid var(--border); min-height:120px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:12px;">
                    <span class="badge {{ $estadoBadge }}" style="font-size:0.72rem;">{{ $config['label'] }}</span>
                    <span class="badge {{ $estadoBadge }}" style="font-size:10px;" title="Total: {{ $total }}">{{ $total }}</span>
                </div>

                @forelse($items as $tarea)
                    @php
                        // Marca de urgencia en pendientes: más de 3 días sin tomarse
                        $diasAbierto = $tarea->created_at ? (int) $tarea->created_at->diffInDays(now()) : 0;
                        $esUrgente = in_array($estado, ['pendiente', 'activo'], true) && $diasAbierto >= 3;
                    @endphp
                    <div x-data="{ asignarOpen: false, internoId: '', cambiarObjetivoOpen: false, objetivoTipo: '', objetivoId: '' }" style="background:var(--surface); border:1px solid {{ $esUrgente ? '#ef4444' : 'var(--border)' }}; border-radius:9px; padding:11px; margin-bottom:8px;">
                        <div style="display:flex; align-items:start; justify-content:space-between; gap:6px;">
                            <div style="font-weight:600; font-size:0.88rem; flex:1;">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</div>
                            @if($esUrgente)
                                <span style="background:#fee2e2; color:#dc2626; padding:1px 6px; border-radius:10px; font-size:0.62rem; font-weight:700; white-space:nowrap;" title="Más de 3 días sin avanzar">{{ $diasAbierto }}d</span>
                            @endif
                        </div>

                        {{-- Objetivo actual --}}
                        <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">
                            {{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}
                        </div>

                        {{-- Cambiar objetivo inline --}}
                        @if(auth()->user() && (auth()->user()->esAdmin() || auth()->user()->esInterno()))
                            <div style="margin-top:4px;">
                                <button type="button" x-on:click="cambiarObjetivoOpen = !cambiarObjetivoOpen" style="font-size:0.7rem; color:#60a5fa; background:none; border:none; padding:0; cursor:pointer; text-decoration:underline;">
                                    Cambiar objetivo
                                </button>
                                <div x-show="cambiarObjetivoOpen" x-cloak x-transition style="margin-top:6px;">
                                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                        <select x-model="objetivoTipo" x-on:change="objetivoId = ''" style="flex:1; min-width:100px; font-size:0.72rem; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--border); background:white;">
                                            <option value="">Tipo...</option>
                                            <option value="empresa">Empresa</option>
                                            <option value="candidato">Candidato</option>
                                            <option value="interno">Interno</option>
                                        </select>

                                        <select x-show="objetivoTipo === 'empresa'" x-model="objetivoId" style="flex:1; min-width:120px; font-size:0.72rem; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--border); background:white;">
                                            <option value="">Seleccionar empresa...</option>
                                            @foreach($empresas as $e)
                                                <option value="{{ $e->id }}">{{ $e->nombre_empresa }}</option>
                                            @endforeach
                                        </select>
                                        <select x-show="objetivoTipo === 'candidato'" x-model="objetivoId" style="flex:1; min-width:120px; font-size:0.72rem; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--border); background:white;">
                                            <option value="">Seleccionar candidato...</option>
                                            @foreach($candidatos as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombreCompleto() }}</option>
                                            @endforeach
                                        </select>
                                        <select x-show="objetivoTipo === 'interno'" x-model="objetivoId" style="flex:1; min-width:120px; font-size:0.72rem; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--border); background:white;">
                                            <option value="">Seleccionar interno...</option>
                                            @foreach($internosObjetivo as $i)
                                                <option value="{{ $i->id }}">{{ $i->name }}</option>
                                            @endforeach
                                        </select>

                                        <button type="button"
                                            x-on:click="if(objetivoId){ $wire.cambiarObjetivo({{ $tarea->id }}, objetivoTipo, parseInt(objetivoId)); cambiarObjetivoOpen = false; objetivoTipo = ''; objetivoId = ''; }"
                                            :disabled="!objetivoId || !objetivoTipo"
                                            class="btn btn-primary"
                                            style="font-size:0.72rem; padding:3px 10px; white-space:nowrap;">
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($tarea->asignadoA)
                            <div style="display:flex; align-items:center; gap:5px; margin-top:4px;">
                                <x-avatar :src="$tarea->asignadoA->avatar_url" :nombre="$tarea->asignadoA->name" :tamano="20" />
                                <span style="font-size:0.74rem; color:#475569;">{{ $tarea->asignadoA->name }}</span>
                            </div>
                        @else
                            <div style="font-size:0.74rem; color:#94a3b8; margin-top:2px;">Sin responsable asignado</div>
                        @endif

                        @if(auth()->user() && (auth()->user()->esAdmin() || auth()->user()->esInterno()))
                            <div style="display:flex; gap:6px; margin-top:10px; flex-wrap:wrap;">
                                @if($estado === 'pendiente')
                                    @if(! $tarea->asignado_a)
                                        <button type="button" wire:click="asignarInteligente({{ $tarea->id }})" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;" title="El sistema elige automáticamente al interno con la especialidad correcta y la menor carga de trabajo">Asignar inteligente</button>
                                        <button type="button" x-on:click="asignarOpen = !asignarOpen" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Asignar manual</button>
                                        <button type="button" wire:click="cancelar({{ $tarea->id }})" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Cancelar</button>
                                    @else
                                        <button type="button" wire:click="activar({{ $tarea->id }})" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Activar</button>
                                        <button type="button" wire:click="cancelar({{ $tarea->id }})" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Cancelar</button>
                                    @endif
                                @elseif($estado === 'activo')
                                    <button type="button" wire:click="iniciar({{ $tarea->id }})" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Iniciar trabajo</button>
                                    <button type="button" wire:click="cancelar({{ $tarea->id }})" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Cancelar</button>
                                @elseif($estado === 'en_proceso')
                                    <button type="button" wire:click="completar({{ $tarea->id }})" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem;">Completar</button>
                                    <button type="button" wire:click="cancelar({{ $tarea->id }})" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Cancelar</button>
                                @elseif($estado === 'completado')
                                    <button type="button" wire:click="reabrir({{ $tarea->id }})" class="btn btn-secondary" style="padding:4px 10px; font-size:0.75rem;">Reabrir</button>
                                @endif
                            </div>

                            {{-- Top 3 internos sugeridos (visibles siempre en pedido pendiente sin asignar) --}}
                            @if($estado === 'pendiente' && ! $tarea->asignado_a)
                                @php $sugeridos = $sugerenciasPorSolicitud[$tarea->id] ?? collect(); @endphp
                                <div style="margin-top:10px; padding:8px; background:var(--surface-2); border-radius:7px; border:1px solid var(--border);">
                                    <div style="font-size:0.7rem; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px;">
                                        🎯 Internos sugeridos
                                    </div>
                                    @if($sugeridos->isEmpty())
                                        <div style="font-size:0.72rem; color:#dc2626; padding:4px 0;">
                                            Ningún interno capacitado disponible.
                                        </div>
                                    @else
                                        @foreach($sugeridos as $sugerido)
                                            @php
                                                $ocup = $sugerido->ocupacionPorcentaje();
                                                $color = $ocup < 50 ? '#10b981' : ($ocup < 80 ? '#f59e0b' : '#ef4444');
                                            @endphp
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; padding:5px 6px; background:var(--surface); border-radius:5px; margin-bottom:4px;">
                                                <div style="display:flex; gap:6px; align-items:center; flex:1; min-width:0;">
                                                    <x-avatar :src="$sugerido->avatar_url" :nombre="$sugerido->name" :tamano="24" />
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:0.74rem; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                            {{ $sugerido->name }}
                                                        </div>
                                                        <div style="font-size:0.65rem; color:{{ $color }};">
                                                            {{ $ocup }}% ocupado · {{ max(0, $sugerido->capacidad_maxima_horas - $sugerido->carga_trabajo_horas) }}h libres
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="asignarInterno({{ $tarea->id }}, {{ $sugerido->id }})" class="btn btn-primary" style="padding:3px 8px; font-size:0.68rem; white-space:nowrap;">
                                                    + Asignar
                                                </button>
                                            </div>
                                        @endforeach
                                        <a href="{{ route('admin.tareas.matching', $tarea) }}" style="display:block; text-align:center; font-size:0.7rem; color:#3b82f6; text-decoration:underline; margin-top:4px;">
                                            Ver todos los internos →
                                        </a>
                                    @endif
                                </div>
                            @endif

                            {{-- Selector dropdown alternativo (Asignar manual) --}}
                            @if($estado === 'pendiente' && ! $tarea->asignado_a)
                                <div x-show="asignarOpen" x-cloak x-transition style="margin-top:8px;" x-data="{
                                    capacitadosLoading: false,
                                    capacitadosList: [],
                                    cargarCapacitados() {
                                        this.capacitadosLoading = true;
                                        fetch('/admin/tareas/internos-capacitados?servicio_id={{ $tarea->servicio_id }}')
                                            .then(r => r.json())
                                            .then(data => { this.capacitadosList = data; this.capacitadosLoading = false; })
                                            .catch(() => { this.capacitadosLoading = false; });
                                    }
                                }" x-init="cargarCapacitados()">
                                    <div style="display:flex; gap:4px;">
                                        <select x-model="internoId" :disabled="capacitadosLoading || capacitadosList.length === 0" style="flex:1; font-size:0.72rem; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--border); background:white;">
                                            <option value="" x-text="capacitadosLoading ? 'Cargando...' : 'Elegir interno...'"></option>
                                            <template x-for="interno in capacitadosList" :key="interno.id">
                                                <option :value="interno.id" x-text="interno.name"></option>
                                            </template>
                                        </select>
                                        <button type="button"
                                            x-on:click="if(internoId){ $wire.asignarInterno({{ $tarea->id }}, parseInt(internoId)); asignarOpen = false; internoId = ''; }"
                                            :disabled="!internoId || capacitadosLoading"
                                            class="btn btn-primary"
                                            style="font-size:0.72rem; padding:3px 10px; white-space:nowrap;">
                                            Asignar e iniciar
                                        </button>
                                    </div>
                                    <template x-if="!capacitadosLoading && capacitadosList.length === 0">
                                        <span style="font-size:0.72rem; color:#dc2626; margin-top:4px; display:block;">Ningún interno capacitado para este servicio.</span>
                                    </template>
                                </div>
                            @endif
                        @endif
                    </div>
                @empty
                    <div style="color:#64748b; font-size:0.82rem;">Sin servicios en este estado.</div>
                @endforelse

                @if($sobran > 0)
                    <a href="{{ route('admin.tareas.index', ['estado' => $estado]) }}"
                       style="display:block; text-align:center; padding:8px; margin-top:6px; background:var(--surface); border:1px dashed var(--border); border-radius:7px; font-size:0.75rem; color:var(--accent); text-decoration:none; font-weight:500;">
                        Ver {{ $sobran }} más en lista →
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    @if(array_sum($totales) > $limite * 4)
        <div style="margin-top:14px; padding:10px 14px; background:rgba(59,130,246,.06); border-radius:8px; font-size:12px; color:#64748b; text-align:center;">
            💡 Mostrando los {{ $limite }} más urgentes por columna. <a href="{{ route('admin.tareas.index') }}" style="color:var(--accent); font-weight:600;">Ver listado completo</a> para filtrar y buscar.
        </div>
    @endif

    {{-- Cancelados (colapsable, limitado) --}}
    @php $totalCancelados = $totales['cancelados'] ?? $cancelados->count(); @endphp
    @if($totalCancelados > 0)
        <details style="margin-top:14px;">
            <summary style="cursor:pointer; font-size:0.82rem; color:#64748b; user-select:none;">
                Ver {{ $totalCancelados }} servicio(s) cancelado(s)
            </summary>
            <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($cancelados as $tarea)
                    <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:7px; padding:9px 12px; opacity:0.8; font-size:0.82rem;">
                        <span style="font-weight:500;">{{ $tarea->servicio?->nombre ?? 'Servicio' }}</span>
                        <span style="color:#64748b; margin-left:6px;">{{ $tarea->asignableNombre() }}</span>
                        @if(auth()->user() && (auth()->user()->esAdmin() || auth()->user()->esInterno()))
                            <button type="button" wire:click="reabrir({{ $tarea->id }})" class="btn btn-secondary" style="margin-left:8px; padding:2px 8px; font-size:0.72rem;">Reabrir</button>
                        @endif
                    </div>
                @endforeach
                @if($totalCancelados > $cancelados->count())
                    <a href="{{ route('admin.tareas.index', ['estado' => 'cancelado']) }}" style="background:var(--surface); border:1px dashed var(--border); border-radius:7px; padding:9px 12px; font-size:0.82rem; color:var(--accent); text-decoration:none;">
                        Ver {{ $totalCancelados - $cancelados->count() }} más →
                    </a>
                @endif
            </div>
        </details>
    @endif
</div>
