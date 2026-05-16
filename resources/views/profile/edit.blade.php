<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Mi perfil</span>
        </nav>
        <h1 class="page-title">Mi perfil</h1>
        <p class="page-subtitle">Gestiona tu información personal y la configuración de tu cuenta.</p>
    </x-slot>

    <div style="display:flex; flex-direction:column; gap:24px; max-width:700px;">
        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Información del perfil</h3>
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">Actualizar contraseña</h3>
            </div>
            @include('profile.partials.update-password-form')
        </div>

        <div class="card fade-in" style="border-color:var(--danger);">
            <div class="card-header">
                <h3 class="card-title" style="color:var(--danger);">Zona de peligro</h3>
            </div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
