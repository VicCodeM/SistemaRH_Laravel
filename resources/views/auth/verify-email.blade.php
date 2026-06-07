<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Verifica tu correo</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Gracias por registrarte. Antes de continuar, verifica tu correo electrónico.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div style="background: var(--success-light); color: #059669; padding: 12px 16px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 20px;">
            Se ha enviado un nuevo enlace de verificación a tu correo.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Reenviar verificación</button>
    </form>

    <div class="divider"></div>

    <form method="POST" action="{{ route('logout') }}" data-no-spa>
        @csrf
        <button type="submit" class="btn btn-secondary btn-lg" style="width: 100%;">Cerrar sesión</button>
    </form>
</x-guest-layout>
