<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">›</span>
            <span>Interno</span>
        </nav>
        <h1 class="page-title">Panel de Operaciones</h1>
        <p class="page-subtitle">Gestión de tareas internas y tickets asignados.</p>
    </x-slot>

    <div class="card fade-in" style="text-align: center; padding: 60px 40px;">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 40px; height: 40px; color: var(--text-muted); margin-bottom: 16px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-4.093 2.046a1.125 1.125 0 01-1.54-1.163l.544-4.544a1.125 1.125 0 01.327-.637l7.127-7.126a1.125 1.125 0 011.59 0l3.755 3.754a1.125 1.125 0 010 1.59l-7.127 7.127a1.125 1.125 0 01-.637.327l-4.544.544a1.125 1.125 0 01-1.163-1.54l2.045-4.093z"/>
        </svg>
        <h3 style="font-weight: 600; margin-bottom: 8px;">En construcción</h3>
        <p class="text-muted text-sm">Tareas, SLA y tickets se implementarán en la siguiente fase.</p>
    </div>
</x-app-layout>
