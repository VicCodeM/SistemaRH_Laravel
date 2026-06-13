@php
    $tipoServicio = \App\Models\Vacante::tiposServicio()[$vacante->tipo_servicio] ?? $vacante->tipo_servicio;
    $ultimaTarea = $vacante->serviciosAsignados->first();
    $salario = $vacante->salario_min || $vacante->salario_max
        ? '$' . number_format((float) ($vacante->salario_min ?? 0), 0) . ' - $' . number_format((float) ($vacante->salario_max ?? 0), 0) . ' MXN'
        : 'Sin definir';
    $compensacion = $vacante->compensacionDetalles();
@endphp

<div style="font-family:inherit;">
    <div class="modal-header">
        <div style="display:flex;align-items:center;gap:12px;min-width:0;">
            <div class="modal-header-icon" style="background:{{ $vacante->esReclutamiento() ? 'rgba(59,130,246,.1)' : 'rgba(14,165,233,.12)' }};color:{{ $vacante->esReclutamiento() ? '#2563eb' : '#0ea5e9' }};">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:20px;height:20px;">
                    @if($vacante->esReclutamiento())
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                    @endif
                </svg>
            </div>
            <div style="min-width:0;">
                <h2 class="modal-title">{{ $vacante->titulo }}</h2>
                <span class="modal-subtitle">{{ $vacante->empresa?->nombre_empresa ?? 'Empresa' }} · {{ $tipoServicio }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
            <span class="badge {{ \App\Models\Vacante::estadoBadgeClass($vacante->estado) }}" style="font-size:12px;">
                {{ \App\Models\Vacante::estadoLabel($vacante->estado) }}
            </span>
            <button onclick="rhModalClose()" class="modal-close">&times;</button>
        </div>
    </div>

    <div class="modal-body modal-stack">
        <div class="modal-progress-bar" style="gap:12px;align-items:flex-start;">
            <div>
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Resumen operativo</div>
                <div style="font-size:13px;color:var(--text-primary);font-weight:600;margin-top:2px;">
                    {{ $vacante->esReclutamiento() ? 'Seguimiento de candidatos y requisitos' : 'Seguimiento de servicio y asignación interna' }}
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                @if($vacante->esReclutamiento())
                    <span class="badge badge-blue" style="font-size:11px;">{{ $vacante->en_proceso_count }} en proceso</span>
                    <span class="badge badge-green" style="font-size:11px;">{{ $vacante->cupos_ocupados_count }} ocupando cupo</span>
                @else
                    <span class="badge badge-blue" style="font-size:11px;">{{ $vacante->servicios_asignados_count }} tarea(s)</span>
                    @if($ultimaTarea)
                        <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($ultimaTarea->estado) }}" style="font-size:11px;">
                            {{ \App\Models\ServicioAsignado::estadoLabel($ultimaTarea->estado) }}
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <div>
            <p class="modal-section-label">Datos principales</p>
            <div class="modal-grid-2">
                <div>
                    <p class="modal-field-label">Empresa</p>
                    <p class="modal-field-value">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Nivel jerárquico</p>
                    <p class="modal-field-value">{{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico) }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Estudios mínimos</p>
                    <p class="modal-field-value">{{ \App\Models\Vacante::nivelEstudiosLabel($vacante->nivel_estudios_minimo) }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Experiencia mínima</p>
                    <p class="modal-field-value">{{ $vacante->experiencia_minima !== null ? $vacante->experiencia_minima . ' año(s)' : 'Sin definir' }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Ubicación</p>
                    <p class="modal-field-value">{{ $vacante->ubicacion ?: 'Sin definir' }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Tipo de contrato</p>
                    <p class="modal-field-value">{{ $vacante->tipo_contrato ?: 'Sin definir' }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Salario</p>
                    <p class="modal-field-value">{{ $salario }}</p>
                </div>
                <div>
                    <p class="modal-field-label">Fecha de publicación</p>
                    <p class="modal-field-value">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
        </div>

        @if(!empty($compensacion))
            <div>
                <p class="modal-section-label">Compensación y prestaciones</p>
                <div class="modal-grid-auto">
                    @foreach($compensacion as $label => $valor)
                        <div style="padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2);">
                            <p class="modal-field-label" style="margin-bottom:8px;">{{ $label }}</p>
                            <p class="modal-field-value" style="line-height:1.6;">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($vacante->area_requerida || $vacante->descripcion || $vacante->requerimientos || $vacante->notas_internas)
            <div class="modal-grid-auto">
                @if($vacante->area_requerida)
                    <div style="padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2);">
                        <p class="modal-field-label" style="margin-bottom:8px;">Área requerida</p>
                        <p class="modal-field-value">{{ $vacante->area_requerida }}</p>
                    </div>
                @endif
                @if($vacante->descripcion)
                    <div style="padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2);">
                        <p class="modal-field-label" style="margin-bottom:8px;">Descripción</p>
                        <p class="modal-field-value" style="line-height:1.6;">{{ $vacante->descripcion }}</p>
                    </div>
                @endif
                @if($vacante->requerimientos)
                    <div style="padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2);">
                        <p class="modal-field-label" style="margin-bottom:8px;">Requerimientos</p>
                        <p class="modal-field-value" style="line-height:1.6;">{{ $vacante->requerimientos }}</p>
                    </div>
                @endif
                @if($vacante->notas_internas)
                    <div style="padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface-2);">
                        <p class="modal-field-label" style="margin-bottom:8px;">Notas internas</p>
                        <p class="modal-field-value" style="line-height:1.6;">{{ $vacante->notas_internas }}</p>
                    </div>
                @endif
            </div>
        @endif

        @if(!$vacante->esReclutamiento() && $vacante->serviciosAsignados->isNotEmpty())
            <div>
                <p class="modal-section-label">Tareas vinculadas</p>
                @foreach($vacante->serviciosAsignados as $tarea)
                    <div class="modal-list-item">
                        <div>
                            <div class="modal-list-item-title">Tarea #{{ $tarea->id }}</div>
                            <div class="modal-list-item-sub">
                                {{ $tarea->asignadoA?->name ? 'Asignada a ' . $tarea->asignadoA->name : 'Sin responsable asignado' }}
                            </div>
                        </div>
                        <span class="badge {{ \App\Models\ServicioAsignado::estadoBadgeClass($tarea->estado) }}" style="font-size:11px;">
                            {{ \App\Models\ServicioAsignado::estadoLabel($tarea->estado) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="modal-footer modal-actions-wrap" style="border-top:1px solid var(--border);padding-top:20px;justify-content:space-between;">
        <div class="modal-actions-wrap">
            <a href="{{ route('admin.vacantes.editar', $vacante) }}" class="btn btn-secondary">Editar solicitud</a>
            @if($vacante->esReclutamiento())
                <a href="{{ route('admin.vacantes.matching', $vacante) }}" class="btn btn-primary">Ver candidatos</a>
            @elseif($ultimaTarea)
                <a href="{{ route('admin.tareas.show', $ultimaTarea) }}" class="btn btn-primary">Ver tarea</a>
            @else
                <form method="POST" action="{{ route('admin.vacantes.tarea', $vacante) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Crear tarea</button>
                </form>
            @endif
        </div>

        <div class="modal-actions-wrap" style="justify-content:flex-end;">
            @if($vacante->estado === 'pendiente')
                <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}" style="margin:0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Activar</button>
                </form>
                <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'rechazar']) }}')" class="btn btn-danger">Rechazar</button>
            @elseif($vacante->estado === 'activa')
                <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'cerrar']) }}')" class="btn btn-secondary">Desactivar</button>
            @elseif($vacante->estado === 'cerrada')
                <form method="POST" action="{{ route('admin.vacantes.reabrir', $vacante) }}" style="margin:0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Reabrir</button>
                </form>
            @elseif($vacante->estado === 'rechazada')
                <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}" style="margin:0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Reactivar</button>
                </form>
            @endif

            <button type="button" onclick="rhModal('{{ route('admin.vacantes.accion.modal', [$vacante, 'eliminar']) }}')" class="btn btn-danger">Eliminar</button>
            <button onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
        </div>
    </div>
</div>
