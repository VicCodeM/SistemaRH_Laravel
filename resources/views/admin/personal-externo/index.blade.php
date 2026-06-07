<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administracion</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Personal Externo</span>
        </nav>
        <div class="candidate-inline-meta">
            <div>
                <h1 class="page-title">Personal Externo</h1>
                <p class="page-subtitle">Consultores, capacitadores y especialistas disponibles.</p>
            </div>
            <a href="{{ route('admin.personal-externo.create') }}" class="btn btn-primary">+ Agregar persona</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="form-inline" style="margin-bottom:20px;">
        <input type="text" name="buscar" class="form-input" placeholder="Nombre, email o empresa..." value="{{ request('buscar') }}" style="min-width:220px;" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
        <select name="especialidad" class="form-input" onchange="this.form.submit()">
            <option value="">Todas las especialidades</option>
            @foreach (\App\Models\CatalogoServicio::tipos() as $key => $label)
                <option value="{{ $key }}" {{ request('especialidad') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="disponibilidad" class="form-input" onchange="this.form.submit()">
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
        @if($personas->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                No hay personal externo registrado.
                <a href="{{ route('admin.personal-externo.create') }}" style="color: var(--accent);">Agregar el primero</a>
            </div>
        @else
            <div class="desktop-only table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Niveles que cubre</th>
                            <th>Empresa / Razon social</th>
                            <th>Contacto</th>
                            <th>Disponibilidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personas as $persona)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:var(--text);">{{ $persona->nombre }} {{ $persona->apellidos }}</div>
                                    @if ($persona->cv_path)
                                        <a href="{{ Storage::url($persona->cv_path) }}" target="_blank" style="font-size:0.75rem; color:#60a5fa;">Ver CV</a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-blue">
                                        {{ \App\Models\CatalogoServicio::tipos()[$persona->especialidad] ?? $persona->especialidad }}
                                    </span>
                                </td>
                                <td style="font-size:0.8rem; color:#94a3b8; max-width:160px;">
                                    {{ collect($persona->niveles_jerarquicos)->map(fn ($n) => \App\Models\CatalogoServicio::nivelJerarquicoLabel($n))->implode(', ') }}
                                </td>
                                <td style="font-size:0.85rem; color:#94a3b8;">{{ $persona->empresa_o_razon_social ?? '—' }}</td>
                                <td style="font-size:0.82rem;">
                                    <div>{{ $persona->email }}</div>
                                    @if ($persona->telefono)
                                        <div style="color:#64748b;">{{ $persona->telefono }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ \App\Models\PersonalExterno::disponibilidadBadgeClass($persona->disponibilidad) }}">
                                        {{ \App\Models\PersonalExterno::disponibilidadLabel($persona->disponibilidad) }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <div class="toolbar-wrap">
                                        <button type="button" onclick="rhModal('{{ route('admin.personal-externo.modal', $persona) }}')" title="Ver detalle" class="btn btn-ghost" style="width:30px;height:30px;padding:0;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        <a href="{{ route('admin.personal-externo.edit', $persona) }}" class="btn btn-secondary btn-sm">Editar</a>
                                        <button type="button" onclick="rhModal('{{ route('admin.personal-externo.accion.modal', [$persona, 'eliminar']) }}')" class="btn btn-danger btn-sm">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-only">
                <div class="candidate-mobile-list">
                    @foreach ($personas as $persona)
                        <article class="candidate-mobile-card">
                            <div class="candidate-inline-meta">
                                <div>
                                    <h3 class="candidate-mobile-card-title">{{ $persona->nombre }} {{ $persona->apellidos }}</h3>
                                    <p class="candidate-mobile-card-subtitle">{{ \App\Models\CatalogoServicio::tipos()[$persona->especialidad] ?? $persona->especialidad }}</p>
                                </div>
                                <span class="badge {{ \App\Models\PersonalExterno::disponibilidadBadgeClass($persona->disponibilidad) }}">
                                    {{ \App\Models\PersonalExterno::disponibilidadLabel($persona->disponibilidad) }}
                                </span>
                            </div>

                            <div class="candidate-mobile-meta">
                                <div>
                                    <p class="candidate-mobile-meta-label">Empresa</p>
                                    <p class="candidate-mobile-meta-value">{{ $persona->empresa_o_razon_social ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Contacto</p>
                                    <p class="candidate-mobile-meta-value">{{ $persona->email }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Telefono</p>
                                    <p class="candidate-mobile-meta-value">{{ $persona->telefono ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="candidate-mobile-meta-label">Niveles</p>
                                    <p class="candidate-mobile-meta-value">{{ collect($persona->niveles_jerarquicos)->map(fn ($n) => \App\Models\CatalogoServicio::nivelJerarquicoLabel($n))->implode(', ') }}</p>
                                </div>
                            </div>

                            <div class="candidate-actions" style="margin-top:14px;">
                                <button type="button" onclick="rhModal('{{ route('admin.personal-externo.modal', $persona) }}')" class="btn btn-secondary btn-sm">Ver</button>
                                <a href="{{ route('admin.personal-externo.edit', $persona) }}" class="btn btn-secondary btn-sm">Editar</a>
                                @if ($persona->cv_path)
                                    <a href="{{ Storage::url($persona->cv_path) }}" target="_blank" class="btn btn-secondary btn-sm">CV</a>
                                @endif
                                <button type="button" onclick="rhModal('{{ route('admin.personal-externo.accion.modal', [$persona, 'eliminar']) }}')" class="btn btn-danger btn-sm">Eliminar</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:20px;">
                {{ $personas->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
