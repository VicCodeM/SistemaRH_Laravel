<section>
    <p class="text-muted text-sm mb-4">Asegúrate de usar una contraseña segura.</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf @method('put')

        <div class="form-group">
            <label class="form-label" for="update_password_current_password">Contraseña Actual</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-input" autocomplete="current-password">
            @error('current_password', 'updatePassword') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="update_password_password">Nueva Contraseña</label>
            <input id="update_password_password" name="password" type="password" class="form-input" autocomplete="new-password">
            @error('password', 'updatePassword') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="update_password_password_confirmation">Confirmar Contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password">
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            @if (session('status') === 'password-updated')
                <span style="color: var(--success); font-size: 0.85rem; font-weight: 500;">✓ Guardado</span>
            @endif
        </div>
    </form>
</section>
