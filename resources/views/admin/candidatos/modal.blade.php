{{-- Partial modal – candidato detalle --}}
<div style="font-family:inherit;">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:24px 28px 20px;border-bottom:1px solid #1e293b;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:rgba(16,185,129,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#34d399;">
                {{ strtoupper(substr($candidato->nombre ?? 'C', 0, 1)) }}
            </div>
            <div>
                <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#f1f5f9;">
                    {{ $candidato->nombre }} {{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }}
                </h2>
                <span style="font-size:0.78rem;color:#64748b;">{{ $candidato->puesto_deseado ?: 'Sin puesto indicado' }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @php
                $estadoColor = match($candidato->solicitud_estado) {
                    'aprobada'  => '#22c55e', 'enviada' => '#f59e0b',
                    'rechazada' => '#ef4444', default   => '#64748b'
                };
                $estadoBg = match($candidato->solicitud_estado) {
                    'aprobada'  => 'rgba(34,197,94,.12)', 'enviada'   => 'rgba(245,158,11,.12)',
                    'rechazada' => 'rgba(239,68,68,.12)', default      => 'rgba(100,116,139,.12)'
                };
                $estadoLabel = ['borrador'=>'Borrador','enviada'=>'Enviada','aprobada'=>'Aprobada','rechazada'=>'Rechazada'];
            @endphp
            <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $estadoBg }};color:{{ $estadoColor }};">
                {{ $estadoLabel[$candidato->solicitud_estado] ?? ucfirst($candidato->solicitud_estado) }}
            </span>
            <button onclick="rhModalClose()" style="width:30px;height:30px;background:#1e293b;border:none;border-radius:7px;cursor:pointer;color:#94a3b8;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>
    </div>

    {{-- Tabs simples --}}
    <div style="padding:20px 28px 0;border-bottom:1px solid #1e293b;display:flex;gap:4px;" id="cand-tabs">
        <button onclick="showTab('tab-personal')" id="btn-personal" style="padding:8px 14px;border-radius:8px 8px 0 0;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#1e3a5f;color:#60a5fa;">Datos personales</button>
        <button onclick="showTab('tab-laboral')"  id="btn-laboral"  style="padding:8px 14px;border-radius:8px 8px 0 0;border:none;cursor:pointer;font-size:13px;font-weight:500;background:transparent;color:#64748b;">Perfil laboral</button>
        <button onclick="showTab('tab-proceso')"  id="btn-proceso"  style="padding:8px 14px;border-radius:8px 8px 0 0;border:none;cursor:pointer;font-size:13px;font-weight:500;background:transparent;color:#64748b;">En proceso</button>
    </div>

    <div style="padding:24px 28px;">

        {{-- Tab: Datos personales --}}
        <div id="tab-personal">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                @php
                    $campos = [
                        'Correo'       => $candidato->usuario?->email,
                        'Teléfono'     => $candidato->celular ?: $candidato->telefono,
                        'CURP'         => $candidato->curp,
                        'RFC'          => $candidato->rfc,
                        'Edad'         => $candidato->edad ? $candidato->edad.' años' : null,
                        'Sexo'         => $candidato->sexo ? ucfirst($candidato->sexo) : null,
                        'Estado civil' => $candidato->estado_civil ? ucfirst($candidato->estado_civil) : null,
                        'Ciudad'       => $candidato->ciudad,
                        'Municipio'    => $candidato->municipio,
                        'Escolaridad'  => $candidato->escolaridad ? ucfirst($candidato->escolaridad) : null,
                    ];
                @endphp
                @foreach ($campos as $label => $valor)
                    <div>
                        <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">{{ $label }}</p>
                        <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">{{ $valor ?: '—' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tab: Perfil laboral --}}
        <div id="tab-laboral" style="display:none;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div>
                    <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Puesto deseado</p>
                    <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">{{ $candidato->puesto_deseado ?: '—' }}</p>
                </div>
                <div>
                    <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Experiencia</p>
                    <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">{{ $candidato->experiencia_anios ? $candidato->experiencia_anios.' año(s)' : '—' }}</p>
                </div>
                <div>
                    <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Sueldo deseado</p>
                    <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">
                        {{ $candidato->sueldo_deseado ? '$'.number_format($candidato->sueldo_deseado, 0).' MXN' : '—' }}
                    </p>
                </div>
                <div>
                    <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">CV</p>
                    @if ($candidato->cv_path)
                        <a href="{{ Storage::url($candidato->cv_path) }}" target="_blank" style="font-size:13px;color:#60a5fa;">Descargar CV</a>
                    @else
                        <p style="font-size:13px;color:#475569;margin:0;">Sin CV adjunto</p>
                    @endif
                </div>
            </div>
            @if ($candidato->habilidades)
                <div>
                    <p style="font-size:11px;color:#475569;margin:0 0 6px;text-transform:uppercase;letter-spacing:.5px;">Habilidades</p>
                    <p style="font-size:13px;color:#e2e8f0;margin:0;line-height:1.6;">{{ $candidato->habilidades }}</p>
                </div>
            @endif
        </div>

        {{-- Tab: En proceso --}}
        <div id="tab-proceso" style="display:none;">
            @forelse ($candidato->postulaciones as $postulacion)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#1e293b;border-radius:8px;margin-bottom:8px;">
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#e2e8f0;margin:0;">{{ $postulacion->vacante?->titulo ?? '—' }}</p>
                        <p style="font-size:12px;color:#64748b;margin:0;">{{ $postulacion->vacante?->empresa?->nombre_empresa ?? '—' }}</p>
                    </div>
                    @php $ec=['postulado'=>['#60a5fa','rgba(37,99,235,.12)'],'entrevista'=>['#f59e0b','rgba(245,158,11,.12)'],'seleccionado'=>['#22c55e','rgba(34,197,94,.12)'],'rechazado'=>['#ef4444','rgba(239,68,68,.12)']]; [$col,$bg]=$ec[$postulacion->estado]??['#64748b','rgba(100,116,139,.12)']; @endphp
                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;color:{{ $col }};background:{{ $bg }};">
                        {{ ucfirst($postulacion->estado) }}
                    </span>
                </div>
            @empty
                <p style="text-align:center;color:#475569;padding:32px 0;font-size:13px;">Sin procesos activos</p>
            @endforelse
        </div>

    </div>

    {{-- Acciones --}}
    <div style="display:flex;gap:8px;padding:0 28px 24px;">
        @if ($candidato->solicitud_estado === 'enviada')
            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}" style="margin:0;">
                @csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#22c55e;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                    ✓ Aprobar solicitud
                </button>
            </form>
            <form method="POST" action="{{ route('admin.candidatos.rechazar', $candidato) }}" style="margin:0;">
                @csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#ef4444;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                    ✗ Rechazar
                </button>
            </form>
        @elseif ($candidato->solicitud_estado === 'rechazada')
            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}" style="margin:0;">
                @csrf @method('PATCH')
                <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#22c55e;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                    Reactivar candidato
                </button>
            </form>
        @endif
        <button onclick="rhModalClose()" style="padding:9px 18px;background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:8px;cursor:pointer;font-size:13px;">
            Cerrar
        </button>
    </div>
</div>

<script>
function showTab(id) {
    ['tab-personal','tab-laboral','tab-proceso'].forEach(t => {
        document.getElementById(t).style.display = t === id ? 'block' : 'none';
    });
    ['btn-personal','btn-laboral','btn-proceso'].forEach(b => {
        const btn = document.getElementById(b);
        const active = b === 'btn-' + id.replace('tab-','');
        btn.style.background = active ? '#1e3a5f' : 'transparent';
        btn.style.color = active ? '#60a5fa' : '#64748b';
        btn.style.fontWeight = active ? '600' : '500';
    });
}
</script>
