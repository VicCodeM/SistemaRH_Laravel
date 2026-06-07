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

        <div style="text-align:center;">
            <p class="text-muted" style="font-size: 0.85rem; margin-bottom:12px;">¿No tienes cuenta? Regístrate como:</p>
            <div style="display:flex; gap:10px;">
                <a href="{{ route('register.candidato') }}" style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border:1.5px solid var(--accent); border-radius:8px; color:var(--accent); font-weight:600; font-size:0.85rem; text-decoration:none; transition:all .15s;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Busco empleo
                </a>
                <a href="{{ route('register.empresa') }}" style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:10px 14px; border:1.5px solid var(--accent); border-radius:8px; color:var(--accent); font-weight:600; font-size:0.85rem; text-decoration:none; transition:all .15s;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    Soy empresa
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
