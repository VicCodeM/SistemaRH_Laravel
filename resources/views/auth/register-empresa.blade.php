<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 44px; height: 44px; background: rgba(37,99,235,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#60a5fa" style="width: 22px; height: 22px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
        </div>
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Registro de Empresa</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Solicita servicios de RH para tu organización</p>
    </div>

    <form method="POST" action="{{ route('register.empresa') }}">
        @csrf

        {{-- Datos del contacto --}}
        <p style="font-size: 0.75rem; font-weight: 700; color: #60a5fa; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px;">Contacto principal</p>

        <div class="form-group">
            <label class="form-label" for="name">Nombre completo</label>
            <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nombre del responsable">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Correo electrónico</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="correo@empresa.com">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite la contraseña">
            </div>
        </div>

        {{-- Datos de la empresa --}}
        <p style="font-size: 0.75rem; font-weight: 700; color: #60a5fa; letter-spacing: 1px; text-transform: uppercase; margin: 20px 0 12px;">Datos de la empresa</p>

        <div class="form-group">
            <label class="form-label" for="nombre_empresa">Nombre de la empresa <span style="color: #ef4444;">*</span></label>
            <input id="nombre_empresa" class="form-input" type="text" name="nombre_empresa" value="{{ old('nombre_empresa') }}" required placeholder="Razón social o nombre comercial">
            @error('nombre_empresa') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label class="form-label" for="rfc">RFC</label>
                <input id="rfc" class="form-input" type="text" name="rfc" value="{{ old('rfc') }}" placeholder="RFC (opcional)" maxlength="13" style="text-transform: uppercase;">
                @error('rfc') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono <span style="color: #ef4444;">*</span></label>
                <input id="telefono" class="form-input" type="tel" name="telefono" value="{{ old('telefono') }}" required placeholder="55 0000 0000">
                @error('telefono') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label class="form-label" for="ciudad">Ciudad <span style="color: #ef4444;">*</span></label>
                <input id="ciudad" class="form-input" type="text" name="ciudad" value="{{ old('ciudad') }}" required placeholder="Ciudad, Estado">
                @error('ciudad') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="giro_o_industria">Giro o industria</label>
                <input id="giro_o_industria" class="form-input" type="text" name="giro_o_industria" value="{{ old('giro_o_industria') }}" placeholder="Ej: Manufactura, TI, Salud">
                @error('giro_o_industria') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div style="background: rgba(37,99,235,0.07); border: 1px solid rgba(37,99,235,0.2); border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 0.82rem; color: #94a3b8;">
            <strong style="color: #60a5fa;">Nota:</strong> Tu empresa quedará pendiente de aprobación. El administrador revisará tu solicitud y te notificará por correo.
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
            Registrar Empresa
        </button>

        <div class="divider"></div>

        <div class="text-center" style="font-size: 0.85rem; color: #64748b;">
            ¿Buscas empleo?
            <a href="{{ route('register.candidato') }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">Regístrate como candidato</a>
            &nbsp;·&nbsp;
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">Inicia sesión</a>
        </div>
    </form>
</x-guest-layout>
