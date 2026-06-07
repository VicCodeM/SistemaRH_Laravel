<x-guest-layout>
    @php
        $esRestablecimiento = request()->routeIs('password.reset') || request()->is('reset-password/*');
        $mensaje = $esRestablecimiento
            ? 'El enlace de recuperación no es válido o ya expiró. Solicita uno nuevo desde la pantalla de recuperación.'
            : ($exception->getMessage() ?: 'No tienes permiso para ver este recurso.');
        $accionPrincipal = $esRestablecimiento
            ? route('password.request')
            : (auth()->check() ? url('/dashboard') : url('/'));
        $textoPrincipal = $esRestablecimiento ? 'Solicitar nuevo enlace' : 'Volver al inicio';
    @endphp

    <div style="text-align:center; padding: 18px 6px 4px;">
        <div style="font-size:64px; line-height:1; margin-bottom:14px; font-weight:700; color:var(--accent);">403</div>
        <h1 style="font-size:1.55rem; margin:0 0 10px; color:var(--text); font-weight:700;">Acceso restringido</h1>
        <p style="color:var(--text-muted); margin:0 auto 28px; font-size:0.96rem; line-height:1.7; max-width: 440px;">
            {{ $mensaje }}
        </p>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ $accionPrincipal }}" class="btn btn-primary">{{ $textoPrincipal }}</a>

            @if($esRestablecimiento)
                <a href="{{ url('/') }}" class="btn btn-secondary">Ir al inicio</a>
            @endif

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Cerrar sesión</button>
                </form>
            @endauth
        </div>
    </div>
</x-guest-layout>
