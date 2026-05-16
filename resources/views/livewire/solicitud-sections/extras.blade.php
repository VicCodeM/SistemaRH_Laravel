@php
    $pillBase  = "padding:8px 22px;border-radius:8px;border:1.5px solid;font-size:.84rem;font-weight:700;cursor:pointer;transition:all .18s;font-family:var(--font);";
    $pillOn    = "background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(37,99,235,.28);";
    $pillOff   = "background:#fff;color:var(--text-muted);border-color:var(--border);";
    $pillDanger = "background:#fff;color:var(--danger);border-color:var(--danger);";

    $licenciaTiene = $licencia_conducir['tiene'] ?? '';
@endphp

<div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);margin-top:6px;">
    <div style="padding:18px 24px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#fff1f2,#f8fafc);">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(244,63,94,.12);color:#f43f5e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        </div>
        <div>
            <h3 style="margin:0;font-size:.95rem;font-weight:700;color:var(--text);">Documentos y datos extras</h3>
            <p style="margin:2px 0 0;font-size:.78rem;color:var(--text-muted);">CURP, NSS, documentos, licencia, redes y referencias</p>
        </div>
    </div>

    <div style="padding:22px 24px;display:grid;gap:20px;">

        {{-- ── DOCUMENTOS OFICIALES ── --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f43f5e;margin:0 0 10px;">Documentos oficiales</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">CURP</label>
                    <input type="text" class="form-input" wire:model.blur="curp" placeholder="CURP (18 caracteres)">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">NSS — Número de seguro social</label>
                    <input type="text" class="form-input" wire:model.blur="nore_seguro_social" placeholder="Número de seguro social">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">RFC</label>
                    <input type="text" class="form-input" wire:model.blur="rfc" placeholder="RFC">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Afore</label>
                    <input type="text" class="form-input" wire:model.blur="afore" placeholder="Nombre de tu Afore">
                </div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);"></div>

        {{-- ── CARTILLA MILITAR ── --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f43f5e;margin:0 0 10px;">Cartilla militar</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $cartilla_tiene === 'si' ? '12px' : '0' }};">
                    <label class="form-label">¿Tienes cartilla militar?</label>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="button" wire:click="setCartillaTiene('si')"
                            style="{{ $cartilla_tiene === 'si' ? $pillOn : $pillOff }}{{ $pillBase }}">
                            Sí, tengo
                        </button>
                        <button type="button" wire:click="setCartillaTiene('no')"
                            style="{{ $cartilla_tiene === 'no' ? 'background:#f0fdf4;color:#10b981;border-color:#10b981;box-shadow:0 2px 8px rgba(16,185,129,.2);' : $pillOff }}{{ $pillBase }}">
                            No tengo
                        </button>
                    </div>
                </div>
                @if($cartilla_tiene === 'si')
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Número de cartilla</label>
                        <input type="text" class="form-input" wire:model.blur="cartilla_militar" placeholder="Número de cartilla militar">
                    </div>
                @endif
            </div>
        </div>

        <div style="height:1px;background:var(--border);"></div>

        {{-- ── PASAPORTE ── --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f43f5e;margin:0 0 10px;">Pasaporte</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $pasaporte_tiene === 'si' ? '12px' : '0' }};">
                    <label class="form-label">¿Tienes pasaporte?</label>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="button" wire:click="setPasaporteTiene('si')"
                            style="{{ $pasaporte_tiene === 'si' ? $pillOn : $pillOff }}{{ $pillBase }}">
                            Sí, tengo
                        </button>
                        <button type="button" wire:click="setPasaporteTiene('no')"
                            style="{{ $pasaporte_tiene === 'no' ? 'background:#f0fdf4;color:#10b981;border-color:#10b981;box-shadow:0 2px 8px rgba(16,185,129,.2);' : $pillOff }}{{ $pillBase }}">
                            No tengo
                        </button>
                    </div>
                </div>
                @if($pasaporte_tiene === 'si')
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Número de pasaporte</label>
                        <input type="text" class="form-input" wire:model.blur="pasaporte" placeholder="Número de pasaporte">
                    </div>
                @endif
            </div>
        </div>

        <div style="height:1px;background:var(--border);"></div>

        {{-- ── LICENCIA DE CONDUCIR ── --}}
        <div>
            <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f43f5e;margin:0 0 10px;">Licencia de conducir</p>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;">
                <div class="form-group" style="margin:0 0 {{ $licenciaTiene === 'si' ? '14px' : '0' }};">
                    <label class="form-label">¿Tienes licencia de conducir?</label>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="button" wire:click="setLicenciaTiene('si')"
                            style="{{ $licenciaTiene === 'si' ? $pillOn : $pillOff }}{{ $pillBase }}">
                            Sí, tengo
                        </button>
                        <button type="button" wire:click="setLicenciaTiene('no')"
                            style="{{ $licenciaTiene === 'no' ? 'background:#f0fdf4;color:#10b981;border-color:#10b981;box-shadow:0 2px 8px rgba(16,185,129,.2);' : $pillOff }}{{ $pillBase }}">
                            No tengo
                        </button>
                    </div>
                </div>
                @if($licenciaTiene === 'si')
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Clase</label>
                            <input type="text" class="form-input" wire:model.blur="licencia_conducir.clase" placeholder="Ej. A, B, C">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-input" wire:model.blur="licencia_conducir.numero" placeholder="Número de licencia">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Vigencia</label>
                            <input type="date" class="form-input" wire:model.blur="licencia_conducir.vigencia">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div style="height:1px;background:var(--border);"></div>

        {{-- ── REFERENCIAS PERSONALES ── --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div>
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#f43f5e;margin:0;">Referencias personales</p>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:3px 0 0;">Opcional — personas que puedan dar referencias de ti</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="agregarReferencia">+ Agregar referencia</button>
            </div>

            @if(count($referencias_personales) > 0)
                <div style="display:grid;gap:10px;">
                    @foreach($referencias_personales as $index => $referencia)
                        <div wire:key="referencia-{{ $index }}" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface-2);">
                            <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr 1fr;gap:10px;margin-bottom:10px;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-input" wire:model.blur="referencias_personales.{{ $index }}.nombre" placeholder="Nombre completo">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-input" wire:model.blur="referencias_personales.{{ $index }}.telefono" placeholder="Teléfono">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Ocupación</label>
                                    <input type="text" class="form-input" wire:model.blur="referencias_personales.{{ $index }}.ocupacion" placeholder="Ocupación">
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Tiempo de conocerlo</label>
                                    <input type="text" class="form-input" wire:model.blur="referencias_personales.{{ $index }}.tiempo" placeholder="Ej. 3 años">
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;align-items:flex-end;">
                                <div class="form-group" style="margin:0;flex:1;">
                                    <label class="form-label">Domicilio</label>
                                    <input type="text" class="form-input" wire:model.blur="referencias_personales.{{ $index }}.domicilio" placeholder="Domicilio de la referencia">
                                </div>
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="eliminarReferencia({{ $index }})" style="color:var(--danger);border-color:var(--danger-light);flex-shrink:0;">Quitar</button>
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
