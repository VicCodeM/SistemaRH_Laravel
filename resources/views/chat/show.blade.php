<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Mensajes</h1>
        <p class="page-subtitle">{{ $room->nombre ?? 'Conversación activa' }}</p>
    </x-slot>

    <div class="chat-layout card fade-in">
        <aside class="chat-sidebar">
            <div class="chat-sidebar-header">
                <h3 class="chat-sidebar-title">Conversaciones</h3>
            </div>
            <div class="chat-sidebar-body">
                <livewire:chat.chat-list :room-id="$room->id" />
            </div>
        </aside>

        <main class="chat-main">
            <livewire:chat.chat-conversacion :room="$room" />
        </main>
    </div>
</x-app-layout>
