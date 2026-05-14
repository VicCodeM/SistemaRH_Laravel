<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <span>Empresas</span>
        </nav>
        <h1 class="page-title">Gestión de Empresas</h1>
        <p class="page-subtitle">{{ $empresas->total() }} empresa(s) registrada(s).</p>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger);">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs de estado --}}
    @php
        $totalPendientes = \App\Models\Empresa::where('estado','pendiente')->count();
        $totalActivas    = \App\Models\Empresa::where('estado','activa')->count();
        $totalOtras      = \App\Models\Empresa::whereIn('estado',['rechazada','suspendida'])->count();
        $estadoActual    = request('estado','');
    @endphp
    <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
        <a href="{{ route('admin.empresas') }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === '' ? 'var(--accent)' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === '' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === '' ? 'var(--accent)' : 'var(--border)' }};">
            Todas
        </a>
        <a href="{{ route('admin.empresas', ['estado'=>'pendiente']) }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === 'pendiente' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'pendiente' ? '#f59e0b' : 'var(--border)' }};">
            Pendientes
            @if($totalPendientes > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'pendiente' ? 'rgba(255,255,255,0.3)' : '#f59e0b' }}; color:{{ $estadoActual === 'pendiente' ? '#fff' : '#fff' }}; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalPendientes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.empresas', ['estado'=>'activa']) }}"
           style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                  background:{{ $estadoActual === 'activa' ? '#22c55e' : 'var(--surface-2)' }};
                  color:{{ $estadoActual === 'activa' ? '#fff' : 'var(--text-muted)' }};
                  border:1px solid {{ $estadoActual === 'activa' ? '#22c55e' : 'var(--border)' }};">
            Activas
            @if($totalActivas > 0)
                <span style="margin-left:6px; background:{{ $estadoActual === 'activa' ? 'rgba(255,255,255,0.3)' : '#22c55e' }}; color:#fff; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalActivas }}</span>
            @endif
        </a>
        @if($totalOtras > 0)
            <a href="{{ route('admin.empresas', ['estado'=>'rechazada']) }}"
               style="padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
                      background:{{ in_array($estadoActual,['rechazada','suspendida']) ? '#ef4444' : 'var(--surface-2)' }};
                      color:{{ in_array($estadoActual,['rechazada','suspendida']) ? '#fff' : 'var(--text-muted)' }};
                      border:1px solid {{ in_array($estadoActual,['rechazada','suspendida']) ? '#ef4444' : 'var(--border)' }};">
                Inactivas
                <span style="margin-left:6px; background:rgba(239,68,68,0.15); color:#ef4444; border-radius:20px; padding:1px 7px; font-size:11px;">{{ $totalOtras }}</span>
            </a>
        @endif

        {{-- Búsqueda --}}
        <form method="GET" style="margin-left:auto; display:flex; gap:8px;">
            @if($estadoActual) <input type="hidden" name="estado" value="{{ $estadoActual }}"> @endif
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o RFC..."
                   style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); width:200px;">
            <button type="submit" class="btn btn-secondary" style="padding:8px 14px; font-size:13px;">Buscar</button>
            @if(request('buscar'))
                <a href="{{ route('admin.empresas', $estadoActual ? ['estado'=>$estadoActual] : []) }}" class="btn btn-secondary" style="padding:8px 12px; font-size:13px;">✕</a>
            @endif
        </form>
    </div>

    {{-- Tabla --}}
    <div class="table-wrapper">
        @if($empresas->isEmpty())
            <div style="text-align:center; padding:48px; color:#475569;">
                @if($estadoActual === 'pendiente')
                    No hay empresas pendientes de aprobación. ¡Todo al día!
                @elseif($estadoActual === 'activa')
                    No hay empresas activas aún.
                @else
                    No hay empresas que coincidan.
                @endif
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>RFC</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empresas as $empresa)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--text);">{{ $empresa->nombre_empresa }}</div>
                                <div style="font-size:12px; color:#64748b;">{{ $empresa->ciudad }}</div>
                            </td>
                            <td style="color:#94a3b8; font-size:0.85rem;">{{ $empresa->rfc ?? '—' }}</td>
                            <td>
                                <div style="font-size:13px;">{{ $empresa->usuario?->email }}</div>
                                <div style="font-size:12px; color:#64748b;">{{ $empresa->telefono }}</div>
                            </td>
                            <td>
                                @php
                                    $colors = ['pendiente'=>'warning','activa'=>'success','rechazada'=>'danger','suspendida'=>'danger'];
                                    $labels = ['pendiente'=>'Pendiente','activa'=>'Activa','rechazada'=>'Rechazada','suspendida'=>'Suspendida'];
                                @endphp
                                <span class="badge badge-{{ $colors[$empresa->estado] ?? 'secondary' }}">
                                    {{ $labels[$empresa->estado] ?? ucfirst($empresa->estado) }}
                                </span>
                            </td>
                            <td style="font-size:13px; color:#64748b;">{{ $empresa->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td style="white-space:nowrap;">
                                <div style="display:flex; gap:6px; align-items:center; justify-content:flex-end;">
                                    <button onclick="rhModal('{{ route('admin.empresas.modal', $empresa) }}')"
                                            title="Ver detalle"
                                            style="width:30px;height:30px;background:#1e293b;border:1px solid #334155;border-radius:7px;cursor:pointer;color:#94a3b8;display:flex;align-items:center;justify-content:center;">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    @if($empresa->estado === 'pendiente')
                                        <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:5px 12px; font-size:12px;">Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.empresas.rechazar', $empresa) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-danger" style="padding:5px 12px; font-size:12px;">Rechazar</button>
                                        </form>
                                    @elseif(in_array($empresa->estado, ['rechazada','suspendida']))
                                        <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success" style="padding:5px 12px; font-size:12px;">Reactivar</button>
                                        </form>
                                    @elseif($empresa->estado === 'activa')
                                        <form method="POST" action="{{ route('admin.empresas.suspender', $empresa) }}" onsubmit="return confirm('¿Suspender esta empresa?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">Suspender</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">
                {{ $empresas->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
