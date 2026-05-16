<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">Tickets de soporte</h1>
                <p class="page-subtitle">
                    @if(auth()->user()->esEmpresa())
                        {{ $tickets->total() }} ticket(s) registrados.
                    @else
                        {{ $tickets->total() }} ticket(s) en cola de atención.
                    @endif
                </p>
            </div>
            @if(auth()->user()->esEmpresa())
                <a href="{{ route('tickets.crear') }}" style="padding:10px 18px; background:var(--accent); color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:500;">+ Nuevo ticket</a>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background:var(--success-light); color:var(--success); border-radius:8px; border-left:4px solid var(--success);">{{ session('success') }}</div>
    @endif

    @php
        $muestraEmpresa = auth()->user()->esAdmin() || auth()->user()->esInterno();
    @endphp

    <div class="card fade-in">
        @if($tickets->isEmpty())
            <p class="text-muted text-sm" style="text-align:center; padding:40px;">Sin tickets registrados.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Asunto</th>
                            @if($muestraEmpresa)
                                <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Empresa</th>
                            @endif
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Prioridad</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Estado</th>
                            <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">SLA</th>
                            <th style="text-align:right; padding:10px 12px; color:var(--text-muted); font-weight:500;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr style="border-bottom:1px solid var(--border); {{ $ticket->estaVencido() ? 'background: rgba(220,38,38,.05);' : '' }}">
                                <td style="padding:12px;">
                                    <p style="font-weight:600; margin:0;">{{ $ticket->asunto }}</p>
                                    <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ \App\Models\Ticket::categoriaLabel($ticket->categoria) }}</p>
                                </td>
                                @if($muestraEmpresa)
                                    <td style="padding:12px; font-size:13px;">{{ $ticket->empresa?->nombre_empresa ?? '—' }}</td>
                                @endif
                                <td style="padding:12px;">
                                    <span class="badge {{ \App\Models\Ticket::prioridadBadgeClass($ticket->prioridad) }}">
                                        {{ \App\Models\Ticket::prioridadLabel($ticket->prioridad) }}
                                    </span>
                                </td>
                                <td style="padding:12px;">
                                    <span class="badge {{ \App\Models\Ticket::estadoBadgeClass($ticket->estado) }}">
                                        {{ \App\Models\Ticket::estadoLabel($ticket->estado) }}
                                    </span>
                                </td>
                                <td style="padding:12px; font-size:12px; color:{{ $ticket->estaVencido() ? 'var(--danger)' : 'var(--text-muted)' }};">
                                    @if($ticket->sla_due_at && !in_array($ticket->estado, ['resuelto', 'cerrado'], true))
                                        @if($ticket->estaVencido())
                                            Vencido
                                        @else
                                            {{ $ticket->sla_due_at->diffForHumans() }}
                                        @endif
                                    @elseif($ticket->resuelto_at)
                                        Resuelto {{ $ticket->resuelto_at->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="padding:12px; text-align:right;">
                                    <a href="{{ route('tickets.show', $ticket) }}" style="font-size:12px; color:var(--accent); text-decoration:none; font-weight:500;">Ver &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $tickets->links() }}</div>
        @endif
    </div>
</x-app-layout>
