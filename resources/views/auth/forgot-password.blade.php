<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">¿Olvidaste tu contraseña?</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="email">Correo Electrónico</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@correo.com">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Enviar Enlace</button>
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" style="color: var(--accent); font-size: 0.85rem; text-decoration: none; font-weight: 500;">← Volver a inicio</a>
        </div>
    </form>
</x-guest-layout>
