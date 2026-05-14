{{-- Partial modal – personal externo --}}
<div style="font-family:inherit;">

    <div style="display:flex;justify-content:space-between;align-items:center;padding:24px 28px 20px;border-bottom:1px solid #1e293b;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:rgba(139,92,246,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#a78bfa;">
                {{ strtoupper(substr($persona->nombre, 0, 1)) }}
            </div>
            <div>
                <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#f1f5f9;">{{ $persona->nombre }} {{ $persona->apellidos }}</h2>
                <span style="font-size:0.78rem;color:#64748b;">
                    {{ \App\Models\CatalogoServicio::tipos()[$persona->especialidad] ?? $persona->especialidad }}
                    @if ($persona->empresa_o_razon_social) · {{ $persona->empresa_o_razon_social }} @endif
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @php
                $dc = ['disponible'=>['#22c55e','rgba(34,197,94,.12)'],'ocupado'=>['#f59e0b','rgba(245,158,11,.12)'],'inactivo'=>['#ef4444','rgba(239,68,68,.12)']];
                [$col,$bg] = $dc[$persona->disponibilidad] ?? ['#64748b','rgba(100,116,139,.12)'];
                $dl = ['disponible'=>'Disponible','ocupado'=>'Ocupado','inactivo'=>'Inactivo'];
            @endphp
            <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $bg }};color:{{ $col }};">
                {{ $dl[$persona->disponibilidad] ?? $persona->disponibilidad }}
            </span>
            <button onclick="rhModalClose()" style="width:30px;height:30px;background:#1e293b;border:none;border-radius:7px;cursor:pointer;color:#94a3b8;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>
    </div>

    <div style="padding:24px 28px;">

        <p style="font-size:11px;font-weight:700;color:#a78bfa;letter-spacing:1px;text-transform:uppercase;margin:0 0 14px;">Contacto</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Correo</p>
                <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">{{ $persona->email }}</p>
            </div>
            <div>
                <p style="font-size:11px;color:#475569;margin:0 0 3px;text-transform:uppercase;letter-spacing:.5px;">Teléfono</p>
                <p style="font-size:13px;color:#e2e8f0;margin:0;font-weight:500;">{{ $persona->telefono ?: '—' }}</p>
            </div>
        </div>

        <p style="font-size:11px;font-weight:700;color:#a78bfa;letter-spacing:1px;text-transform:uppercase;margin:0 0 12px;">Niveles que cubre</p>
        @php $niveles = \App\Models\CatalogoServicio::nivelesJerarquicos(); @endphp
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
            @foreach ($persona->niveles_jerarquicos ?? [] as $n)
                <span style="padding:4px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;font-size:12px;color:#94a3b8;">
                    {{ $niveles[$n] ?? $n }}
                </span>
            @endforeach
        </div>

        @if ($persona->descripcion)
            <p style="font-size:11px;font-weight:700;color:#a78bfa;letter-spacing:1px;text-transform:uppercase;margin:0 0 8px;">Perfil / Descripción</p>
            <p style="font-size:13px;color:#94a3b8;line-height:1.65;margin:0 0 20px;">{{ $persona->descripcion }}</p>
        @endif

        @if ($persona->cv_path)
            <a href="{{ Storage::url($persona->cv_path) }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#1e293b;border:1px solid #334155;border-radius:8px;color:#60a5fa;text-decoration:none;font-size:13px;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Descargar CV
            </a>
        @endif

        <div style="display:flex;gap:8px;margin-top:24px;padding-top:20px;border-top:1px solid #1e293b;">
            <a href="{{ route('admin.personal-externo.edit', $persona) }}"
               style="padding:9px 18px;background:#1e3a5f;color:#60a5fa;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none;"
               onclick="rhModalClose()">
                Editar datos
            </a>
            <button onclick="rhModalClose()" style="padding:9px 18px;background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:8px;cursor:pointer;font-size:13px;">
                Cerrar
            </button>
        </div>
    </div>
</div>
