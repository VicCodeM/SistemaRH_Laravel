<div class="solicitud-card" style="margin-top:6px;">
    <div class="solicitud-card-header" style="background:linear-gradient(135deg,#fff7ed,#f8fafc);">
        <div class="solicitud-card-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
        </div>
        <div>
            <h3 class="solicitud-card-title">Experiencia laboral</h3>
            <p class="solicitud-card-subtitle">Historial de empleos y aspiración salarial</p>
        </div>
    </div>

    <div style="padding:24px;">
        {{-- Sueldo deseado --}}
        <div style="margin-bottom:22px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f59e0b;margin:0 0 12px;">Aspiración salarial</p>
            <div class="solicitud-grid-2">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Sueldo deseado <span style="color:var(--danger);">*</span></label>
                    <input type="text" class="form-input" wire:model.blur="sueldo_deseado" placeholder="Ej. $15,000">
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:22px;"></div>

        {{-- Historial laboral --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div>
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f59e0b;margin:0;">Historial laboral</p>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:3px 0 0;">Opcional — mejora el filtrado automático de candidatos</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="agregarEmpleo">+ Agregar empleo</button>
            </div>

            @if(count($historial_laboral) > 0)
                <div style="display:grid;gap:12px;">
                    @foreach($historial_laboral as $index => $empleo)
                        <div wire:key="empleo-{{ $index }}" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface-2);">
                            <div class="solicitud-grid-3" style="margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.empresa" placeholder="Nombre de la empresa">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Puesto</label>
                                    <input type="text" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.puesto" placeholder="Puesto desempeñado">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Jefe directo</label>
                                    <input type="text" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.jefe" placeholder="Nombre del jefe">
                                </div>
                            </div>
                            <div class="solicitud-grid-3" style="margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Sueldo</label>
                                    <input type="text" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.sueldo" placeholder="Sueldo mensual">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Desde</label>
                                    <input type="date" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.desde">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-input" wire:model.blur="historial_laboral.{{ $index }}.hasta">
                                </div>
                            </div>
                            <div class="form-group" style="margin:0 0 10px;">
                                <label class="form-label">Motivo de salida</label>
                                <textarea class="form-input" rows="2" wire:model.blur="historial_laboral.{{ $index }}.motivo" placeholder="Motivo de salida del empleo"></textarea>
                            </div>
                            <div style="text-align:right;">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="eliminarEmpleo({{ $index }})" style="color:var(--danger);border-color:var(--danger-light);">Quitar empleo</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:20px;border:2px dashed var(--border);border-radius:12px;text-align:center;color:var(--text-muted);font-size:.82rem;">
                    Sin historial laboral. Agrega empleos anteriores si deseas mejorar el perfil.
                </div>
            @endif
        </div>
    </div>
</div>
