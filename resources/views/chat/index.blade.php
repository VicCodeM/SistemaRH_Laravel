<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Chat</h1>
        <p class="page-subtitle">Conversaciones con el equipo.</p>
    </x-slot>

    <div class="card fade-in" style="display:grid; grid-template-columns:280px 1fr; height:580px; overflow:hidden; padding:0;">

        {{-- Panel izquierdo: lista de chats --}}
        <div style="border-right:1px solid var(--border); overflow-y:auto; padding:12px;">
            <p style="font-size:11px; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; padding:4px 8px; margin-bottom:8px;">Conversaciones</p>
            <livewire:chat.chat-list />
        </div>

        {{-- Panel derecho: vacío al inicio --}}
        <div style="display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:40px; height:40px; margin:0 auto 12px; opacity:.4;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                </svg>
                <p style="font-size:14px;">Selecciona una conversación</p>
            </div>
        </div>

    </div>
</x-app-layout>
