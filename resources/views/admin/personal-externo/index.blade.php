<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">›</span>
            <span>Personal Externo</span>
        </nav>
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 class="page-title">Personal Externo</h1>
                <p class="page-subtitle">Consultores, capacitadores y especialistas disponibles.</p>
            </div>
            <a href="{{ route('admin.personal-externo.create') }}" class="btn btn-primary">+ Agregar persona</a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <form method="GET" style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" name="buscar" class="form-input" placeholder="Nombre, email o empresa..." value="{{ request('buscar') }}" style="width: 220px;">
        <select name="especialidad" class="form-input" style="width: auto;" onchange="this.form.submit()">
            <option value="">Todas las especialidades</option>
            @foreach (\App\Models\CatalogoServicio::tipos() as $key => $label)
                <option value="{{ $key }}" {{ request('especialidad') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="disponibilidad" class="form-input" style="width: auto;" onchange="this.form.submit()">
            <option value="">Cualquier disponibilidad</option>
            @foreach (\App\Models\PersonalExterno::disponibilidades() as $key => $label)
                <option value="{{ $key }}" {{ request('disponibilidad') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Buscar</button>
        @if (request()->hasAny(['buscar', 'especialidad', 'disponibilidad']))
            <a href="{{ route('admin.personal-externo.index') }}" class="btn btn-secondary">Limpiar</a>
        @endif
    </form>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Especialidad</th>
                    <th>Niveles que cubre</th>
                    <th>Empresa / Razón social</th>
                    <th>Contacto</th>
                    <th>Disponibilidad</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($personas as $persona)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--text);">{{ $persona->nombre }} {{ $persona->apellidos }}</div>
                            @if ($persona->cv_path)
                                <a href="{{ Storage::url($persona->cv_path) }}" target="_blank" style="font-size: 0.75rem; color: #60a5fa;">Ver CV</a>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-blue">
                                {{ \App\Models\CatalogoServicio::tipos()[$persona->especialidad] ?? $persona->especialidad }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; color: #94a3b8; max-width: 160px;">
                            {{ collect($persona->niveles_jerarquicos)->map(fn ($n) => \App\Models\CatalogoServicio::nivelJerarquicoLabel($n))->implode(', ') }}
                        </td>
                        <td style="font-size: 0.85rem; color: #94a3b8;">{{ $persona->empresa_o_razon_social ?? '—' }}</td>
                        <td style="font-size: 0.82rem;">
                            <div>{{ $persona->email }}</div>
                            @if ($persona->telefono)
                                <div style="color: #64748b;">{{ $persona->telefono }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ \App\Models\PersonalExterno::disponibilidadBadgeClass($persona->disponibilidad) }}">
                                {{ \App\Models\PersonalExterno::disponibilidadLabel($persona->disponibilidad) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <div style="display:flex;gap:6px;align-items:center;">
                                <button onclick="rhModal('{{ route('admin.personal-externo.modal', $persona) }}')"
                                        title="Ver detalle"
                                        class="btn btn-ghost" style="width:30px;height:30px;padding:0;">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                                <a href="{{ route('admin.personal-externo.edit', $persona) }}" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Editar</a>
                                <form method="POST" action="{{ route('admin.personal-externo.destroy', $persona) }}" style="display: inline;" onsubmit="return confirm('¿Eliminar este registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px; color: #475569;">
                            No hay personal externo registrado.
                            <a href="{{ route('admin.personal-externo.create') }}" style="color: var(--accent);">Agregar el primero</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $personas->links() }}
    </div>
</x-app-layout>
