@props([
    'titulo'   => '¿Qué sigue?',
    'acciones' => collect(),  // Collection de arrays: icono, titulo, mensaje, href, color, etiquetaBoton
])

<div class="card fade-in" style="padding:20px; margin-bottom:20px;">
    <h2 style="margin:0 0 14px; font-size:0.95rem; font-weight:700; color:var(--text);">{{ $titulo }}</h2>

    <div class="pending-actions-grid">
        @foreach($acciones as $a)
            <a href="{{ $a['href'] }}"
               class="pending-action-item"
               style="border-left-color: {{ $a['color'] }};"
               onmouseover="this.style.transform='translateX(2px)'"
               onmouseout="this.style.transform='translateX(0)'">
                <div style="font-size:28px; line-height:1; flex-shrink:0;">{{ $a['icono'] }}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:0.9rem; color:var(--text); margin-bottom:2px;">
                        {{ $a['titulo'] }}
                    </div>
                    <div style="font-size:0.78rem; color:#64748b; line-height:1.4;">
                        {{ $a['mensaje'] }}
                    </div>
                    <div style="margin-top:8px; font-size:0.75rem; color:{{ $a['color'] }}; font-weight:600;">
                        {{ $a['etiquetaBoton'] ?? 'Ir' }} →
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
