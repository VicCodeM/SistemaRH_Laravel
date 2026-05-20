@php
    $licenciaTiene = $licencia_conducir['tiene'] ?? '';
@endphp

<div class="solicitud-card" style="margin-top:6px;">
    <div class="solicitud-card-header" style="background:linear-gradient(135deg,#fff1f2,#f8fafc);">
        <div class="solicitud-card-icon" style="background:rgba(244,63,94,.12);color:#f43f5e;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        </div>
        <div>
            <h3 class="solicitud-card-title">Documentos y datos extras</h3>
            <p class="solicitud-card-subtitle">CURP, NSS, documentos, licencia, redes y referencias</p>
        </div>
    </div>

    <div style="padding:24px;">

        {{-- Documentos oficiales --}}
        <div style="margin-bottom:22px;">
            <p class="section-label" style="color:#f43f5e;">Documentos oficiales</p>
            <div class="solicitud-grid-2">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">CURP <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model="curp" placeholder="CURP (18 caracteres)">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">NSS — Número de seguro social <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model="nore_seguro_social" placeholder="Número de seguro social">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">RFC <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model="rfc" placeholder="RFC">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Afore <span class="req">*</span></label>
                    <input type="text" class="form-input" wire:model="afore" placeholder="Nombre de tu Afore">
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Cartilla militar --}}
        <div style="margin-bottom:22px;">
            <p class="section-label" style="color:#f43f5e;">Cartilla militar</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $cartilla_tiene === 'si' ? '12px' : '0' }};">
                    <label class="form-label">¿Tienes cartilla militar? <span class="req">*</span></label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="cartilla_tiene" value="si">
                            <span>Sí, tengo</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="cartilla_tiene" value="no">
                            <span>No tengo</span>
                        </label>
                    </div>
                </div>
                @if($cartilla_tiene === 'si')
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Número de cartilla</label>
                        <input type="text" class="form-input" wire:model="cartilla_militar" placeholder="Número de cartilla militar">
                    </div>
                @endif
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Pasaporte --}}
        <div style="margin-bottom:22px;">
            <p class="section-label" style="color:#f43f5e;">Pasaporte</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $pasaporte_tiene === 'si' ? '12px' : '0' }};">
                    <label class="form-label">¿Tienes pasaporte? <span class="req">*</span></label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="pasaporte_tiene" value="si">
                            <span>Sí, tengo</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="pasaporte_tiene" value="no">
                            <span>No tengo</span>
                        </label>
                    </div>
                </div>
                @if($pasaporte_tiene === 'si')
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Número de pasaporte</label>
                        <input type="text" class="form-input" wire:model="pasaporte" placeholder="Número de pasaporte">
                    </div>
                @endif
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Licencia de conducir --}}
        <div style="margin-bottom:22px;">
            <p class="section-label" style="color:#f43f5e;">Licencia de conducir</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $licenciaTiene === 'si' ? '14px' : '0' }};">
                    <label class="form-label">¿Tienes licencia de conducir? <span class="req">*</span></label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="licencia_conducir.tiene" value="si">
                            <span>Sí, tengo</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" wire:model.live="licencia_conducir.tiene" value="no">
                            <span>No tengo</span>
                        </label>
                    </div>
                </div>
                @if($licenciaTiene === 'si')
                    <div class="solicitud-grid-3">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Clase</label>
                            <input type="text" class="form-input" wire:model="licencia_conducir.clase" placeholder="Ej. A, B, C">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-input" wire:model="licencia_conducir.numero" placeholder="Número de licencia">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Vigencia</label>
                            <input type="date" class="form-input" wire:model="licencia_conducir.vigencia">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="section-divider"></div>

        {{-- Referencias personales --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div>
                    <p class="section-label" style="color:#f43f5e;">Referencias personales</p>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:3px 0 0;">Opcional — personas que puedan dar referencias de ti</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" x-on:click="$wire.agregarReferencia()" wire:loading.attr="disabled">+ Agregar referencia</button>
            </div>

            @if(count($referencias_personales) > 0)
                <div style="display:grid;gap:10px;">
                    @foreach($referencias_personales as $index => $referencia)
                        <div wire:key="referencia-{{ $index }}" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface-2);">
                            <div class="solicitud-grid-4" style="margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-input" wire:model="referencias_personales.{{ $index }}.nombre" placeholder="Nombre completo">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-input" wire:model="referencias_personales.{{ $index }}.telefono" placeholder="Teléfono">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Ocupación</label>
                                    <input type="text" class="form-input" wire:model="referencias_personales.{{ $index }}.ocupacion" placeholder="Ocupación">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Tiempo de conocerlo</label>
                                    <input type="text" class="form-input" wire:model="referencias_personales.{{ $index }}.tiempo" placeholder="Ej. 3 años">
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;align-items:flex-end;">
                                <div class="form-group" style="margin:0;flex:1;">
                                    <label class="form-label">Domicilio</label>
                                    <input type="text" class="form-input" wire:model="referencias_personales.{{ $index }}.domicilio" placeholder="Domicilio de la referencia">
                                </div>
                                <button type="button" class="btn btn-ghost btn-sm" x-on:click="$wire.eliminarReferencia({{ $index }})" wire:loading.attr="disabled" style="color:var(--danger);border-color:var(--danger-light);flex-shrink:0;">Quitar</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:20px;border:2px dashed var(--border);border-radius:12px;text-align:center;color:var(--text-muted);font-size:.82rem;">
                    Sin referencias. Puedes agregar si lo deseas.
                </div>
            @endif
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
    padding: 9px 22px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    font-size: .84rem;
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
.radio-pill input[type="radio"]:checked[value="no"] + span {
    background: #10b981;
    border-color: #10b981;
    box-shadow: 0 2px 10px rgba(16,185,129,.2);
}
.req { color: var(--danger); }
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
