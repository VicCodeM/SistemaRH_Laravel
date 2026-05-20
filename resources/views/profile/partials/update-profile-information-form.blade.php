<section>
    <p class="text-muted text-sm mb-4">Actualiza tu información de perfil y correo electrónico.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('patch')

        {{-- Foto / Avatar con vista previa --}}
        <div class="form-group" x-data="{
            preview: null,
            seleccionar(evento) {
                const archivo = evento.target.files[0];
                if (!archivo) { this.preview = null; return; }
                if (archivo.size > 5 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 5 MB.');
                    evento.target.value = '';
                    this.preview = null;
                    return;
                }
                const lector = new FileReader();
                lector.onload = e => this.preview = e.target.result;
                lector.readAsDataURL(archivo);
            },
            limpiar() {
                this.preview = null;
                document.getElementById('input-avatar').value = '';
            }
        }" style="display:flex; align-items:center; gap:14px;">

            {{-- Avatar: muestra preview si hay, sino el guardado, sino iniciales --}}
            <div style="position:relative;">
                <template x-if="preview">
                    <img :src="preview" alt="Vista previa"
                         style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #10b981;">
                </template>
                <div x-show="!preview" x-cloak>
                    <x-avatar :src="$user->avatar_url" :nombre="$user->name" :tamano="64" />
                </div>
                <span x-show="preview" x-cloak
                      style="position:absolute; bottom:-4px; right:-4px; background:#10b981; color:#fff; font-size:9px; padding:2px 6px; border-radius:10px; font-weight:700;">
                    NUEVA
                </span>
            </div>

            <div style="flex:1;">
                <label class="form-label">Foto de perfil</label>
                <input type="file" name="avatar" id="input-avatar" accept="image/jpeg,image/png,image/webp"
                       @change="seleccionar($event)"
                       class="form-input" style="padding:6px;">
                @error('avatar') <p class="form-error">{{ $message }}</p> @enderror

                <p x-show="!preview" style="font-size:11px; color:#94a3b8; margin-top:4px;">JPG, PNG o WEBP, máximo 5 MB.</p>

                <div x-show="preview" x-cloak style="margin-top:6px; padding:6px 10px; background:#dcfce7; border-radius:6px; font-size:11px; color:#166534;">
                    👀 Estás viendo una vista previa. La foto se guardará cuando hagas clic en "Guardar Cambios".
                    <button type="button" @click="limpiar()" style="background:none; border:none; color:#dc2626; cursor:pointer; font-weight:600; margin-left:6px;">Cancelar</button>
                </div>
            </div>

            @if($user->avatar_url)
                <label x-show="!preview" x-cloak
                       style="display:flex; align-items:center; gap:6px; font-size:12px; color:#dc2626; cursor:pointer; white-space:nowrap;">
                    <input type="checkbox" name="quitar_avatar" value="1" style="accent-color:#dc2626;">
                    Quitar foto
                </label>
            @endif
        </div>

        <div class="form-group">
            <label class="form-label" for="name">Nombre Completo</label>
            <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required>
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
