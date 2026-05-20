{{-- Variables esperadas: $servicio (ServicioAsignado con relación comentarios) --}}
@php
    $comentarios = $servicio->comentarios()->get();
    $puedeComentar = match(auth()->user()?->rol) {
        'admin'     => true,
        'interno'   => $servicio->asignado_a === auth()->id(),
        'empresa'   => $servicio->asignable_type === \App\Models\Empresa::class && $servicio->asignable_id === auth()->user()->empresa?->id,
        'candidato' => $servicio->asignable_type === \App\Models\Candidato::class && $servicio->asignable_id === auth()->user()->candidato?->id,
        default     => false,
    };
@endphp

<div class="card" style="padding:20px; margin-bottom:18px;">
    <h3 style="margin:0 0 14px; font-size:0.95rem; font-weight:700;">💬 Conversación del pedido</h3>

    @if($puedeComentar)
        <form method="POST" action="{{ route('pedidos.comentarios.store', $servicio) }}" style="margin-bottom:18px;">
            @csrf
            <textarea name="mensaje" rows="2" maxlength="2000" required class="form-input"
                      placeholder="Escribe una actualización, pregunta o nota..."></textarea>
            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                <button type="submit" class="btn btn-primary" style="font-size:13px;">Enviar comentario</button>
            </div>
        </form>
    @endif

    @if($comentarios->isEmpty())
        <p style="margin:0; color:#94a3b8; font-size:0.85rem; text-align:center; padding:12px;">
            Aún no hay comentarios. {{ $puedeComentar ? 'Escribe el primero arriba ↑' : '' }}
        </p>
    @else
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($comentarios as $c)
                @php
                    $rol = $c->autor?->rol ?? '';
                    $colorBg = match($rol) {
                        'admin'     => 'rgba(59,130,246,.06)',
                        'interno'   => 'rgba(34,197,94,.06)',
                        'empresa'   => 'rgba(245,158,11,.06)',
                        'candidato' => 'rgba(168,85,247,.06)',
                        default     => 'var(--surface-2)',
                    };
                    $colorBadge = match($rol) {
                        'admin'     => 'badge-blue',
                        'interno'   => 'badge-green',
                        'empresa'   => 'badge-yellow',
                        'candidato' => 'badge-purple',
                        default     => 'badge-gray',
                    };
                    $labelRol = match($rol) {
                        'admin' => 'Admin', 'interno' => 'Interno', 'empresa' => 'Empresa', 'candidato' => 'Candidato', default => '',
                    };
                    $esAutor = $c->user_id === auth()->id();
                    $esAdmin = auth()->user()?->rol === 'admin';
                @endphp
                <div style="background:{{ $colorBg }}; border:1px solid var(--border); border-radius:10px; padding:12px 14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-avatar :src="$c->autor?->avatar_url" :nombre="$c->autor?->name ?? '?'" :tamano="24" />
                            <span style="font-weight:600; font-size:0.85rem;">{{ $c->autor?->name ?? 'Usuario' }}</span>
                            <span class="badge {{ $colorBadge }}" style="font-size:10px;">{{ $labelRol }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:11px; color:#94a3b8;">{{ $c->created_at?->diffForHumans() }}</span>
                            @if($esAutor || $esAdmin)
                                <button type="button" onclick="rhModal('{{ route('pedidos.comentarios.destroy.modal', $c) }}')" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:11px;" title="Borrar">✕</button>
                            @endif
                        </div>
                    </div>
                    <p style="margin:0; font-size:0.88rem; color:var(--text); white-space:pre-wrap;">{{ $c->mensaje }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
