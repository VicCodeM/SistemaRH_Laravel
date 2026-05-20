<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <div>
                <nav class="breadcrumbs">
                    <a href="{{ route('admin.dashboard') }}">Administración</a>
                    <span class="breadcrumb-sep">›</span>
                    <a href="{{ route('admin.tareas.index') }}">Tareas</a>
                    <span class="breadcrumb-sep">›</span>
                    <span>Tablero Kanban</span>
                </nav>
                <h1 class="page-title">Tablero Kanban de servicios</h1>
                <p class="page-subtitle">Visualiza, filtra y mueve servicios entre columnas.</p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.tareas.index') }}" class="btn btn-secondary" style="font-size:13px;">Lista</a>
                <a href="{{ route('admin.tareas.crear') }}" class="btn btn-primary">+ Nuevo servicio</a>
            </div>
        </div>
    </x-slot>

    <livewire:servicios-kanban-board />
</x-app-layout>
