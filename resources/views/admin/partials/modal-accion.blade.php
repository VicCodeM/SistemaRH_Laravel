@php
    $esPeligro = str_contains($config['clase'] ?? '', 'danger');
    $permitido = $config['permitido'] ?? true;
@endphp

<div style="padding:28px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
        <div style="width:44px;height:44px;border-radius:12px;background:{{ $esPeligro ? 'var(--danger-light)' : 'var(--accent-light)' }};color:{{ $esPeligro ? 'var(--danger)' : 'var(--accent)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:22px;height:22px;">
                @if($esPeligro)
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zm-9.303-3.374c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                @endif
            </svg>
        </div>
        <div>
            <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ $config['titulo'] }}</h2>
            <p style="margin:4px 0 0;color:#64748b;font-size:0.88rem;">{{ $config['descripcion'] }}</p>
        </div>
    </div>

    <div style="padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid var(--border);margin-bottom:20px;">
        <p style="margin:0;font-size:0.95rem;font-weight:600;color:var(--text);">{{ $registro['titulo'] }}</p>
        @if(!empty($registro['detalle']))
            <p style="margin:4px 0 0;font-size:0.83rem;color:#64748b;">{{ $registro['detalle'] }}</p>
        @endif
    </div>

    <p style="margin:0 0 20px;color:#475569;font-size:0.9rem;">{{ $config['mensaje'] }}</p>

    @if(!empty($config['aviso']))
        <div style="margin-bottom:20px;padding:12px 14px;border-radius:10px;background:var(--warning-light);color:#92400e;border:1px solid #fcd34d;">
            {{ $config['aviso'] }}
        </div>
    @endif

    <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
        <button type="button" onclick="rhModalClose()" class="btn btn-secondary">Cancelar</button>
        @if($permitido)
            <form method="POST" action="{{ $config['ruta'] }}" style="margin:0;">
                @csrf
                @if(($config['metodo'] ?? 'POST') !== 'POST')
                    @method($config['metodo'])
                @endif
                @foreach(($config['campos'] ?? []) as $nombre => $valor)
                    <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
                @endforeach
                <button type="submit" class="btn {{ $config['clase'] }}">{{ $config['boton'] }}</button>
            </form>
        @endif
    </div>
</div>
