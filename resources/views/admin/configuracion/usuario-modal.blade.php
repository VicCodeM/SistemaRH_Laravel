@php
    $esNuevo = $esNuevo ?? false;
    $accion = $esNuevo
        ? route('admin.configuracion.usuarios.guardar')
        : route('admin.configuracion.usuarios.actualizar', $usuario);
@endphp

<div style="font-family:inherit;">
    {{-- Header --}}
    <div class="modal-header">
        <div>
            <h2 class="modal-title">{{ $esNuevo ? 'Nuevo usuario' : 'Editar usuario' }}</h2>
            <p class="modal-subtitle">{{ $esNuevo ? 'Crea una cuenta interna o de operación sin salir de la pantalla.' : 'Ajusta acceso, rol y estado sin perder el contexto.' }}</p>
        </div>
        <button onclick="rhModalClose()" class="modal-close">&times;</button>
    </div>

    <form method="POST" action="{{ $accion }}" style="padding:24px 28px 0;">
        @csrf
        @if(! $esNuevo) @method('PATCH') @endif

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div><label class="form-label">Nombre</label><input type="text" name="name" class="form-input" value="{{ old('name', $usuario->name) }}" required spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX"></div>
            <div><label class="form-label">Correo</label><input type="email" name="email" class="form-input" value="{{ old('email', $usuario->email) }}" required></div>
            <div><label class="form-label">Rol</label>
                <select name="rol" class="form-input" required>
                    @foreach(\App\Models\User::roles() as $key => $label)
                        <option value="{{ $key }}" {{ old('rol', $usuario->rol) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="form-label">Estado</label>
                <select name="estado" class="form-input" required>
                    @foreach(\App\Models\User::estados() as $key => $label)
                        <option value="{{ $key }}" {{ old('estado', $usuario->estado) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="form-label">Contraseña {{ $esNuevo ? '' : '(opcional)' }}</label><input type="password" name="password" class="form-input" {{ $esNuevo ? 'required' : '' }}></div>
            <div><label class="form-label">Confirmar contraseña {{ $esNuevo ? '' : '(opcional)' }}</label><input type="password" name="password_confirmation" class="form-input" {{ $esNuevo ? 'required' : '' }}></div>
        </div>

        <div style="margin-top:16px; padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:var(--bg-muted); color:var(--text-muted); font-size:0.84rem; line-height:1.6;">
            {{ $esNuevo ? 'Si el usuario es interno, puedes dejarlo activo desde el inicio.' : 'El acceso puede bloquearse o reactivarse sin duplicar cuentas.' }}
        </div>

        <div class="modal-footer" style="padding:18px 0 24px; border-top:1px solid var(--border); margin-top:20px;">
            <button type="submit" class="btn btn-primary">{{ $esNuevo ? 'Crear usuario' : 'Guardar cambios' }}</button>
            <button type="button" onclick="rhModalClose()" class="btn btn-ghost">Cerrar</button>
        </div>
    </form>

    @if(! $esNuevo)
        <div style="display:flex; gap:8px; flex-wrap:wrap; padding:0 28px 24px;">
            <form method="POST" action="{{ route('admin.configuracion.usuarios.recuperar', $usuario) }}" style="margin:0;">@csrf
                <button type="submit" class="btn btn-ghost">Reenviar acceso</button>
            </form>
            <form method="POST" action="{{ route('admin.configuracion.usuarios.estado', $usuario) }}" style="margin:0;">@csrf @method('PATCH')
                <button type="submit" class="btn btn-secondary">{{ $usuario->estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear' }}</button>
            </form>
        </div>
    @endif
</div>
