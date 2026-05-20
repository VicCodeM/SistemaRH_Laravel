<span wire:poll.15s>
    @if($this->noLeidos > 0)
        <span style="position:absolute; top:6px; right:6px; min-width:18px; height:18px; background:var(--danger); color:#fff; font-size:10px; font-weight:700; border-radius:9px; display:flex; align-items:center; justify-content:center; padding:0 5px;">
            {{ $this->noLeidos > 99 ? '99+' : $this->noLeidos }}
        </span>
    @endif
</span>
