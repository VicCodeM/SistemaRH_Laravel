<x-app-layout>
    <div style="max-width:520px; margin:60px auto; text-align:center;">
        <div style="font-size:64px; margin-bottom:12px;">⚠️</div>
        <h1 style="font-size:1.6rem; margin:0 0 8px; color:var(--text);">Algo salió mal</h1>
        <p style="color:#64748b; margin:0 0 24px; font-size:0.95rem;">
            Tuvimos un problema. Intenta de nuevo o regresa al inicio. Si pasa otra vez, avisa al administrador.
        </p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Volver al inicio</a>
    </div>
</x-app-layout>
