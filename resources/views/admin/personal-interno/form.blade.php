<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.dashboard') }}">Administración</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.personal-interno.index') }}">Personal interno</a>
            <span class="breadcrumb-sep">›</span>
            <span>Nuevo</span>
        </nav>
        <h1 class="page-title">Agregar personal interno</h1>
        <p class="page-subtitle">Se creará la cuenta y se enviará un enlace de acceso al correo indicado.</p>
    </x-slot>

    <div style="max-width:520px;">
        @if($errors->any())
            <div style="margin-bottom:16px; padding:12px 16px; background:var(--danger-light); color:var(--danger); border-radius:8px; border-left:4px solid var(--danger);">
                <ul style="margin:0; padding-left:16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card" style="padding:28px;">
            <form method="POST" action="{{ route('admin.personal-interno.guardar') }}">
                @csrf

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--text);">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Ej. María González"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text); box-sizing:border-box;">
                </div>

                <div style="margin-bottom:28px;">
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--text);">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="colaborador@empresa.com"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text); box-sizing:border-box;">
                    <p style="margin:6px 0 0; font-size:12px; color:#64748b;">Se enviará un correo con el enlace para que el colaborador establezca su contraseña.</p>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <a href="{{ route('admin.personal-interno.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear y enviar acceso</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
