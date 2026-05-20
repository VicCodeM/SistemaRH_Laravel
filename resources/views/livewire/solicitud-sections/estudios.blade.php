@php
    $pillBase = "padding:9px 20px;border-radius:10px;border:1.5px solid;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .18s;font-family:var(--font);white-space:nowrap;";
    $pillOn   = "background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 10px rgba(37,99,235,.28);";
    $pillOff  = "background:#fff;color:var(--text-muted);border-color:var(--border);";
@endphp

<div class="solicitud-card" style="margin-top:6px;">
    <div class="solicitud-card-header" style="background:linear-gradient(135deg,#faf5ff,#f8fafc);">
        <div class="solicitud-card-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
        </div>
        <div>
            <h3 class="solicitud-card-title">Escolaridad y habilidades</h3>
            <p class="solicitud-card-subtitle">Nivel de estudios, perfil y experiencia</p>
        </div>
    </div>

    <div style="padding:24px;">
        {{-- Perfil general --}}
        <div style="margin-bottom:22px;">
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#8b5cf6;margin:0 0 12px;">Perfil general</p>
            <div style="display:grid;gap:12px;">
                <div class="solicitud-grid-2">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Nivel de escolaridad <span style="color:var(--danger);">*</span></label>
                        <select class="form-input" wire:model="escolaridad">
                            <option value="">Selecciona un nivel</option>
                            @foreach($nivelesEstudio as $clave => $label)
                                <option value="{{ $clave }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Experiencia total (años) <span style="color:var(--danger);">*</span></label>
                        <input type="number" class="form-input" wire:model="experiencia_anios" min="0" max="60" placeholder="Años de experiencia">
                    </div>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Puesto deseado <span style="color:var(--danger);">*</span></label>
                    <input type="text" class="form-input" wire:model="puesto_deseado" placeholder="Ej. Desarrollador web, Auxiliar administrativo…">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Habilidades principales <span style="color:var(--danger);">*</span></label>
                    <textarea class="form-input" wire:model="habilidades" rows="2" placeholder="Habilidades separadas por coma: Excel, atención al cliente, comunicación…"></textarea>
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin-bottom:22px;"></div>

        {{-- Estudios detallados --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div>
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#8b5cf6;margin:0;">Estudios detallados</p>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:3px 0 0;">Opcional — agrega más datos para afinar coincidencias</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" x-on:click="$wire.agregarEscolaridad()" wire:loading.attr="disabled">+ Agregar estudio</button>
            </div>

            @if(count($escolaridad_detallada) > 0)
                <div style="display:grid;gap:10px;">
                    @foreach($escolaridad_detallada as $index => $estudio)
                        <div wire:key="estudio-{{ $index }}" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface-2);">
                            <div class="solicitud-grid-4" style="align-items:end;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nivel</label>
                                    <select class="form-input" wire:model="escolaridad_detallada.{{ $index }}.nivel">
                                        <option value="">Selecciona</option>
                                        @foreach($nivelesEstudio as $clave => $label)
                                            <option value="{{ $clave }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Institución / carrera</label>
                                    <input type="text" class="form-input" wire:model="escolaridad_detallada.{{ $index }}.nombre" placeholder="Institución o carrera">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Duración</label>
                                    <input type="text" class="form-input" wire:model="escolaridad_detallada.{{ $index }}.anios" placeholder="Años">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Título / especialidad</label>
                                    <input type="text" class="form-input" wire:model="escolaridad_detallada.{{ $index }}.titulo" placeholder="Título o especialidad">
                                </div>
                            </div>
                            <div style="text-align:right;margin-top:10px;">
                                <button type="button" class="btn btn-ghost btn-sm" x-on:click="$wire.eliminarEscolaridad({{ $index }})" wire:loading.attr="disabled" style="color:var(--danger);border-color:var(--danger-light);">Quitar</button>
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
