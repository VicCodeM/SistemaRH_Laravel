<div class="solicitud-card" style="margin-top:6px;">
    <div class="solicitud-card-header" style="background:linear-gradient(135deg,#f0fdf4,#f8fafc);">
        <div class="solicitud-card-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
        </div>
        <div>
            <h3 class="solicitud-card-title">Información de contacto</h3>
            <p class="solicitud-card-subtitle">Teléfonos y dirección actual</p>
        </div>
    </div>

    <div style="padding:24px;">
        {{-- Teléfono --}}
        <div style="margin-bottom:22px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#10b981;margin:0 0 12px;">Teléfono</p>
            <div class="solicitud-grid-2">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Teléfono fijo</label>
                    <input type="text" class="form-input" wire:model="telefono" placeholder="Número de teléfono (opcional)" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Celular <span style="color:var(--danger);">*</span></label>
                    <input type="text" class="form-input" wire:model="celular" placeholder="Número de celular" spellcheck="true" autocapitalize="sentences">
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:22px;"></div>

        {{-- Dirección --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#10b981;margin:0 0 12px;">Dirección</p>
            <div style="display:grid;gap:12px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Domicilio <span style="color:var(--danger);">*</span></label>
                    <input type="text" class="form-input" wire:model="domicilio" placeholder="Calle, número y referencias" spellcheck="true" autocapitalize="sentences">
                </div>
                <div class="solicitud-grid-2">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Colonia <span style="color:var(--danger);">*</span></label>
                        <input type="text" class="form-input" wire:model="colonia" placeholder="Colonia" spellcheck="true" autocapitalize="sentences">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Código postal <span style="color:var(--danger);">*</span></label>
                        <input type="text" class="form-input" wire:model="codigo_postal" placeholder="CP" spellcheck="true" autocapitalize="sentences">
                    </div>
                </div>
                <div class="solicitud-grid-2">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Municipio <span style="color:var(--danger);">*</span></label>
                        <input type="text" class="form-input" wire:model="municipio" placeholder="Municipio" spellcheck="true" autocapitalize="sentences">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Ciudad <span style="color:var(--danger);">*</span></label>
                        <input type="text" class="form-input" wire:model="ciudad" placeholder="Ciudad" spellcheck="true" autocapitalize="sentences">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
