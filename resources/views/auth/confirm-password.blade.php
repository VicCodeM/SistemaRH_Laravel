<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 4px;">Confirma tu Contraseña</h3>
        <p class="text-muted" style="font-size: 0.88rem;">Esta es un área segura. Verifica tu identidad.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="Tu contraseña">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Confirmar</button>
    </form>
</x-guest-layout>
