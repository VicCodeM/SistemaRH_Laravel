{{-- Partial – no usa layout, se inyecta en #rh-modal-content --}}
<div style="font-family: inherit;">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:24px 28px 20px; border-bottom:1px solid #1e293b;">
        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:38px;height:38px;background:rgba(37,99,235,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#60a5fa" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.15rem;font-weight:700;color:#f1f5f9;">{{ $empresa->nombre_empresa }}</h2>
                    <span style="font-size:0.78rem;color:#64748b;">Registrada el {{ $empresa->created_at?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @php
                $badgeColor = match($empresa->estado) {
                    'activa'    => '#22c55e', 'pendiente' => '#f59e0b',
                    'rechazada' => '#ef4444', 'suspendida' => '#94a3b8', default => '#64748b'
                };
                $badgeBg = match($empresa->estado) {
                    'activa'    => 'rgba(34,197,94,.12)', 'pendiente' => 'rgba(245,158,11,.12)',
                    'rechazada' => 'rgba(239,68,68,.12)', 'suspendida' => 'rgba(148,163,184,.12)', default => 'rgba(100,116,139,.12)'
                };
            @endphp
            <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                {{ ucfirst($empresa->estado) }}
            </span>
            <button onclick="rhModalClose()" style="width:30px;height:30px;background:#1e293b;border:none;border-radius:7px;cursor:pointer;color:#94a3b8;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div style="padding:24px 28px;">

        {{-- Datos de la empresa --}}
        <p style="font-size:11px;font-weight:700;color:#60a5fa;letter-spacing:1px;text-transform:uppercase;margin:0 0 14px;">Datos de la empresa</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">RFC</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->rfc ?: '—' }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Teléfono</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->telefono ?: '—' }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Ciudad</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->ciudad ?: '—' }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Giro / Industria</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->descripcion ?: '—' }}</p>
            </div>
        </div>

        {{-- Contacto --}}
        <p style="font-size:11px;font-weight:700;color:#60a5fa;letter-spacing:1px;text-transform:uppercase;margin:0 0 14px;">Contacto principal</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Nombre</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->usuario?->name ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Correo</p>
                <p style="font-size:14px;color:#e2e8f0;margin:0;font-weight:500;">{{ $empresa->usuario?->email ?? '—' }}</p>
            </div>
        </div>

        {{-- Solicitudes recientes --}}
        @if ($empresa->vacantes->isNotEmpty())
            <p style="font-size:11px;font-weight:700;color:#60a5fa;letter-spacing:1px;text-transform:uppercase;margin:0 0 12px;">Solicitudes de servicio</p>
            @foreach ($empresa->vacantes as $v)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:#1e293b;border-radius:8px;margin-bottom:6px;">
                    <div>
                        <span style="font-size:13px;color:#e2e8f0;font-weight:500;">{{ $v->titulo }}</span>
                        <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ ucfirst($v->nivel_jerarquico) }}</span>
                    </div>
                    @php $ec=['pendiente'=>'#f59e0b','activa'=>'#22c55e','cerrada'=>'#64748b']; @endphp
                    <span style="font-size:11px;color:{{ $ec[$v->estado] ?? '#64748b' }};font-weight:600;">{{ ucfirst($v->estado) }}</span>
                </div>
            @endforeach
        @endif

        {{-- Acciones --}}
        <div style="display:flex;gap:8px;margin-top:24px;padding-top:20px;border-top:1px solid #1e293b;">
            @if ($empresa->estado === 'pendiente')
                <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#22c55e;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        ✓ Aprobar empresa
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.empresas.rechazar', $empresa) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#ef4444;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        ✗ Rechazar
                    </button>
                </form>
            @elseif ($empresa->estado === 'activa')
                <form method="POST" action="{{ route('admin.empresas.suspender', $empresa) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#334155;color:#94a3b8;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        Suspender
                    </button>
                </form>
            @elseif (in_array($empresa->estado, ['rechazada', 'suspendida']))
                <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="rhModalClose()" style="padding:9px 18px;background:#22c55e;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                        Reactivar
                    </button>
                </form>
            @endif
            <button onclick="rhModalClose()" style="padding:9px 18px;background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:8px;cursor:pointer;font-size:13px;">
                Cerrar
            </button>
        </div>
    </div>
</div>
