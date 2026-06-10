<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <span>Catálogo de Servicios</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 class="page-title">Catálogo de Servicios</h1>
                <p class="page-subtitle">Servicios que ofrece la consultora y que el personal interno puede tener como especialidades.</p>
            </div>
            <a href="{{ route('admin.catalogo.create') }}" class="btn btn-primary">+ Agregar servicio</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
        <select name="tipo" class="form-input" style="width:auto;" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            @foreach(\App\Models\CatalogoServicio::tipos() as $key => $label)
                <option value="{{ $key }}" {{ request('tipo') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="nivel" class="form-input" style="width:auto;" onchange="this.form.submit()">
            <option value="">Todos los niveles</option>
            @foreach(\App\Models\CatalogoServicio::nivelesJerarquicos() as $key => $label)
                <option value="{{ $key }}" {{ request('nivel') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="para_quien" class="form-input" style="width:auto;" onchange="this.form.submit()">
            <option value="">Para todos</option>
            <option value="empresa"   {{ request('para_quien') === 'empresa'   ? 'selected' : '' }}>Solo empresas</option>
            <option value="candidato" {{ request('para_quien') === 'candidato' ? 'selected' : '' }}>Solo candidatos</option>
            <option value="ambos"     {{ request('para_quien') === 'ambos'     ? 'selected' : '' }}>Ambos</option>
        </select>
        @if(request()->hasAny(['tipo','nivel','para_quien']))
            <a href="{{ route('admin.catalogo.index') }}" class="btn btn-secondary">Limpiar</a>
        @endif
    </form>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Tipo</th>
                    <th>Nivel jerárquico</th>
                    <th>Para quién</th>
                    <th>Orden</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicios as $servicio)
                    @php $bloqueado = $servicio->activo && ! $servicio->puedeDesactivarse(); @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600; color:var(--text);">{{ $servicio->nombre }}</div>
                            @if($servicio->descripcion)
                                <div style="font-size:0.78rem; color:#64748b; margin-top:2px;">{{ Str::limit($servicio->descripcion, 80) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size:0.75rem;">
                                {{ \App\Models\CatalogoServicio::tipos()[$servicio->tipo] ?? $servicio->tipo }}
                            </span>
                        </td>
                        <td style="font-size:0.83rem; color:#94a3b8;">
                            {{ \App\Models\CatalogoServicio::nivelJerarquicoLabel($servicio->nivel_jerarquico) }}
                        </td>
                        <td style="font-size:0.8rem; color:#94a3b8;">
                            @php $pq = ['empresa'=>'Empresa','candidato'=>'Candidato','ambos'=>'Ambos']; @endphp
                            {{ $pq[$servicio->para_quien] ?? $servicio->para_quien }}
                        </td>
                        <td style="text-align:center; font-size:0.85rem; color:#64748b;">{{ $servicio->orden ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.catalogo.toggle', $servicio) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        @disabled($bloqueado)
                                        style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; border:none;
                                               background:{{ $servicio->activo ? 'var(--success-light)' : 'var(--surface-2)' }};
                                               color:{{ $servicio->activo ? 'var(--success)' : 'var(--text-muted)' }};
                                               opacity:{{ $bloqueado ? '.6' : '1' }};
                                               cursor:{{ $bloqueado ? 'not-allowed' : 'pointer' }};"
                                        title="{{ $bloqueado ? 'No se puede desactivar: tiene pedidos activos o en proceso' : ($servicio->activo ? 'Clic para desactivar' : 'Clic para activar') }}">
                                    {{ $servicio->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </form>
                        </td>
                        <td style="white-space:nowrap;">
                            <div style="display:flex; gap:6px;">
                                <a href="{{ route('admin.catalogo.edit', $servicio) }}" class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem;">Editar</a>
                                <form method="POST" action="{{ route('admin.catalogo.destroy', $servicio) }}" onsubmit="return confirm('¿Eliminar este servicio del catálogo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding:4px 10px; font-size:0.8rem;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:48px; color:#475569;">
                            No hay servicios en el catálogo.
                            <a href="{{ route('admin.catalogo.create') }}" style="color:var(--accent);">Agregar el primero</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $servicios->links() }}
    </div>
</x-app-layout>
