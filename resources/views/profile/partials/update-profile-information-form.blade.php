<section>
    <p class="text-muted text-sm mb-4">Actualiza tu información de perfil y correo electrónico.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf @method('patch')

        <div class="form-group">
            <label class="form-label" for="name">Nombre Completo</label>
            <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required autofocus>
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Correo Electrónico</label>
            <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p style="margin-top: 6px; font-size: 0.82rem; color: var(--warning);">
                    Correo no verificado.
                    <button form="send-verification" style="background: none; border: none; color: var(--accent); text-decoration: underline; cursor: pointer; font-size: 0.82rem;">Reenviar</button>
                </p>
            @endif
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            @if (session('status') === 'profile-updated')
                <span style="color: var(--success); font-size: 0.85rem; font-weight: 500;">✓ Guardado</span>
            @endif
        </div>
    </form>
</section>
