<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <span>Solicitud</span>
        </nav>
        <h1 class="page-title">Solicitud de Empleo</h1>
        <p class="page-subtitle">Completa tu información para aplicar a vacantes.</p>
    </x-slot>

    <div style="max-width: 820px; margin: 0 auto;">
        <livewire:candidato-solicitud />
    </div>
</x-app-layout>
