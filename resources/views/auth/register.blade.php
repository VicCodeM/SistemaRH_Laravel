<x-guest-layout max-width="760px">
    <div style="display:grid; gap:20px;">
        <div style="text-align:center;">
            <div style="width:56px; height:56px; background:rgba(16,185,129,.12); border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981" style="width:28px; height:28px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A18 18 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z" />
                </svg>
            </div>
            <h3 style="font-size:1.45rem; font-weight:800; margin-bottom:6px;">Registro de candidato</h3>
            <p class="text-muted" style="font-size:0.92rem; margin:0;">
                Solo creas tu acceso. Tu perfil completo y tu solicitud se capturan después, desde tu panel.
            </p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px; align-items:start;">
            <div class="card" style="padding:22px;">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div style="margin-bottom:18px;">
                        <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">Crea tu acceso</h4>
                        <p style="margin:0; color:#64748b; font-size:0.86rem;">
                            Te pedimos solo lo indispensable para entrar al sistema.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre completo</label>
                        <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Escribe tu nombre completo">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="tu@correo.com">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                        <div class="form-group">
                            <label class="form-label" for="password">Contraseña</label>
                            <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña">
                        </div>
                    </div>

                    <label style="display:flex; align-items:flex-start; gap:8px; margin-top:20px; font-size:0.84rem; color:#475569; line-height:1.5; cursor:pointer;">
                        <input type="checkbox" name="acepta_terminos" value="1" {{ old('acepta_terminos') ? 'checked' : '' }} required style="margin-top:3px; width:16px; height:16px; flex-shrink:0;">
                        <span>
                            Acepto los
                            <a href="{{ route('paginas.terminos') }}" target="_blank" style="color:var(--accent);">Términos del servicio</a>
                            y la
                            <a href="{{ route('paginas.privacidad') }}" target="_blank" style="color:var(--accent);">Política de privacidad</a>.
                        </span>
                    </label>
                    @error('acepta_terminos')
                        <p style="margin:6px 0 0; font-size:0.8rem; color:#dc2626;">{{ $message }}</p>
                    @enderror

                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:18px;">
                        <div style="font-size:0.84rem; color:#64748b; line-height:1.5;">
                            Después de entrar, completarás tu solicitud por pestañas.
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg" style="min-width:180px;">
                            Crear cuenta
                        </button>
                    </div>
                </form>
            </div>

            <aside class="card" style="padding:18px; background:linear-gradient(180deg, rgba(16,185,129,.08), rgba(37,99,235,.04)); border:1px solid rgba(148,163,184,.16);">
                <h4 style="margin:0 0 10px; font-size:1rem; font-weight:800;">Así funciona</h4>
                <div style="display:grid; gap:10px; font-size:0.88rem; color:#475569; line-height:1.6;">
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">1. Acceso</strong>
                        Crea tu correo y contraseña para entrar.
                    </div>
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">2. Solicitud</strong>
                        Completa tus datos, estudios, experiencia y aspiración.
                    </div>
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">3. Revisión</strong>
                        El admin valida tu solicitud y decide el siguiente paso.
                    </div>
                </div>

                <div style="margin-top:16px; padding:12px; border-radius:12px; background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.18); color:#166534; font-size:0.84rem; line-height:1.5;">
                    Si la configuración de candidatos exige aprobación previa, primero verás el estado de revisión y luego podrás completar tu solicitud.
                </div>

                <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; font-size:0.88rem;">
                    <span class="badge badge-blue">Simple</span>
                    <span class="badge badge-green">Sin pasos extra</span>
                    <span class="badge badge-yellow">100% en español</span>
                </div>
            </aside>
        </div>

        <div class="divider"></div>

        <div class="text-center" style="font-size:0.88rem; color:#64748b;">
            ¿Eres una empresa?
            <a href="{{ route('register.empresa') }}" style="color:var(--accent); font-weight:600; text-decoration:none;">Regístrate como empresa</a>
            &nbsp;·&nbsp;
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" style="color:var(--accent); font-weight:600; text-decoration:none;">Inicia sesión</a>
        </div>
    </div>
</x-guest-layout>
