<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.personal-interno.index') }}">Personal interno</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nuevo</span>
        </nav>
        <h1 class="page-title">Agregar personal interno</h1>
        <p class="page-subtitle">Define los datos, capacidad de trabajo y los servicios que sabe brindar. Se enviará un enlace de acceso al correo.</p>
    </x-slot>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger); max-width:760px;">
            <ul style="margin:0; padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.personal-interno.guardar') }}" style="max-width:760px; display:flex; flex-direction:column; gap:18px;">
        @csrf

        {{-- Datos básicos --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">1. Datos básicos</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX"
                           placeholder="Ej. María González"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="colaborador@empresa.com"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); box-sizing:border-box;">
                </div>
            </div>
            <p style="margin:8px 0 0; font-size:12px; color:#64748b;">Se enviará un correo con el enlace para establecer su contraseña.</p>
        </div>

        {{-- Configuración de trabajo --}}
        <div class="card" style="padding:24px;">
            <h2 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">2. Capacidad de trabajo</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Horas por semana</label>
                    <input type="number" name="capacidad_maxima_horas" value="{{ old('capacidad_maxima_horas', 40) }}" min="1" max="80"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); box-sizing:border-box;">
                    <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Capacidad máxima de trabajo</p>
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Departamento</label>
                    <input type="text" name="departamento" value="{{ old('departamento') }}" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX"
                           placeholder="Ej. Capacitación"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); box-sizing:border-box;">
                    <p style="margin:4px 0 0; font-size:11px; color:#94a3b8;">Opcional</p>
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Disponibilidad</label>
                    <select name="disponibilidad"
                            style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                        <option value="disponible" @selected(old('disponibilidad', 'disponible') === 'disponible')>Disponible</option>
                        <option value="de_licencia" @selected(old('disponibilidad') === 'de_licencia')>De licencia</option>
                        <option value="fuera" @selected(old('disponibilidad') === 'fuera')>Fuera</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Especialidades --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex; align-items:start; justify-content:space-between; gap:12px; margin-bottom:14px; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 4px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#475569;">3. Especialidades (servicios que sabe brindar)</h2>
                    <p style="margin:0; font-size:12px; color:#64748b;">Selecciona los servicios del catálogo. Solo se le podrán asignar pedidos de estos servicios.</p>
                </div>
                @if($servicios->isNotEmpty())
                    <div style="font-size:12px; color:#64748b; display:flex; gap:10px;">
                        <button type="button" onclick="document.querySelectorAll('input[name=\'servicios[]\']').forEach(c => c.checked = true)" style="background:none; border:none; color:#3b82f6; cursor:pointer; font-size:12px; text-decoration:underline;">Marcar todas</button>
                        <button type="button" onclick="document.querySelectorAll('input[name=\'servicios[]\']').forEach(c => c.checked = false)" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:12px; text-decoration:underline;">Limpiar</button>
                    </div>
                @endif
            </div>

            @if($servicios->isEmpty())
                <div style="padding:20px; text-align:center; background:var(--surface-2); border-radius:8px; color:#64748b; font-size:13px;">
                    No hay servicios en el catálogo.
                    <a href="{{ route('admin.catalogo.create') }}" style="color:#3b82f6; text-decoration:underline;">Agregar uno</a>.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:8px;">
                    @foreach($servicios as $servicio)
                        <label style="display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:var(--surface); cursor:pointer; font-size:13px; transition:background .15s;"
                               onmouseover="this.style.background='var(--surface-2)'"
                               onmouseout="this.style.background='var(--surface)'">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                                   @checked(in_array($servicio->id, old('servicios', [])))
                                   style="width:16px; height:16px; accent-color:var(--accent);">
                            <span>{{ $servicio->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <a href="{{ route('admin.personal-interno.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear interno y enviar acceso</button>
        </div>
    </form>
</x-app-layout>
