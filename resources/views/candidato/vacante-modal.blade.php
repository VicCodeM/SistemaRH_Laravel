{{-- Modal de detalle de solicitud para candidato (tema light) --}}
<div style="font-family:inherit;">
    <div class="modal-header">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="modal-header-icon" style="background:rgba(59,130,246,.1);color:#2563eb;font-size:18px;font-weight:700;">
                {{ strtoupper(substr($vacante->empresa?->nombre_empresa ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h2 class="modal-title">{{ $vacante->titulo }}</h2>
                <span class="modal-subtitle">{{ $vacante->empresa?->nombre_empresa ?? 'Empresa' }}</span>
            </div>
        </div>
        <button onclick="rhModalClose()" class="modal-close">&times;</button>
    </div>

    <div class="modal-body modal-stack">
        @php
            $compensacion = $vacante->compensacionDetalles();
        @endphp

        {{-- Resumen --}}
        @if($vacante->requisitoResumen())
            <p style="margin:0;color:var(--text-secondary);font-size:13px;line-height:1.5;">{{ $vacante->requisitoResumen() }}</p>
        @endif

        {{-- Indicadores --}}
        <div class="modal-grid-3">
            @foreach ([
                'Jerarquía' => \App\Models\CatalogoServicio::nivelJerarquicoLabel($vacante->nivel_jerarquico),
                'Estudios mínimos' => \App\Models\Vacante::nivelEstudiosLabel($vacante->nivel_estudios_minimo) ?: 'Sin definir',
                'Experiencia' => $vacante->experiencia_minima !== null ? $vacante->experiencia_minima . ' año(s)' : 'Sin definir'
            ] as $label => $valor)
                <div style="padding:12px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
                    <div class="modal-field-label">{{ $label }}</div>
                    <div style="margin-top:6px; font-weight:600; color:var(--text-primary);">{{ $valor }}</div>
                </div>
            @endforeach
        </div>

        {{-- Descripción --}}
        <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
            <div class="modal-field-label" style="margin-bottom:8px;">Descripción</div>
            <div style="color:var(--text-secondary); line-height:1.6; font-size:13px; white-space:pre-wrap;">{{ $vacante->descripcion ?: 'Sin descripción detallada.' }}</div>
        </div>

        {{-- Ubicación y contrato --}}
        <div class="modal-grid-2">
            @foreach (['Ubicación' => $vacante->ubicacion ?: 'Sin definir', 'Contrato' => $vacante->tipo_contrato ?: 'Sin definir'] as $label => $valor)
                <div style="padding:12px 14px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
                    <div class="modal-field-label">{{ $label }}</div>
                    <div style="margin-top:6px; font-weight:600; color:var(--text-primary);">{{ $valor }}</div>
                </div>
            @endforeach
        </div>

        @if($postulacionActual)
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
                <div class="modal-field-label" style="margin-bottom:8px;">Estado de tu solicitud</div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <span class="badge {{ \App\Models\Postulacion::estadoBadgeClass($postulacionActual->estado) }}">
                        {{ \App\Models\Postulacion::estadoLabel($postulacionActual->estado) }}
                    </span>
                    @if($postulacionActual->fecha_postulacion)
                        <span style="color:var(--text-secondary); font-size:13px;">Postulada el {{ $postulacionActual->fecha_postulacion->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        @endif

        @if(!empty($compensacion))
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
                <div class="modal-field-label" style="margin-bottom:8px;">Compensación y prestaciones</div>
                <div style="display:grid; gap:10px;">
                    @foreach($compensacion as $label => $valor)
                        <div style="display:grid; grid-template-columns:170px 1fr; gap:10px; align-items:start;">
                            <span class="modal-field-label">{{ $label }}</span>
                            <span style="color:var(--text-secondary); line-height:1.6; font-size:13px;">{{ $valor }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($vacante->requerimientos)
            <div style="padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted);">
                <div class="modal-field-label" style="margin-bottom:8px;">Requerimientos</div>
                <div style="color:var(--text-secondary); line-height:1.6; font-size:13px; white-space:pre-wrap;">{{ $vacante->requerimientos }}</div>
            </div>
        @endif

        @if($yaPostulado)
            <div class="alert alert-info">Ya enviaste una solicitud para esta vacante.</div>
        @elseif(! $puedePostular)
            <div class="alert alert-warning">Debes tener tu solicitud aprobada para poder aplicar.</div>
        @endif
    </div>

    <div class="modal-footer modal-actions-wrap" style="justify-content:space-between;border-top:1px solid var(--border);padding-top:20px;">
        <button onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
        @if($puedePostular)
            <form method="POST" action="{{ route('candidato.postular', $vacante) }}" style="margin:0;">@csrf
                <button type="submit" class="btn btn-primary">Solicitar entrevista</button>
            </form>
        @endif
    </div>
</div>
