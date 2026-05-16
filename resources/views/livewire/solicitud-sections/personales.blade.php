@php
    $pillBase = "padding:9px 20px;border-radius:10px;border:1.5px solid;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .18s;font-family:var(--font);white-space:nowrap;";
    $pillOn   = "background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 10px rgba(37,99,235,.28);";
    $pillOff  = "background:#fff;color:var(--text-muted);border-color:var(--border);";
@endphp

<div class="solicitud-card" style="margin-top:6px;">
    <div class="solicitud-card-header" style="background:linear-gradient(135deg,#eff6ff,#f8fafc);">
        <div class="solicitud-card-icon" style="background:rgba(37,99,235,.12);color:var(--accent);">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
        </div>
        <div>
            <h3 class="solicitud-card-title">Datos personales</h3>
            <p class="solicitud-card-subtitle">Todos los campos son obligatorios</p>
        </div>
    </div>

    <div style="padding:24px;">

        {{-- ═══ NOMBRE COMPLETO ═══ --}}
        <div style="margin-bottom:22px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--accent);margin:0 0 12px;">Nombre completo</p>
            <div class="solicitud-grid-3">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Nombre(s)</label>
                    <input type="text" class="form-input" wire:model.blur="nombre" placeholder="Nombre(s)">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Apellido paterno</label>
                    <input type="text" class="form-input" wire:model.blur="apellido_paterno" placeholder="Apellido paterno">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Apellido materno</label>
                    <input type="text" class="form-input" wire:model.blur="apellido_materno" placeholder="Apellido materno">
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:22px;"></div>

        {{-- ═══ DATOS GENERALES ═══ --}}
        <div style="margin-bottom:22px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--accent);margin:0 0 12px;">Datos generales</p>

            {{-- Fila 1: edad, fecha, lugar, nacionalidad --}}
            <div class="solicitud-grid-4" style="margin-bottom:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Edad</label>
                    <input type="number" class="form-input" wire:model.blur="edad" min="14" max="100" placeholder="Años">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" class="form-input" wire:model.blur="fecha_nacimiento">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Lugar de nacimiento</label>
                    <input type="text" class="form-input" wire:model.blur="lugar_nacimiento" placeholder="Ciudad, estado o país">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Nacionalidad</label>
                    <input type="text" class="form-input" wire:model.blur="nacionalidad" placeholder="Mexicana">
                </div>
            </div>

            {{-- Fila 2: Sexo + Estado civil (2 cols) --}}
            <div class="solicitud-grid-2">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Sexo</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                        @foreach(['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $val => $label)
                            <button type="button"
                                wire:click="setSexo('{{ $val }}')"
                                style="{{ $sexo === $val ? $pillOn : $pillOff }}{{ $pillBase }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Estado civil</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                        @foreach(['Soltero/a' => 'Soltero/a', 'Casado/a' => 'Casado/a', 'Union libre' => 'Unión libre', 'Divorciado/a' => 'Divorciado/a', 'Viudo/a' => 'Viudo/a'] as $val => $label)
                            <button type="button"
                                wire:click="setEstadoCivil('{{ $val }}')"
                                style="{{ $estado_civil === $val ? $pillOn : $pillOff }}{{ $pillBase }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:22px;"></div>

        {{-- ═══ INFORMACIÓN ADICIONAL ═══ --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--accent);margin:0 0 12px;">Información adicional</p>

            {{-- Vive con --}}
            <div class="form-group" style="margin:0 0 16px;">
                <label class="form-label">Vive con</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                    @foreach(['Solo/a', 'Con familia', 'Con pareja', 'Con companeros'] as $opt)
                        <button type="button"
                            wire:click="setViveCon('{{ $opt }}')"
                            style="{{ $vive_con === $opt ? $pillOn : $pillOff }}{{ $pillBase }}">
                            {{ str_replace('Con companeros', 'Con compañeros', $opt) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Peso y estatura --}}
            <div class="solicitud-grid-2" style="margin-bottom:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Peso</label>
                    <input type="text" class="form-input" wire:model.blur="peso" placeholder="Ej. 72 kg">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Estatura</label>
                    <input type="text" class="form-input" wire:model.blur="estatura" placeholder="Ej. 1.75 m">
                </div>
            </div>

            {{-- Dependientes --}}
            <div class="form-group" style="margin:0;">
                <label class="form-label">Dependientes económicos</label>
                <textarea class="form-input" wire:model.blur="dependientes" rows="2"
                    placeholder="Ej. 2 hijos, 1 padre. Si no tienes, escribe 'Ninguno'."></textarea>
            </div>
        </div>

    </div>
</div>

<style>
.solicitud-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.solicitud-card-header {
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.solicitud-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.solicitud-card-title {
    margin: 0;
    font-size: .95rem;
    font-weight: 700;
    color: var(--text);
}
.solicitud-card-subtitle {
    margin: 2px 0 0;
    font-size: .78rem;
    color: var(--text-muted);
}

/* Grid layouts responsive */
.solicitud-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}
.solicitud-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.solicitud-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

@media (max-width: 900px) {
    .solicitud-grid-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .solicitud-grid-4,
    .solicitud-grid-3,
    .solicitud-grid-2 {
        grid-template-columns: 1fr;
    }
    .solicitud-card-header {
        padding: 14px 16px 10px;
    }
    .solicitud-card {
        padding: 16px;
    }
}
</style>
