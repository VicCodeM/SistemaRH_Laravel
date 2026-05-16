<div wire:poll.3000ms="actualizarMensajes" style="display:flex; flex-direction:column; height:100%;">
    <div id="chat-mensajes" style="flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:12px;">
        @forelse($mensajes as $msg)
            @php $esMio = $msg->sender_user_id === auth()->id(); @endphp
            <div style="display:flex; {{ $esMio ? 'justify-content:flex-end' : 'justify-content:flex-start' }};">
                <div style="max-width:70%;">
                    @if(!$esMio)
                        <p style="font-size:11px; color:var(--text-muted); margin:0 0 3px 4px;">
                            {{ $msg->sender?->name ?? 'Usuario' }} · {{ ucfirst($msg->sender_role) }}
                        </p>
                    @endif
                    <div style="padding:10px 14px; border-radius:{{ $esMio ? '16px 16px 4px 16px' : '16px 16px 16px 4px' }};
                        background: {{ $esMio ? 'var(--accent)' : 'var(--surface-2)' }};
                        color: {{ $esMio ? '#fff' : 'inherit' }};
                        font-size:14px; line-height:1.5;">
                        {{ $msg->contenido }}
                    </div>
                    <p style="font-size:11px; color:var(--text-muted); margin:3px 4px 0; text-align:{{ $esMio ? 'right' : 'left' }};">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <div style="flex:1; display:flex; align-items:center; justify-content:center;">
                <p style="color:var(--text-muted); font-size:14px;">Sé el primero en escribir un mensaje.</p>
            </div>
        @endforelse
    </div>

    <div style="padding:16px 20px; border-top:1px solid var(--border); background: var(--surface);">
        <form wire:submit="enviar" style="display:flex; gap:10px; align-items:flex-end;">
            <textarea wire:model="mensaje" rows="1" placeholder="Escribe un mensaje..."
                style="flex:1; padding:10px 14px; border:1px solid var(--border); border-radius:12px; font-size:14px; background: var(--surface-2); resize:none; max-height:120px; overflow-y:auto;"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();$wire.enviar();}"></textarea>
            <button type="submit"
                style="padding:10px 16px; background: var(--accent); color:#fff; border:none; border-radius:12px; cursor:pointer; font-size:14px; white-space:nowrap; font-weight:500;">
                Enviar
            </button>
        </form>
        @error('mensaje') <p style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
    </div>
</div>

<script>
    document.addEventListener('livewire:updated', () => {
        const el = document.getElementById('chat-mensajes');
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('chat-mensajes');
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    });
</script>
