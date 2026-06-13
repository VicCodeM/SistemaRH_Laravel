<div>
    <div class="flex items-center justify-between mb-4">
        <select wire:model.live="vacanteId" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
            <option value="">Todas las solicitudes</option>
            @foreach($vacantes as $v)
                <option value="{{ $v->id }}">{{ $v->titulo }}</option>
            @endforeach
        </select>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        @foreach($estados as $estado => $estadoLabel)
            @php
                $estadoBadge = \App\Models\Postulacion::estadoBadgeClass($estado);
                $items = $postulacionesPorEstado[$estado] ?? collect();
            @endphp
            <div class="card" style="padding: 0;">
                <div style="padding: 14px 16px; border-bottom: 2px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                    <span class="badge {{ $estadoBadge }}" style="font-size: 0.85rem;">{{ $estadoLabel }}</span>
                    <span class="badge {{ $estadoBadge }}">{{ $items->count() }}</span>
                </div>
                <div style="padding: 8px;">
                    @forelse($items as $p)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; margin-bottom: 6px; border-left: 3px solid var(--border);">
                            <div style="font-weight: 600; font-size: 0.85rem;">{{ $p->candidato->nombre ?? 'Candidato' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">{{ $p->updated_at->diffForHumans() }}</div>
                            @if(auth()->user() && (auth()->user()->esAdmin() || auth()->user()->esInterno()))
                                <select wire:change="moverEstado({{ $p->id }}, $event.target.value)" style="font-size: 0.72rem; padding: 3px 6px; margin-top: 6px; width: 100%; border-radius: var(--radius); border: 1px solid var(--border); background: white;">
                                    @foreach($estados as $estadoKey => $estadoNombre)
                                        <option value="{{ $estadoKey }}" @selected($p->estado === $estadoKey)>{{ $estadoNombre }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted text-center" style="padding: 16px 0; font-size: 0.8rem;">Sin candidatos</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
