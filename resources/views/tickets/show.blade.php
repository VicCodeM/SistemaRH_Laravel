<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('tickets.index') }}">Tickets</a>
            <span class="breadcrumb-sep">›</span>
            <span>#{{ $ticket->id }}</span>
        </nav>
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h1 class="page-title">{{ $ticket->asunto }}</h1>
                <p class="page-subtitle">Empresa: {{ $ticket->empresa?->nombre_empresa }} · Categoría: {{ ucfirst(str_replace('_', ' ', $ticket->categoria)) }}</p>
            </div>
            @if(auth()->user()->esAdmin())
                <form method="POST" action="{{ route('tickets.estado', $ticket) }}" style="display:flex; gap:8px; align-items:center;">
                    @csrf @method('PATCH')
                    <select name="estado" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface);">
                        @foreach(['abierto','en_proceso','resuelto','cerrado'] as $e)
                            <option value="{{ $e }}" {{ $ticket->estado === $e ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $e)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" style="padding:8px 14px; background: var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:500;">Actualizar</button>
                </form>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background: var(--success-light); color: var(--success); border-radius:8px; border-left:4px solid var(--success);">{{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns: 1fr 280px; gap:20px; align-items:start;">

        {{-- Hilo de mensajes --}}
        <div>
            {{-- Descripción inicial --}}
            <div class="card fade-in" style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <p style="font-weight:600; font-size:13px; margin:0;">{{ $ticket->empresa?->usuario?->name ?? 'Empresa' }}</p>
                    <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
                <p style="font-size:14px; line-height:1.6; margin:0; white-space:pre-wrap;">{{ $ticket->descripcion }}</p>
            </div>

            {{-- Respuestas --}}
            @foreach($ticket->mensajes as $msg)
                @php $esMio = $msg->user_id === auth()->id(); @endphp
                <div class="card fade-in" style="margin-bottom:12px; {{ $esMio ? 'border-left: 3px solid var(--accent);' : '' }}">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <p style="font-weight:600; font-size:13px; margin:0;">
                            {{ $msg->user?->name ?? 'Usuario' }}
                            @if($msg->user?->esAdmin())
                                <span style="font-size:11px; background: var(--accent-light); color: var(--accent); padding:1px 6px; border-radius:10px; font-weight:400;">Admin</span>
                            @endif
                        </p>
                        <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ $msg->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                    <p style="font-size:14px; line-height:1.6; margin:0; white-space:pre-wrap;">{{ $msg->mensaje }}</p>
                </div>
            @endforeach

            {{-- Formulario respuesta --}}
            @if(!in_array($ticket->estado, ['resuelto','cerrado']))
                <div class="card fade-in">
                    <form method="POST" action="{{ route('tickets.responder', $ticket) }}">
                        @csrf
                        <textarea name="mensaje" rows="4" placeholder="Escribe tu respuesta..."
                            style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface-2); resize:vertical; margin-bottom:10px;">{{ old('mensaje') }}</textarea>
                        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-bottom:8px;">{{ $message }}</p> @enderror
                        <div style="text-align:right;">
                            <button type="submit" style="padding:10px 20px; background: var(--accent); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:500;">Responder</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="card" style="text-align:center; padding:20px; background: var(--success-light);">
                    <p style="color: var(--success); font-weight:500; margin:0;">✓ Ticket resuelto — {{ $ticket->resuelto_at?->format('d/m/Y H:i') }}</p>
                </div>
            @endif
        </div>

        {{-- Info lateral --}}
        <div>
            <div class="card fade-in">
                <h4 style="font-weight:600; font-size:14px; margin:0 0 14px 0;">Información</h4>

                <div style="display:grid; gap:10px; font-size:13px;">
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Estado</p>
                        @php $eColores = ['abierto'=>'var(--warning)','en_proceso'=>'var(--accent)','resuelto'=>'var(--success)','cerrado'=>'var(--text-muted)']; @endphp
                        <span style="font-weight:500; color:{{ $eColores[$ticket->estado] ?? 'inherit' }};">{{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}</span>
                    </div>
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Prioridad</p>
                        <span style="font-weight:500;">{{ ucfirst($ticket->prioridad) }}</span>
                    </div>
                    @if($ticket->sla_due_at)
                        <div>
                            <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">SLA</p>
                            @php $vencido = $ticket->sla_due_at->isPast() && !in_array($ticket->estado, ['resuelto','cerrado']); @endphp
                            <span style="font-weight:500; color:{{ $vencido ? 'var(--danger)' : 'inherit' }};">
                                {{ $ticket->sla_due_at->format('d/m/Y H:i') }}
                                @if($vencido) ⚠ Vencido @endif
                            </span>
                        </div>
                    @endif
                    <div>
                        <p style="color:var(--text-muted); margin:0 0 2px; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Abierto</p>
                        <span>{{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    @if($ticket->asignado)
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
