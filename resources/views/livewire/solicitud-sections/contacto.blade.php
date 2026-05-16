<div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);margin-top:6px;">
    <div style="padding:18px 24px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#f0fdf4,#f8fafc);">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,.12);color:#10b981;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
        </div>
        <div>
            <h3 style="margin:0;font-size:.95rem;font-weight:700;color:var(--text);">Información de contacto</h3>
            <p style="margin:2px 0 0;font-size:.78rem;color:var(--text-muted);">Teléfonos y dirección actual</p>
        </div>
    </div>

    <div style="padding:22px 24px;">
        {{-- Teléfono --}}
        <div style="margin-bottom:20px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#10b981;margin:0 0 10px;">Teléfono</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Celular</label>
                    <input type="text" class="form-input" wire:model.blur="celular" placeholder="Número de celular">
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:20px;"></div>

        {{-- Dirección --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#10b981;margin:0 0 10px;">Dirección</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                <div class="form-group" style="margin:0;grid-column:1/-1;">
                    <label class="form-label">Domicilio</label>
                    <input type="text" class="form-input" wire:model.blur="domicilio" placeholder="Calle, número y referencias">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Colonia</label>
                    <input type="text" class="form-input" wire:model.blur="colonia" placeholder="Colonia">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Código postal</label>
                    <input type="text" class="form-input" wire:model.blur="codigo_postal" placeholder="CP">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Municipio</label>
                    <input type="text" class="form-input" wire:model.blur="municipio" placeholder="Municipio">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Ciudad</label>
                    <input type="text" class="form-input" wire:model.blur="ciudad" placeholder="Ciudad">
                </div>
            </div>
        </div>
    </div>
</div>
