<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.vacantes') }}">Solicitudes</a>
            <span class="breadcrumb-sep">›</span>
            <span>Editar</span>
        </nav>
        <h1 class="page-title">Editar solicitud</h1>
        <p class="page-subtitle" style="color:#94a3b8;">{{ $vacante->empresa?->nombre_empresa ?? '' }}</p>
    </x-slot>

    <div style="display:grid; grid-template-columns:1fr 280px; gap:20px; align-items:start; max-width:960px;">

        {{-- Formulario --}}
        <div class="card">
            <form method="POST" action="{{ route('admin.vacantes.actualizar', $vacante) }}">
                @csrf @method('PUT')

                {{-- Info empresa --}}
                <div style="padding:10px 14px; background:var(--surface-2); border-radius:8px; border:1px solid var(--border); margin-bottom:22px; font-size:0.83rem; color:#94a3b8;">
                    Empresa: <span style="color:var(--text); font-weight:600;">{{ $vacante->empresa?->nombre_empresa ?? '—' }}</span>
                    &nbsp;·&nbsp; Enviada: <span style="color:var(--text);">{{ $vacante->fecha_publicacion?->format('d/m/Y') ?? '—' }}</span>
                </div>

                {{-- Tipo de servicio --}}
                <div class="form-group">
                    <label class="form-label">Tipo de servicio <span style="color:var(--danger)">*</span></label>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:7px; margin-top:8px;">
                        @foreach($tipos as $key => $label)
                            <label style="display:flex; align-items:center; gap:8px; padding:9px 11px; border:1px solid {{ old('tipo_servicio', $vacante->tipo_servicio) === $key ? 'var(--accent)' : 'var(--border)' }}; border-radius:8px; cursor:pointer; background:{{ old('tipo_servicio', $vacante->tipo_servicio) === $key ? 'rgba(59,130,246,0.07)' : 'var(--surface-2)' }};">
                                <input type="radio" name="tipo_servicio" value="{{ $key }}"
                                       {{ old('tipo_servicio', $vacante->tipo_servicio) === $key ? 'checked' : '' }}
                                       style="accent-color:var(--accent);" onchange="toggleReclutamiento(this.value)">
                                <span style="font-size:0.82rem; font-weight:500;">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('tipo_servicio')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Título --}}
                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="titulo">Título / Puesto <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input @error('titulo') is-invalid @enderror"
                           value="{{ old('titulo', $vacante->titulo) }}" maxlength="200">
                    @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Nivel --}}
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="nivel_jerarquico">Nivel jerárquico <span style="color:var(--danger)">*</span></label>
                    <select id="nivel_jerarquico" name="nivel_jerarquico" class="form-input @error('nivel_jerarquico') is-invalid @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach($niveles as $key => $label)
                            <option value="{{ $key }}" {{ old('nivel_jerarquico', $vacante->nivel_jerarquico) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('nivel_jerarquico')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Requerimientos del cliente --}}
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label" for="requerimientos">Requerimientos del cliente</label>
                    <textarea id="requerimientos" name="requerimientos"
                              class="form-input @error('requerimientos') is-invalid @enderror"
                              rows="3" maxlength="2000"
                              placeholder="Lo que el cliente describió al solicitar el servicio…">{{ old('requerimientos', $vacante->requerimientos) }}</textarea>
                    @error('requerimientos')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Campos de reclutamiento --}}
                <div id="campos-reclutamiento" style="{{ old('tipo_servicio', $vacante->tipo_servicio) !== 'reclutamiento' ? 'display:none;' : '' }}">
                    <div style="margin-top:20px; padding-top:16px; border-top:1px dashed var(--border);">
                        <div style="font-size:0.78rem; color:#60a5fa; font-weight:600; letter-spacing:.05em; text-transform:uppercase; margin-bottom:12px;">
                            Detalles del puesto (Reclutamiento)
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="descripcion">Descripción del puesto</label>
                            <textarea id="descripcion" name="descripcion"
                                      class="form-input @error('descripcion') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Responsabilidades, actividades, perfil buscado…">{{ old('descripcion', $vacante->descripcion) }}</textarea>
                            @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:14px;">
                            <div class="form-group">
                                <label class="form-label" for="salario_min">Salario mínimo ($)</label>
                                <input type="number" id="salario_min" name="salario_min" class="form-input"
                                       value="{{ old('salario_min', $vacante->salario_min) }}" min="0" step="500" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="salario_max">Salario máximo ($)</label>
                                <input type="number" id="salario_max" name="salario_max" class="form-input"
                                       value="{{ old('salario_max', $vacante->salario_max) }}" min="0" step="500" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:14px;">
                            <label class="form-label" for="ubicacion">Ubicación</label>
                            <input type="text" id="ubicacion" name="ubicacion" class="form-input"
                                   value="{{ old('ubicacion', $vacante->ubicacion) }}" placeholder="Ciudad, Estado o Remoto">
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; padding-top:18px; border-top:1px solid var(--border);">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.vacantes') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        {{-- Sidebar: estado + acceso rápido --}}
        <div>
            <div class="card">
                <h3 style="font-weight:600; font-size:0.9rem; margin:0 0 14px;">Estado de la solicitud</h3>
                <div style="margin-bottom:14px;">
                    @php $ec = ['pendiente'=>'#f59e0b','activa'=>'#22c55e','cerrada'=>'#64748b']; $el = ['pendiente'=>'Por revisar','activa'=>'Activa','cerrada'=>'Cerrada']; @endphp
                    <span style="padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:{{ $ec[$vacante->estado] ?? '#64748b' }}22; color:{{ $ec[$vacante->estado] ?? '#64748b' }};">
                        {{ $el[$vacante->estado] ?? ucfirst($vacante->estado) }}
                    </span>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    @if($vacante->estado !== 'activa')
                        <form method="POST" action="{{ route('admin.vacantes.activar', $vacante) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success" style="width:100%; font-size:0.85rem;">Activar solicitud</button>
                        </form>
                    @endif
                    @if($vacante->estado !== 'cerrada')
                        <form method="POST" action="{{ route('admin.vacantes.cerrar', $vacante) }}" onsubmit="return confirm('¿Cerrar esta solicitud?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-secondary" style="width:100%; font-size:0.85rem;">Cerrar solicitud</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.vacantes.matching', $vacante) }}"
                       style="display:block; text-align:center; padding:8px; border:1px solid #1e3a5f; color:#60a5fa; background:transparent; border-radius:8px; font-size:0.85rem; text-decoration:none;">
                        Gestionar candidatos →
                    </a>
                </div>
            </div>

            @if($vacante->postulaciones->count() > 0 ?? false)
            <div class="card" style="margin-top:12px;">
                <h3 style="font-weight:600; font-size:0.9rem; margin:0 0 10px;">Candidatos en proceso</h3>
                <div style="font-size:1.6rem; font-weight:700; color:var(--accent);">{{ $vacante->postulaciones->whereNotIn('estado',['retirado','rechazado'])->count() ?? 0 }}</div>
                <div style="font-size:0.8rem; color:#64748b;">activos en esta solicitud</div>
            </div>
            @endif
        </div>
    </div>

    <script>
    function toggleReclutamiento(tipo) {
        document.getElementById('campos-reclutamiento').style.display = tipo === 'reclutamiento' ? '' : 'none';
    }
    </script>
</x-app-layout>
