<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Bienvenido de nuevo</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Ingresa tus credenciales para continuar</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Correo electrónico</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu@correo.com">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between mb-4">
            <label for="remember_me" class="flex items-center gap-2" style="cursor: pointer;">
                <input id="remember_me" type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: var(--accent);">
                <span class="text-muted text-sm">Recordarme</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size: 0.82rem; color: var(--accent); text-decoration: none; font-weight: 500;">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            Iniciar sesión
        </button>

        <div class="divider"></div>

        <div class="text-center">
            <p class="text-muted" style="font-size: 0.85rem;">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">Regístrate aquí</a>
            </p>
        </div>
    </form>
</x-guest-layout>
