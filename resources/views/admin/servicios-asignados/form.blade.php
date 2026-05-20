<x-app-layout>
    @php
        $esEdicion = isset($tarea);
    @endphp
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.tareas.index') }}">Tareas</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>{{ $esEdicion ? 'Editar #' . $tarea->id : 'Asignar' }}</span>
        </nav>
        <h1 class="page-title">{{ $esEdicion ? 'Editar tarea' : 'Asignar servicio' }}</h1>
        <p class="page-subtitle">{{ $esEdicion ? 'Actualiza los datos de la tarea asignada.' : 'Crea una tarea concreta para que el equipo interno la tome y la cierre.' }}</p>
    </x-slot>

    <div class="card fade-in" style="max-width:880px;">
        <form method="POST" action="{{ $esEdicion ? route('admin.tareas.actualizar', $tarea) : route('admin.tareas.guardar') }}">
            @csrf
            @if($esEdicion)
                @method('PUT')
            @endif

            <div style="display:grid; gap:18px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Servicio <span style="color:var(--danger);">*</span></label>
                        <select name="servicio_id" required style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            <option value="">Seleccionar...</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}" {{ old('servicio_id', $esEdicion ? $tarea->servicio_id : '') == $servicio->id ? 'selected' : '' }}>
                                    {{ $servicio->nombre }} · {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico) }}
                                </option>
                            @endforeach
                        </select>
                        @error('servicio_id') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Responsable interno</label>
                        <select name="asignado_a" style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            <option value="">Pendiente de asignación</option>
                            @foreach($internos as $interno)
                                <option value="{{ $interno->id }}" {{ old('asignado_a', $esEdicion ? $tarea->asignado_a : '') == $interno->id ? 'selected' : '' }}>
                                    {{ $interno->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('asignado_a') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                        <p style="font-size:11px; color:var(--text-muted); margin-top:4px;">Si no asignas un responsable, el servicio quedará en estado <strong>Pendiente</strong>.</p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Beneficiario (objetivo) <span style="color:var(--danger);">*</span></label>
                        <select name="objetivo" required style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            <option value="">Seleccionar...</option>
                            <optgroup label="Empresas">
                                @foreach($empresas as $empresa)
                                    <option value="empresa:{{ $empresa->id }}" {{ old('objetivo', $objetivoActual ?? '') === 'empresa:' . $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nombre_empresa }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Candidatos">
                                @foreach($candidatos as $candidato)
                                    <option value="candidato:{{ $candidato->id }}" {{ old('objetivo', $objetivoActual ?? '') === 'candidato:' . $candidato->id ? 'selected' : '' }}>
                                        {{ $candidato->nombreCompleto() }} · {{ $candidato->usuario?->email }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Personal interno">
                                @foreach($internosObjetivo as $internoObj)
                                    <option value="interno:{{ $internoObj->id }}" {{ old('objetivo', $objetivoActual ?? '') === 'interno:' . $internoObj->id ? 'selected' : '' }}>
                                        {{ $internoObj->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('objetivo') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Estado <span style="color:var(--danger);">*</span></label>
                        <select name="estado" required style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            @foreach(\App\Models\ServicioAsignado::estados() as $key => $label)
                                <option value="{{ $key }}" {{ old('estado', $esEdicion ? $tarea->estado : 'pendiente') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Notas</label>
                    <textarea name="notas" rows="4" maxlength="5000"
                        placeholder="Describe lo que se debe hacer, alcance, observaciones o criterios de cierre..."
                        style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); resize:vertical;">{{ old('notas', $esEdicion ? $tarea->notas : '') }}</textarea>
                    @error('notas') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                @if($esEdicion)
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Fecha de inicio</label>
                            <input type="datetime-local" name="fecha_inicio"
                                value="{{ old('fecha_inicio', $tarea->fecha_inicio?->format('Y-m-d\TH:i') ?? '') }}"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            @error('fecha_inicio') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Fecha de fin</label>
                            <input type="datetime-local" name="fecha_fin"
                                value="{{ old('fecha_fin', $tarea->fecha_fin?->format('Y-m-d\TH:i') ?? '') }}"
                                style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface);">
                            @error('fecha_fin') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:500; margin-bottom:6px;">Resumen de cierre</label>
                        <textarea name="cierre_resumen" rows="3" maxlength="5000"
                            placeholder="Notas del cierre o resultado de la tarea..."
                            style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); resize:vertical;">{{ old('cierre_resumen', $tarea->cierre_resumen ?? '') }}</textarea>
                        @error('cierre_resumen') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div style="display:flex; align-items:flex-end;">
                        <div style="padding:12px 14px; background:var(--surface-2); border:1px solid var(--border); border-radius:8px; font-size:13px; color:var(--text-muted);">
                            Si no asignas un responsable, la tarea se creará en estado <strong>Pendiente</strong>. Luego podrás asignarla desde el <a href="{{ route('admin.tareas.kanban') }}" style="color:var(--accent);">tablero Kanban</a>.
                        </div>
                    </div>
                @endif

                <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:8px; border-top:1px solid var(--border);">
                    <a href="{{ route('admin.tareas.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">{{ $esEdicion ? 'Guardar cambios' : 'Registrar servicio' }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
