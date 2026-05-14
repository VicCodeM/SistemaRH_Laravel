<div>
    <div class="flex items-center justify-between mb-4">
        <select wire:model.live="vacanteId" class="form-input" style="width: auto; padding: 7px 32px 7px 12px; font-size: 0.85rem;">
            <option value="">Todas las vacantes</option>
            @foreach($vacantes as $v)
                <option value="{{ $v->id }}">{{ $v->titulo }}</option>
            @endforeach
        </select>
    </div>

    @php
        $columns = [
            'postulados' => ['label' => 'Postulados', 'color' => 'var(--accent)', 'bg' => 'var(--accent-light)'],
            'entrevista' => ['label' => 'Entrevista', 'color' => '#d97706', 'bg' => 'var(--warning-light)'],
            'seleccionados' => ['label' => 'Seleccionados', 'color' => '#059669', 'bg' => 'var(--success-light)'],
            'rechazados' => ['label' => 'Rechazados', 'color' => '#dc2626', 'bg' => 'var(--danger-light)'],
        ];
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        @foreach($columns as $key => $col)
            <div class="card" style="padding: 0;">
                <div style="padding: 14px 16px; border-bottom: 2px solid {{ $col['color'] }}; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 600; font-size: 0.85rem;">{{ $col['label'] }}</span>
                    <span class="badge" style="background: {{ $col['bg'] }}; color: {{ $col['color'] }};">{{ count($$key) }}</span>
                </div>
                <div style="padding: 8px;">
                    @forelse($$key as $p)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; margin-bottom: 6px; border-left: 3px solid {{ $col['color'] }};">
                            <div style="font-weight: 600; font-size: 0.85rem;">{{ $p->candidato->nombre ?? 'Candidato' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">{{ $p->updated_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-muted text-center" style="padding: 16px 0; font-size: 0.8rem;">Sin candidatos</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
