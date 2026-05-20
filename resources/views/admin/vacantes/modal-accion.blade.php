@php
    $tipoServicio = \App\Models\Vacante::tiposServicio()[$vacante->tipo_servicio] ?? $vacante->tipo_servicio;
    $esEliminacion = $accion === 'eliminar';
@endphp

<div style="padding:28px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
        <div style="width:44px;height:44px;border-radius:12px;background:var(--danger-light);color:var(--danger);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:22px;height:22px;">
                @if($esEliminacion)
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-9.75 0V6a1.5 1.5 0 0 1 1.5-1.5h4.5A1.5 1.5 0 0 1 15.75 6v1.5m-8.25 0v10.125A2.625 2.625 0 0 0 10.125 20.25h3.75A2.625 2.625 0 0 0 16.5 17.625V7.5m-6 3v6m3-6v6"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                @endif
            </svg>
        </div>
        <div>
            <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ $config['titulo'] }}</h2>
            <p style="margin:4px 0 0;color:#64748b;font-size:0.88rem;">{{ $config['descripcion'] }}</p>
        </div>
    </div>

    <div style="padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid var(--border);margin-bottom:20px;">
        <p style="margin:0;font-size:0.95rem;font-weight:600;color:var(--text);">{{ $vacante->titulo }}</p>
        <p style="margin:4px 0 0;font-size:0.83rem;color:#64748b;">{{ $vacante->empresa?->nombre_empresa ?? 'Empresa' }} · {{ $tipoServicio }}</p>
    </div>

    <p style="margin:0 0 20px;color:#475569;font-size:0.9rem;">{{ $config['mensaje'] }}</p>

    <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
        <button type="button" onclick="rhModalClose()" class="btn btn-secondary">Cancelar</button>
        <form method="POST" action="{{ $config['ruta'] }}" style="margin:0;">
            @csrf
            @if($config['metodo'] !== 'POST')
                @method($config['metodo'])
            @endif
            <button type="submit" class="btn {{ $config['clase'] }}">{{ $config['boton'] }}</button>
        </form>
    </div>
</div>
