<div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);margin-top:6px;">
    <div style="padding:18px 24px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#faf5ff,#f8fafc);">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(139,92,246,.12);color:#8b5cf6;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
        </div>
        <div>
            <h3 style="margin:0;font-size:.95rem;font-weight:700;color:var(--text);">Escolaridad y habilidades</h3>
            <p style="margin:2px 0 0;font-size:.78rem;color:var(--text-muted);">Nivel de estudios, perfil y experiencia</p>
        </div>
    </div>

    <div style="padding:22px 24px;">
        {{-- Resumen --}}
        <div style="margin-bottom:20px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#8b5cf6;margin:0 0 10px;">Perfil general</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Nivel de escolaridad</label>
                    <select class="form-input" wire:model.blur="escolaridad">
                        <option value="">Selecciona un nivel</option>
                        @foreach($nivelesEstudio as $clave => $label)
                            <option value="{{ $clave }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Experiencia total (años)</label>
                    <input type="number" class="form-input" wire:model.blur="experiencia_anios" min="0" max="60" placeholder="Años de experiencia">
                </div>
                <div class="form-group" style="margin:0;grid-column:1/-1;">
                    <label class="form-label">Puesto deseado</label>
                    <input type="text" class="form-input" wire:model.blur="puesto_deseado" placeholder="Ej. Desarrollador web, Auxiliar administrativo…">
                </div>
                <div class="form-group" style="margin:0;grid-column:1/-1;">
                    <label class="form-label">Habilidades principales</label>
                    <textarea class="form-input" wire:model.blur="habilidades" rows="2" placeholder="Habilidades separadas por coma: Excel, atención al cliente, comunicación…"></textarea>
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:20px;"></div>

        {{-- Estudios detallados --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div>
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#8b5cf6;margin:0;">Estudios detallados</p>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:3px 0 0;">Opcional — agrega más datos para afinar coincidencias</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="agregarEscolaridad">+ Agregar estudio</button>
            </div>

            @if(count($escolaridad_detallada) > 0)
                <div style="display:grid;gap:10px;">
                    @foreach($escolaridad_detallada as $index => $estudio)
                        <div wire:key="estudio-{{ $index }}" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface-2);">
                            <div style="display:grid;grid-template-columns:160px 1fr 100px 1fr;gap:10px;align-items:end;flex-wrap:wrap;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nivel</label>
                                    <select class="form-input" wire:model.blur="escolaridad_detallada.{{ $index }}.nivel">
                                        <option value="">Selecciona</option>
                                        @foreach($nivelesEstudio as $clave => $label)
                                            <option value="{{ $clave }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Institución / carrera</label>
                                    <input type="text" class="form-input" wire:model.blur="escolaridad_detallada.{{ $index }}.nombre" placeholder="Institución o carrera">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Duración</label>
                                    <input type="text" class="form-input" wire:model.blur="escolaridad_detallada.{{ $index }}.anios" placeholder="Años">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Título / especialidad</label>
                                    <input type="text" class="form-input" wire:model.blur="escolaridad_detallada.{{ $index }}.titulo" placeholder="Título o especialidad">
                                </div>
                            </div>
                            <div style="text-align:right;margin-top:10px;">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="eliminarEscolaridad({{ $index }})" style="color:var(--danger);border-color:var(--danger-light);">Quitar</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:20px;border:2px dashed var(--border);border-radius:12px;text-align:center;color:var(--text-muted);font-size:.82rem;">
                    Sin estudios detallados. Agrega si deseas mejorar el perfil.
                </div>
            @endif
        </div>
    </div>
</div>
