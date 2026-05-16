<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Solicitud</span>
        </nav>
        <h1 class="page-title">Solicitud del candidato</h1>
        <p class="page-subtitle">Completa tu perfil en una sola ficha, con pestañas internas y sin pasos innecesarios.</p>
    </x-slot>

    <div style="max-width:1180px; margin:0 auto;">
        <livewire:candidato-solicitud />
    </div>
</x-app-layout>
