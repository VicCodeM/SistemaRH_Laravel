<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('tickets.index') }}">Tickets</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>#{{ $ticket->id }}</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">{{ $ticket->asunto }}</h1>
                <p class="page-subtitle">
                    {{ $ticket->empresa?->nombre_empresa ?? 'Empresa' }}
                    &middot; {{ \App\Models\Ticket::categoriaLabel($ticket->categoria) }}
                </p>
            </div>
            @if(auth()->user()->esAdmin() || auth()->user()->esInterno())
                <form method="POST" action="{{ route('tickets.estado', $ticket) }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    @csrf
                    @method('PATCH')
                    <select name="estado" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface);">
                        @foreach(\App\Models\Ticket::estados() as $key => $label)
                            <option value="{{ $key }}" {{ $ticket->estado === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" style="padding:8px 14px; background:var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:500;">Actualizar estado</button>
                </form>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">{{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">
        <div>
            <div class="card fade-in" style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:10px;">
                    <div>
                        <p style="font-weight:600; font-size:13px; margin:0;">{{ $ticket->empresa?->usuario?->name ?? 'Empresa' }}</p>
                        <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0;">Creado el {{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                    <span class="badge {{ \App\Models\Ticket::estadoBadgeClass($ticket->estado) }}">
                        {{ \App\Models\Ticket::estadoLabel($ticket->estado) }}
                    </span>
                </div>
                <p style="font-size:14px; line-height:1.6; margin:0; white-space:pre-wrap;">{{ $ticket->descripcion }}</p>
            </div>

            @foreach($ticket->mensajes as $mensaje)
                @php $esMio = $mensaje->user_id === auth()->id(); @endphp
                <div class="card fade-in" style="margin-bottom:12px; {{ $esMio ? 'border-left:3px solid var(--accent);' : '' }}">
                    <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:8px;">
                        <p style="font-weight:600; font-size:13px; margin:0;">
                            {{ $mensaje->user?->name ?? 'Usuario' }}
                            @if($mensaje->user?->esAdmin())
                                <span style="font-size:11px; background:var(--accent-light); color:var(--accent); padding:1px 6px; border-radius:10px; font-weight:400;">Administrador</span>
                            @elseif($mensaje->user?->esInterno())
                                <span style="font-size:11px; background:rgba(34,197,94,.12); color:#22c55e; padding:1px 6px; border-radius:10px; font-weight:400;">Interno</span>
                            @endif
                        </p>
                        <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ $mensaje->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                    <p style="font-size:14px; line-height:1.6; margin:0; white-space:pre-wrap;">{{ $mensaje->mensaje }}</p>
                </div>
            @endforeach

            @if(!in_array($ticket->estado, ['resuelto', 'cerrado'], true))
                <div class="card fade-in">
                    <form method="POST" action="{{ route('tickets.responder', $ticket) }}">
                        @csrf
                        <textarea name="mensaje" rows="4" placeholder="Escribe una respuesta clara..."
                            style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface-2); resize:vertical; margin-bottom:10px;">{{ old('mensaje') }}</textarea>
                        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-bottom:8px;">{{ $message }}</p> @enderror
                        <div style="text-align:right;">
                            <button type="submit" style="padding:10px 20px; background:var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:500;">Responder</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="card" style="text-align:center; padding:20px; background:var(--success-light);">
                    <p style="color:var(--success); font-weight:500; margin:0;">Ticket {{ \App\Models\Ticket::estadoLabel($ticket->estado) }} &mdash; {{ $ticket->resuelto_at?->format('d/m/Y H:i') ?? 'sin fecha' }}</p>
                </div>
            @endif
        </div>

        <div>
            <div class="card fade-in">
                <h4 style="font-weight:600; font-size:14px; margin:0 0 14px 0;">Información</h4>

                <div style="display:grid; gap:10px; font-size:13px;">
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Estado</p>
                        <span class="badge {{ \App\Models\Ticket::estadoBadgeClass($ticket->estado) }}">{{ \App\Models\Ticket::estadoLabel($ticket->estado) }}</span>
                    </div>
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Prioridad</p>
                        <span style="font-weight:500;">{{ \App\Models\Ticket::prioridadLabel($ticket->prioridad) }}</span>
                    </div>
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Tipo</p>
                        <span style="font-weight:500;">{{ \App\Models\Ticket::categoriaLabel($ticket->categoria) }}</span>
                    </div>
                    @if($ticket->sla_due_at)
                        <div>
                            <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">SLA</p>
                            <span style="font-weight:500; color:{{ $ticket->estaVencido() ? 'var(--danger)' : 'inherit' }};">
                                {{ $ticket->sla_due_at->format('d/m/Y H:i') }}
                                @if($ticket->estaVencido())
                                    &middot; Vencido
                                @endif
                            </span>
                        </div>
                    @endif
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Abierto</p>
                        <span>{{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    @if(auth()->user()->esAdmin() || auth()->user()->esInterno())
                        <div>
                            <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Asignado a</p>
                            <form method="POST" action="{{ route('tickets.asignar', $ticket) }}" style="display:flex; gap:6px; align-items:center;">
                                @csrf @method('PATCH')
                                <select name="asignado_a" style="flex:1; padding:6px 8px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:var(--surface);" onchange="this.form.submit()">
                                    <option value="">Sin asignar</option>
                                    @foreach(\App\Models\User::where('rol', 'interno')->where('estado', 'activo')->orderBy('name')->get() as $interno)
                                        <option value="{{ $interno->id }}" {{ $ticket->asignado_a == $interno->id ? 'selected' : '' }}>{{ $interno->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @elseif($ticket->asignado)
                        <div>
                            <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Asignado a</p>
                            <span style="font-weight:500;">{{ $ticket->asignado->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
