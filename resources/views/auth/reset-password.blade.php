<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Restablecer contraseña</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Ingresa tu nueva contraseña para continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <div style="padding: 12px 14px; border: 1px solid var(--border); border-radius: 10px; background: var(--bg-muted); color: var(--text); font-size: 0.95rem; word-break: break-word;">
                {{ old('email', $request->email) }}
            </div>
            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
            <p class="text-muted" style="font-size: 0.82rem; margin-top: 6px;">
                Por seguridad, este correo queda fijado desde el enlace de recuperación.
            </p>
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Nueva contraseña</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" autofocus>
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Restablecer</button>
    </form>
</x-guest-layout>
