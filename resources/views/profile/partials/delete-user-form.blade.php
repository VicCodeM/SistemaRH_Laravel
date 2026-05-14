<section>
    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px;">
        Una vez eliminada tu cuenta, todos tus datos serán borrados permanentemente.
    </p>

    <button type="button" class="btn btn-danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Eliminar Mi Cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" style="padding: 24px;">
            @csrf @method('delete')

            <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 8px;">¿Estás seguro?</h3>
            <p class="text-muted text-sm mb-4">Esta acción es irreversible. Ingresa tu contraseña para confirmar.</p>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input id="password" name="password" type="password" class="form-input" placeholder="Tu contraseña actual">
                @error('password', 'userDeletion') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3" style="justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close')">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar Cuenta</button>
            </div>
        </form>
    </x-modal>
</section>
