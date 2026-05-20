{{-- Partial modal – candidato detalle (tema light) --}}
<div style="font-family:inherit;">

    {{-- Header --}}
    <div class="modal-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <x-avatar :src="$candidato->usuario?->avatar_url" :nombre="$candidato->nombre . ' ' . ($candidato->apellido_paterno ?? '')" :tamano="48" />
            <div>
                <h2 class="modal-title">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }}</h2>
                <span class="modal-subtitle">{{ $candidato->puesto_deseado ?: 'Sin puesto indicado' }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="badge {{ \App\Models\Candidato::solicitudEstadoBadgeClass($candidato->solicitud_estado) }}" style="font-size:12px;">
                {{ \App\Models\Candidato::solicitudEstadoLabel($candidato->solicitud_estado) }}
            </span>
            <span class="badge badge-blue" style="font-size:12px;">{{ $candidato->solicitudProgreso() }}%</span>
            <button onclick="rhModalClose()" class="modal-close">&times;</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="padding:16px 28px 0;border-bottom:1px solid var(--border);display:flex;gap:4px;flex-wrap:wrap;" id="cand-tabs">
        <button onclick="showTab('tab-personal')" id="btn-personal" class="modal-tab active">Datos personales</button>
        <button onclick="showTab('tab-laboral')" id="btn-laboral" class="modal-tab">Perfil laboral</button>
        <button onclick="showTab('tab-proceso')" id="btn-proceso" class="modal-tab">En proceso</button>
    </div>

    <div class="modal-body">
        {{-- Avance --}}
        <div class="modal-progress-bar" style="margin-bottom:18px;">
            <div>
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Avance de la solicitud</div>
                <div style="font-size:13px;color:var(--text-primary);font-weight:600;">{{ $candidato->solicitudProgreso() }}% completado</div>
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="{{ route('admin.candidatos.solicitud.pdf', $candidato) }}" target="_blank" class="btn btn-secondary btn-sm" title="Descargar la solicitud completa en PDF">📄 PDF</a>
                <a href="{{ route('admin.candidatos.solicitud', $candidato) }}" class="btn btn-primary btn-sm">Editar solicitud</a>
            </div>
        </div>

        {{-- Tab: Datos personales --}}
        <div id="tab-personal">
            <div class="modal-grid-2">
                @php
                    $campos = [
                        'Correo' => $candidato->usuario?->email, 'Teléfono' => $candidato->celular ?: $candidato->telefono,
                        'CURP' => $candidato->curp, 'RFC' => $candidato->rfc,
                        'Edad' => $candidato->edad ? $candidato->edad.' años' : null, 'Sexo' => $candidato->sexo ? ucfirst($candidato->sexo) : null,
                        'Estado civil' => $candidato->estado_civil ? ucfirst($candidato->estado_civil) : null, 'Ciudad' => $candidato->ciudad,
                        'Municipio' => $candidato->municipio, 'Escolaridad' => $candidato->escolaridad ? ucfirst($candidato->escolaridad) : null,
                    ];
                @endphp
                @foreach ($campos as $label => $valor)
                    <div>
                        <p class="modal-field-label">{{ $label }}</p>
                        <p class="modal-field-value">{{ $valor ?: '—' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tab: Perfil laboral --}}
        <div id="tab-laboral" style="display:none;">
            <div class="modal-grid-2" style="margin-bottom:16px;">
                <div><p class="modal-field-label">Puesto deseado</p><p class="modal-field-value">{{ $candidato->puesto_deseado ?: '—' }}</p></div>
                <div><p class="modal-field-label">Experiencia</p><p class="modal-field-value">{{ $candidato->experiencia_anios ? $candidato->experiencia_anios.' año(s)' : '—' }}</p></div>
                <div><p class="modal-field-label">Sueldo deseado</p><p class="modal-field-value">{{ $candidato->sueldo_deseado ? '$'.number_format($candidato->sueldo_deseado, 0).' MXN' : '—' }}</p></div>
                <div>
                    <p class="modal-field-label">CV</p>
                    @if ($candidato->cv_path)
                        <a href="{{ Storage::url($candidato->cv_path) }}" target="_blank" style="font-size:13px;color:var(--accent);">Descargar CV</a>
                    @else
                        <p style="font-size:13px;color:var(--text-muted);margin:0;">Sin CV adjunto</p>
                    @endif
                </div>
            </div>
            @if ($candidato->habilidades)
                <div><p class="modal-field-label">Habilidades</p><p class="modal-field-value" style="line-height:1.6;">{{ $candidato->habilidades }}</p></div>
            @endif
        </div>

        {{-- Tab: En proceso --}}
        <div id="tab-proceso" style="display:none;">
            @forelse ($candidato->postulaciones as $postulacion)
                <div class="modal-list-item">
                    <div>
                        <p class="modal-list-item-title" style="margin:0;">{{ $postulacion->vacante?->titulo ?? '—' }}</p>
                        <p class="modal-list-item-sub" style="margin:0;">{{ $postulacion->vacante?->empresa?->nombre_empresa ?? '—' }}</p>
                    </div>
                    <span class="badge badge-gray" style="font-size:11px;">{{ \App\Models\Postulacion::estadoLabel($postulacion->estado) }}</span>
                </div>
            @empty
                <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px;">Sin procesos activos</p>
            @endforelse
        </div>
    </div>

    {{-- Acciones --}}
    <div class="modal-footer modal-actions-wrap" style="border-top:1px solid var(--border);padding-top:20px;">
        @if ($candidato->solicitud_estado === 'enviada')
            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-success">✓ Aprobar solicitud</button>
            </form>
            <form method="POST" action="{{ route('admin.candidatos.rechazar', $candidato) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-danger">✗ Rechazar</button>
            </form>
            <a href="{{ route('admin.candidatos.solicitud', $candidato) }}" class="btn btn-ghost">Editar solicitud</a>
        @elseif ($candidato->solicitud_estado === 'rechazada')
            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" class="btn btn-success">Reactivar candidato</button>
            </form>
        @endif
        <button onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
    </div>
</div>

<script>
function showTab(id) {
    ['tab-personal','tab-laboral','tab-proceso'].forEach(t => {
        document.getElementById(t).style.display = t === id ? 'block' : 'none';
    });
    document.querySelectorAll('#cand-tabs .modal-tab').forEach(btn => {
        const active = btn.id === 'btn-' + id.replace('tab-','');
        btn.classList.toggle('active', active);
    });
}
</script>
