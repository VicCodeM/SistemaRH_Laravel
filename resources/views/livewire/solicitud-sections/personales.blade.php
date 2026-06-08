@php
    $sexoOpciones = ['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'];
    $estadoCivilOpciones = ['Soltero/a','Casado/a','Union libre','Divorciado/a','Viudo/a'];
    $viveConOpciones = ['Solo/a','Con familia','Con pareja','Con companeros'];
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
            <p class="section-label" style="color:var(--accent);">Nombre completo</p>
            <div class="solicitud-grid-3">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Nombre(s) <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="nombre" placeholder="Nombre(s)" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Apellido paterno <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="apellido_paterno" placeholder="Apellido paterno" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Apellido materno <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="apellido_materno" placeholder="Apellido materno" spellcheck="true" autocapitalize="sentences">
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- ═══ DATOS GENERALES ═══ --}}
        <div style="margin-bottom:22px;">
            <p class="section-label" style="color:var(--accent);">Datos generales</p>

            <div class="solicitud-grid-4" style="margin-bottom:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Edad <span class="req">*</span></label>
                    <input type="number" class="form-input" wire:model.blur="edad" min="14" max="100" placeholder="Años">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Fecha de nacimiento <span class="req">*</span></label>
                    <input type="date" class="form-input" wire:model.blur="fecha_nacimiento">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Lugar de nacimiento <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="lugar_nacimiento" placeholder="Ciudad, estado o país" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Nacionalidad <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="nacionalidad" placeholder="Mexicana" spellcheck="true" autocapitalize="sentences">
                </div>
            </div>

            <div class="solicitud-grid-2">
                {{-- Sexo --}}
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Sexo <span class="req">*</span></label>
                    <div class="radio-group">
                        @foreach($sexoOpciones as $val => $label)
                            <label class="radio-pill">
                                <input type="radio" wire:model.live="sexo" name="sexo" value="{{ $val }}">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Estado civil --}}
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Estado civil <span class="req">*</span></label>
                    <div class="radio-group">
                        @foreach($estadoCivilOpciones as $val)
                            <label class="radio-pill">
                                <input type="radio" wire:model.live="estado_civil" name="estado_civil" value="{{ $val }}">
                                <span>{{ str_replace('Union libre', 'Unión libre', $val) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- ═══ INFORMACIÓN ADICIONAL ═══ --}}
        <div>
            <p class="section-label" style="color:var(--accent);">Información adicional</p>

            <div class="form-group" style="margin:0 0 16px;">
                <label class="form-label">Vive con <span class="req">*</span></label>
                <div class="radio-group">
                    @foreach($viveConOpciones as $opt)
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="vive_con" name="vive_con" value="{{ $opt }}">
                            <span>{{ str_replace('Con companeros', 'Con compañeros', $opt) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="solicitud-grid-2" style="margin-bottom:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Peso <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="peso" placeholder="Ej. 72 kg" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Estatura <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="estatura" placeholder="Ej. 1.75 m" spellcheck="true" autocapitalize="sentences">
                </div>
            </div>

            <div class="form-group" style="margin:0;">
                <label class="form-label">Dependientes económicos <span class="req">*</span></label>
                <textarea class="form-input" wire:model.blur="dependientes" rows="2" spellcheck="true" autocapitalize="sentences"
                    placeholder="Ej. 2 hijos, 1 padre. Si no tienes, escribe 'Ninguno'."></textarea>
            </div>
        </div>

    </div>
</div>

<style>
.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}
.radio-pill {
    position: relative;
    cursor: pointer;
    user-select: none;
}
.radio-pill input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.radio-pill span {
    display: inline-block;
    padding: 9px 20px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    font-size: .82rem;
    font-weight: 600;
    font-family: var(--font);
    transition: all .18s;
    white-space: nowrap;
}
.radio-pill:hover span {
    border-color: #cbd5e1;
}
.radio-pill input[type="radio"]:checked + span {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
    box-shadow: 0 2px 10px rgba(37,99,235,.28);
}
.req {
    color: var(--danger);
}
.section-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin: 0 0 12px;
}
.section-divider {
    height: 1px;
    background: var(--border);
    margin-bottom: 22px;
}
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
    .solicitud-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .solicitud-grid-4,
    .solicitud-grid-3,
    .solicitud-grid-2 { grid-template-columns: 1fr; }
    .solicitud-card-header { padding: 14px 16px 10px; }
}
</style>
