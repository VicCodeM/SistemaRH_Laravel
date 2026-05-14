<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h1 class="page-title">Tickets de Soporte</h1>
                <p class="page-subtitle">{{ $tickets->total() }} ticket(s) en el sistema.</p>
            </div>
            @if(auth()->user()->rol === 'empresa')
                <a href="{{ route('tickets.crear') }}" style="padding:10px 18px; background: var(--accent); color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:500;">+ Nuevo ticket</a>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:12px 16px; background: var(--success-light); color: var(--success); border-radius:8px; border-left:4px solid var(--success);">{{ session('success') }}</div>
    @endif

    <div class="card fade-in">
        @if($tickets->isEmpty())
            <p class="text-muted text-sm" style="text-align:center; padding:40px;">Sin tickets registrados.</p>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">#</th>
                        <th style="text-align:left; padding:10px 12px; color:var(--text-muted); font-weight:500;">Asunto</th>
                        @if(auth()->user()->esAdmin())
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
                        @php
                            $slaVencido = $ticket->sla_due_at && $ticket->sla_due_at->isPast() && !in_array($ticket->estado, ['resuelto','cerrado']);
                            $colores = ['baja'=>'var(--text-muted)','media'=>'var(--accent)','alta'=>'var(--warning)','urgente'=>'var(--danger)'];
                            $bgs     = ['baja'=>'var(--surface-2)','media'=>'var(--accent-light)','alta'=>'var(--warning-light)','urgente'=>'var(--danger-light)'];
                            $eColores = ['abierto'=>'var(--warning)','en_proceso'=>'var(--accent)','resuelto'=>'var(--success)','cerrado'=>'var(--text-muted)'];
                            $eBgs    = ['abierto'=>'var(--warning-light)','en_proceso'=>'var(--accent-light)','resuelto'=>'var(--success-light)','cerrado'=>'var(--surface-2)'];
                        @endphp
                        <tr style="border-bottom: 1px solid var(--border); {{ $slaVencido ? 'background: rgba(239,68,68,.05);' : '' }}">
                            <td style="padding:12px; color:var(--text-muted); font-size:13px;">#{{ $ticket->id }}</td>
                            <td style="padding:12px;">
                                <p style="font-weight:500; margin:0;">{{ $ticket->asunto }}</p>
                                <p style="font-size:12px; color:var(--text-muted); margin:0;">{{ ucfirst($ticket->categoria) }}</p>
                            </td>
                            @if(auth()->user()->esAdmin())
                                <td style="padding:12px; font-size:13px;">{{ $ticket->empresa?->nombre_empresa ?? '—' }}</td>
                            @endif
                            <td style="padding:12px;">
                                <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $bgs[$ticket->prioridad] ?? 'var(--surface-2)' }}; color:{{ $colores[$ticket->prioridad] ?? 'var(--text-muted)' }};">
                                    {{ ucfirst($ticket->prioridad) }}
                                </span>
                            </td>
                            <td style="padding:12px;">
                                <span style="padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; background:{{ $eBgs[$ticket->estado] ?? 'var(--surface-2)' }}; color:{{ $eColores[$ticket->estado] ?? 'var(--text-muted)' }};">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}
                                </span>
                            </td>
                            <td style="padding:12px; font-size:12px; color:{{ $slaVencido ? 'var(--danger)' : 'var(--text-muted)' }};">
                                @if($ticket->sla_due_at && !in_array($ticket->estado, ['resuelto','cerrado']))
                                    @if($slaVencido)
                                        ⚠ Vencido ({{ $ticket->sla_due_at->diffForHumans() }})
                                    @else
                                        {{ $ticket->sla_due_at->diffForHumans() }}
                                    @endif
                                @elseif($ticket->resuelto_at)
                                    ✓ Resuelto {{ $ticket->resuelto_at->diffForHumans() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:12px; text-align:right;">
                                <a href="{{ route('tickets.show', $ticket) }}" style="font-size:12px; color: var(--accent); text-decoration:none; font-weight:500;">Ver →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:16px;">{{ $tickets->links() }}</div>
        @endif
    </div>
</x-app-layout>
