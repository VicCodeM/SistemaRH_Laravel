@php
    $nivelesEstudio    = \App\Models\Vacante::nivelesEstudios();
    $progresoSolicitud = $this->progresoSolicitud();
    $secciones         = $this->seccionesCompletadas();
    $puedeEnviar       = $this->puedeEnviarSolicitud();

    $tabs = [
        'personales' => ['numero' => 1, 'titulo' => 'Personales',  'detalle' => 'Datos de identificación'],
        'contacto'   => ['numero' => 2, 'titulo' => 'Contacto',    'detalle' => 'Cómo localizarte'],
        'estudios'   => ['numero' => 3, 'titulo' => 'Escolaridad', 'detalle' => 'Estudios y perfil'],
        'laboral'    => ['numero' => 4, 'titulo' => 'Experiencia', 'detalle' => 'Historial laboral'],
        'extras'     => ['numero' => 5, 'titulo' => 'Extras',      'detalle' => 'CURP y documentos'],
    ];

    $btnLocked = "display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:.82rem;font-weight:600;background:#f1f5f9;color:#94a3b8;cursor:not-allowed;border:1.5px solid #e2e8f0;";
    $lockIcon  = '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>';
@endphp

{{-- SIN x-cloak en el div raíz: cabecera y pestañas siempre visibles --}}
<div x-data="{
    tab: 'personales',
    order: ['personales','contacto','estudios','laboral','extras'],
    goTo(t) { this.tab = t; window.scrollTo({top:0,behavior:'smooth'}); },
    next(c) { const i = this.order.indexOf(c); if(i < this.order.length-1) this.goTo(this.order[i+1]); },
    prev(c) { const i = this.order.indexOf(c); if(i > 0) this.goTo(this.order[i-1]); },
    isActive(k) { return this.tab === k; },
}">

    {{-- ═══ CABECERA ═══ --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:6px;box-shadow:var(--shadow-sm);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#fff"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--text);">{{ $modoAdmin ? 'Editar solicitud' : 'Mi solicitud' }}</h2>
                    <p style="margin:2px 0 0;font-size:.78rem;color:var(--text-muted);">{{ $modoAdmin ? 'Modo administrativo — los cambios se guardan automáticamente' : 'Completa los 5 pasos para enviar tu solicitud' }}</p>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <span class="badge {{ $yaEnviada ? 'badge-success' : 'badge-warning' }}">{{ $yaEnviada ? 'Enviada' : 'Borrador' }}</span>
                <span style="padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;background:{{ $puedeEnviar ? 'var(--success-light)' : 'var(--accent-light)' }};color:{{ $puedeEnviar ? 'var(--success)' : 'var(--accent)' }};">
                    {{ $progresoSolicitud }}%
                </span>
            </div>
        </div>
        <div style="height:6px;background:var(--border);border-radius:99px;overflow:hidden;">
            <div style="height:100%;width:{{ $progresoSolicitud }}%;border-radius:99px;transition:width .4s ease;
                background:{{ $puedeEnviar ? 'linear-gradient(90deg,#10b981,#34d399)' : 'linear-gradient(90deg,#2563eb,#7c3aed)' }};"></div>
        </div>
    </div>

    {{-- ═══ PESTAÑAS DE NAVEGACIÓN ═══ --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:14px 20px;margin-bottom:6px;box-shadow:var(--shadow-sm);">
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @foreach($tabs as $key => $tabData)
                @php $done = $secciones[$key]; @endphp
                <button type="button"
                    @click="goTo('{{ $key }}')"
                    style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:10px;border:2px solid;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .18s;font-family:var(--font);
                        {{ $done
                            ? 'background:#ecfdf5;border-color:#10b981;color:#10b981;'
                            : 'background:#f8fafc;border-color:var(--border);color:var(--text-muted);' }}"
                    :style="isActive('{{ $key }}')
                        ? 'background:#eff6ff;border-color:var(--accent);color:var(--accent);'
                        : ''">

                    {{-- Número / check badge --}}
                    <span style="width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:800;flex-shrink:0;
                        {{ $done
                            ? 'background:#10b981;color:#fff;'
                            : 'background:#cbd5e1;color:#fff;' }}"
                        :style="isActive('{{ $key }}') ? 'background:var(--accent);color:#fff;' : ''">
                        @if($done)
                            <span x-show="isActive('{{ $key }}')">{{ $tabData['numero'] }}</span>
                            <span x-show="!isActive('{{ $key }}')">✓</span>
                        @else
                            {{ $tabData['numero'] }}
                        @endif
                    </span>

                    {{ $tabData['titulo'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('exito'))<div class="alert alert-success" style="margin-bottom:6px;">{{ session('exito') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="margin-bottom:6px;">{{ session('error') }}</div>@endif
    @error('solicitud')<div class="alert alert-danger" style="margin-bottom:6px;">{{ $message }}</div>@enderror
    @if($yaEnviada && !$modoAdmin)
        <div class="alert alert-info" style="margin-bottom:6px;">Tu solicitud fue enviada. Puedes seguir corrigiendo datos antes de que sea revisada.</div>
    @endif
    @if($accesoPendiente && !$modoAdmin)
        <div class="alert alert-warning" style="margin-bottom:6px;">Tu acceso está en revisión. Cuando sea aprobado podrás completar tu solicitud.</div>
    @endif

    @if($accesoPendiente && !$modoAdmin)
        <div style="padding:28px;border:1px dashed var(--border);border-radius:12px;background:var(--surface-2);color:var(--text-muted);text-align:center;">
            Tu perfil no está habilitado aún. Cuando seas aprobado podrás completar tu expediente.
        </div>
    @else

    {{-- ═══ PANELES POR PASO ═══ --}}
    {{-- x-cloak solo en cada panel para evitar flash; la barra de pestañas ya es visible --}}

    {{-- Sin x-cloak en paneles: conflicta con morphdom de Livewire al re-renderizar --}}
    {{-- style="display:none" en los paneles no-default para evitar flash inicial   --}}

    <div x-show="tab === 'personales'">
        @include('livewire.solicitud-sections.personales')
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
            @if($secciones['personales'])
                <button type="button" class="btn btn-primary" @click="next('personales')">Continuar →</button>
            @else
                <button type="button" disabled title="Completa todos los campos de esta sección" style="{{ $btnLocked }}">{!! $lockIcon !!} Completa esta sección</button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'contacto'" style="display:none;">
        @include('livewire.solicitud-sections.contacto')
        <div style="display:flex;justify-content:space-between;gap:10px;margin-top:16px;">
            <button type="button" class="btn btn-ghost" @click="prev('contacto')">← Atrás</button>
            @if($secciones['contacto'])
                <button type="button" class="btn btn-primary" @click="next('contacto')">Continuar →</button>
            @else
                <button type="button" disabled title="Completa todos los campos de esta sección" style="{{ $btnLocked }}">{!! $lockIcon !!} Completa esta sección</button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'estudios'" style="display:none;">
        @include('livewire.solicitud-sections.estudios')
        <div style="display:flex;justify-content:space-between;gap:10px;margin-top:16px;">
            <button type="button" class="btn btn-ghost" @click="prev('estudios')">← Atrás</button>
            @if($secciones['estudios'])
                <button type="button" class="btn btn-primary" @click="next('estudios')">Continuar →</button>
            @else
                <button type="button" disabled title="Completa todos los campos de esta sección" style="{{ $btnLocked }}">{!! $lockIcon !!} Completa esta sección</button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'laboral'" style="display:none;">
        @include('livewire.solicitud-sections.laboral')
        <div style="display:flex;justify-content:space-between;gap:10px;margin-top:16px;">
            <button type="button" class="btn btn-ghost" @click="prev('laboral')">← Atrás</button>
            @if($secciones['laboral'])
                <button type="button" class="btn btn-primary" @click="next('laboral')">Continuar →</button>
            @else
                <button type="button" disabled title="Completa todos los campos de esta sección" style="{{ $btnLocked }}">{!! $lockIcon !!} Completa esta sección</button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'extras'" style="display:none;">
        @include('livewire.solicitud-sections.extras')

        {{-- ═══ RESUMEN DE VALIDACIÓN ═══ --}}
        @if(!$modoAdmin && !$puedeEnviar)
            @php $faltantes = $this->camposRequeridos(); @endphp
            <div style="margin-top:16px;background:#fff;border:1.5px solid var(--warning);border-radius:14px;overflow:hidden;">
                <div style="padding:14px 18px;background:var(--warning-light);display:flex;align-items:center;gap:10px;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">La solicitud no puede enviarse aún — faltan campos obligatorios</span>
                </div>
                <div style="padding:16px 18px;display:grid;gap:10px;">
                    @foreach($faltantes as $seccion => $campos)
                        @php
                            $labels = ['personales'=>'Datos personales','contacto'=>'Contacto','estudios'=>'Escolaridad','laboral'=>'Experiencia','extras'=>'Documentos'];
                        @endphp
                        <div>
                            <button type="button"
                                @click="goTo('{{ $seccion }}')"
                                style="display:flex;align-items:center;gap:8px;background:none;border:none;cursor:pointer;font-family:var(--font);padding:0;margin-bottom:6px;">
                                <span style="width:20px;height:20px;border-radius:50%;background:var(--danger-light);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">!</span>
                                <span style="font-size:.8rem;font-weight:700;color:var(--danger);text-decoration:underline;">{{ $labels[$seccion] ?? $seccion }} →</span>
                            </button>
                            <ul style="margin:0;padding-left:28px;list-style:disc;">
                                @foreach($campos as $campo)
                                    <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ═══ BOTONES FINALES ═══ --}}
        <div style="display:flex;justify-content:space-between;gap:10px;margin-top:16px;">
            <button type="button" class="btn btn-ghost" @click="prev('extras')">← Atrás</button>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-secondary" wire:click="guardarBorrador" wire:loading.attr="disabled">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0z"/></svg>
                    Guardar avance
                </button>

                @if(!$modoAdmin)
                    @if($puedeEnviar)
                        <button type="button" class="btn btn-primary" wire:click="enviarSolicitud" wire:loading.attr="disabled"
                            style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,.3);">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                            Enviar solicitud
                        </button>
                    @else
                        <button type="button" disabled
                            title="Completa todos los campos obligatorios para enviar"
                            style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:500;background:#e2e8f0;color:#94a3b8;cursor:not-allowed;border:1px solid var(--border);">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                            Enviar solicitud
                        </button>
                    @endif
                @else
                    <button type="button" class="btn btn-primary" wire:click="guardarBorrador" wire:loading.attr="disabled">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                        Guardar cambios
                    </button>
                @endif
            </div>
        </div>
    </div>

    @endif
</div>
