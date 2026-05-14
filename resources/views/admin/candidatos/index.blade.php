<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <span>Candidatos</span>
        </nav>
        <h1 class="page-title">Gestión de Candidatos</h1>
        <p class="page-subtitle">{{ $candidatos->total() }} candidato(s) registrado(s).</p>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background: var(--success-light); color: var(--success); border-radius:8px; border-left:4px solid var(--success);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:16px; padding:12px 16px; background: var(--danger-light); color: var(--danger); border-radius:8px; border-left:4px solid var(--danger);">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card fade-in" style="margin-bottom:20px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o CURP..."
                    style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background: var(--surface);">
            </div>
            <div>
                <label style="font-size:12px; color:var(--text-muted); display:block; margin-bottom:4px;">Estado</label>
                <select name="estado" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background: var(--surface);">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="enviada" {{ request('estado') === 'enviada' ? 'selected' : '' }}>Enviada</option>
                    <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <button type="submit" style="padding:8px 16px; background: var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px;">Filtrar</button>
            @if(request()->hasAny(['buscar', 'estado']))
                <a href="{{ route('admin.candidatos') }}" style="padding:8px 16px; border:1px solid var(--border); border-radius:8px; font-size:14px; text-decoration:none; color: var(--text-muted);">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card fade-in">
        @if($candidatos->isEmpty())
            <p class="text-muted text-sm" style="text-align:center; padding:40px 0;">No hay candidatos que coincidan.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border);">
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Candidato</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Contacto</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">CURP</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Solicitud</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Enviada</th>
                            <th style="text-align:right; padding:10px 12px; color:var(--text-muted); font-weight:500;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidatos as $candidato)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding:12px;">
                                    <p style="font-weight:500; margin:0;">{{ $candidato->nombre }} {{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }}</p>
                                    <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ $candidato->puesto_deseado ?? 'Sin puesto indicado' }}</p>
                                </td>
                                <td style="padding:12px;">
                                    <p style="margin:0; font-size:13px;">{{ $candidato->usuario?->email }}</p>
                                    <p style="margin:0; font-size:12px; color:var(--text-muted);">{{ $candidato->celular ?? $candidato->telefono ?? '—' }}</p>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">{{ $candidato->curp ?? '—' }}</td>
                                <td style="padding:12px;">
                                    @php
                                        $colors = ['borrador'=>'var(--text-muted)','enviada'=>'var(--warning)','aprobada'=>'var(--success)','rechazada'=>'var(--danger)','en_revision'=>'var(--accent)'];
                                        $bgs    = ['borrador'=>'var(--surface-2)','enviada'=>'var(--warning-light)','aprobada'=>'var(--success-light)','rechazada'=>'var(--danger-light)','en_revision'=>'var(--accent-light)'];
                                        $estado = $candidato->solicitud_estado ?? 'borrador';
                                    @endphp
                                    <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $bgs[$estado] ?? 'var(--surface-2)' }}; color:{{ $colors[$estado] ?? 'var(--text-muted)' }};">
                                        {{ ucfirst($estado) }}
                                    </span>
                                </td>
                                <td style="padding:12px; color:var(--text-muted); font-size:13px;">
                                    {{ $candidato->solicitud_enviada_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td style="padding:12px; text-align:right;">
                                    <div style="display:flex; gap:6px; justify-content:flex-end; align-items:center;">
                                        <button onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')"
                                                title="Ver solicitud"
                                                style="width:30px;height:30px;background:#1e293b;border:1px solid #334155;border-radius:7px;cursor:pointer;color:#94a3b8;display:flex;align-items:center;justify-content:center;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        @if($candidato->solicitud_estado === 'enviada')
                                            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" style="padding:5px 12px; background: var(--success); color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:500;">Aprobar</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.candidatos.rechazar', $candidato) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" style="padding:5px 12px; background: var(--danger); color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:500;">Rechazar</button>
                                            </form>
                                        @elseif($candidato->solicitud_estado === 'rechazada')
                                            <form method="POST" action="{{ route('admin.candidatos.aprobar', $candidato) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" style="padding:5px 12px; background: var(--success); color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:500;">Reactivar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">
                {{ $candidatos->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
