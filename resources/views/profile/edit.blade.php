<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <span>Mi Perfil</span>
        </nav>
        <h1 class="page-title">Mi Perfil</h1>
        <p class="page-subtitle">Gestiona tu información personal y configuración de cuenta.</p>
    </x-slot>

    <div style="display: flex; flex-direction: column; gap: 24px; max-width: 700px;">

        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Información del Perfil</h3>
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Actualizar Contraseña</h3>
            </div>
            @include('profile.partials.update-password-form')
        </div>

        <div class="card fade-in" style="border-color: var(--danger);">
            <div class="card-header">
                <h3 class="card-title" style="color: var(--danger);">Zona de Peligro</h3>
            </div>
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
