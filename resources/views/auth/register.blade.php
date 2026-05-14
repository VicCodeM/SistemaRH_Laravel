<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 44px; height: 44px; background: rgba(16,185,129,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#34d399" style="width: 22px; height: 22px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Registro de Candidato</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Crea tu perfil y accede a oportunidades de empleo</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Nombre Completo</label>
            <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Tu nombre completo">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Correo Electrónico</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="tu@correo.com">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px; background: #059669;">
            Crear mi Perfil
        </button>

        <div class="divider"></div>

        <div class="text-center" style="font-size: 0.85rem; color: #64748b;">
            ¿Eres una empresa?
            <a href="{{ route('register.empresa') }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">Regístrate aquí</a>
            &nbsp;·&nbsp;
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">Inicia sesión</a>
        </div>
    </form>
</x-guest-layout>
