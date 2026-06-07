<x-guest-layout>
    <div style="text-align:center; padding: 18px 6px 4px;">
        <div style="font-size:64px; line-height:1; margin-bottom:14px; font-weight:700; color:var(--accent);">429</div>
        <h1 style="font-size:1.55rem; margin:0 0 10px; color:var(--text); font-weight:700;">Demasiadas solicitudes</h1>
        <p style="color:var(--text-muted); margin:0 auto 28px; font-size:0.96rem; line-height:1.7; max-width: 440px;">
            Hemos recibido demasiadas peticiones en poco tiempo. Espera un momento e inténtalo de nuevo.
        </p>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ url('/') }}" class="btn btn-primary">Ir a la portada</a>
        </div>
    </div>
</x-guest-layout>
