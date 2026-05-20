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
@endphp

<div x-data="{
    tab: localStorage.getItem('solicitud_tab_{{ $candidatoId ?? Auth::id() ?? 0 }}') || 'personales',
    order: ['personales','contacto','estudios','laboral','extras'],
    secciones: JSON.parse($el.dataset.secciones || '{}'),
    init() {
        // Observar cambios en data-secciones cuando Livewire re-renderiza
        const observer = new MutationObserver(() => {
            this.secciones = JSON.parse(this.$el.dataset.secciones || '{}');
        });
        observer.observe(this.$el, { attributes: true, attributeFilter: ['data-secciones'] });

        // Escuchar evento de Livewire para cambiar de pestaña
        Livewire.on('cambiarPestana', (data) => {
            this.goTo(data[0]?.pestana || data.pestana);
        });
    },
    goTo(t) {
        const idx = this.order.indexOf(t);
        if (idx > 0 && !this.secciones[this.order[idx-1]]) {
            return;
        }
        this.tab = t;
        localStorage.setItem('solicitud_tab_{{ $candidatoId ?? Auth::id() ?? 0 }}', t);
        window.scrollTo({top:0, behavior:'smooth'});
    },
    next() {
        const i = this.order.indexOf(this.tab);
        if (i < this.order.length - 1) this.goTo(this.order[i+1]);
    },
    prev() {
        const i = this.order.indexOf(this.tab);
        if (i > 0) this.goTo(this.order[i-1]);
    },
    isActive(k) { return this.tab === k; },
    isLocked(k) {
        const idx = this.order.indexOf(k);
        return idx > 0 && !this.secciones[this.order[idx-1]];
    },
    async guardar() {
        await $wire.guardarBorrador();
    }
}" data-secciones='@json($secciones)' x-on:cambiar-pestana.window="goTo($event.detail.pestana)"
    x-on:change="
        const el = $event.target;
        const modelo = el.getAttribute('wire:model');
        if (modelo && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT')) {
            const isLive = el.hasAttribute('wire:model.live') || el.getAttribute('wire:model')?.includes('.live');
            const isRadio = el.type === 'radio' || el.type === 'checkbox';
            if (!isLive && !isRadio) {
                $wire.set(modelo, el.value).then(() => $wire.$refresh());
            }
        }
    ">

    {{-- ═══ CABECERA ═══ --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:10px;box-shadow:var(--shadow-sm);">
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

    {{-- ═══ STEPPER / PESTAÑAS ═══ --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 16px;margin-bottom:10px;box-shadow:var(--shadow-sm);">
        <div class="wizard-stepper-wrap">
            <div class="stepper">
                @foreach($tabs as $key => $tabData)
                    @php
                        $done = $secciones[$key];
                        $locked = !$this->pestanaDesbloqueada($key);
                    @endphp
                    <div class="stepper-step">
                        <button type="button"
                            @click="goTo('{{ $key }}')"
                            :class="tab === '{{ $key }}' ? 'step-circle-active' : ''"
                            class="step-circle {{ $done ? 'step-circle-done' : '' }} {{ $locked ? 'step-circle-locked' : '' }}">
                            @if($done)
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                <span style="font-size:.8rem;font-weight:700;">{{ $tabData['numero'] }}</span>
                            @endif
                        </button>
                        <span class="step-label" :class="tab === '{{ $key }}' ? 'step-label-active' : ''">{{ $tabData['titulo'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <div class="step-line {{ $done ? 'step-line-done' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('exito'))<div class="alert alert-success" style="margin-bottom:10px;">{{ session('exito') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="margin-bottom:10px;">{{ session('error') }}</div>@endif
    @error('solicitud')<div class="alert alert-danger" style="margin-bottom:10px;">{{ $message }}</div>@enderror

    {{-- Toast de notificación --}}
    <div x-data="{ show: false, mensaje: '', tipo: 'success' }"
         x-on:notificacion.window="mensaje = $event.detail.mensaje; tipo = $event.detail.tipo; show = true; setTimeout(() => show = false, 4000);"
         x-show="show" x-transition
         class="wizard-toast"
         x-cloak>
        <div style="padding:14px 18px;border-radius:12px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(0,0,0,.18);"
             :style="tipo === 'success' ? 'background:#ecfdf5;color:#065f46;border:1px solid #10b981;' : 'background:#fef2f2;color:#991b1b;border:1px solid #ef4444;'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path x-show="tipo === 'success'" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path x-show="tipo !== 'success'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <span x-text="mensaje"></span>
        </div>
    </div>
    @if($yaEnviada && !$modoAdmin)
        <div class="alert alert-info" style="margin-bottom:10px;">Tu solicitud fue enviada. Puedes seguir corrigiendo datos antes de que sea revisada.</div>
    @endif
    @if($accesoPendiente && !$modoAdmin)
        <div class="alert alert-warning" style="margin-bottom:10px;">Tu acceso está en revisión. Cuando sea aprobado podrás completar tu solicitud.</div>
    @endif

    @if($accesoPendiente && !$modoAdmin)
        <div style="padding:28px;border:1px dashed var(--border);border-radius:12px;background:var(--surface-2);color:var(--text-muted);text-align:center;">
            Tu perfil no está habilitado aún. Cuando seas aprobado podrás completar tu expediente.
        </div>
    @else

    {{-- ═══ PANELES POR PASO ═══ --}}
    <div x-show="tab === 'personales'" x-cloak>
        @include('livewire.solicitud-sections.personales')
        @php $faltantesPersonales = $this->camposFaltantesPorSeccion('personales'); @endphp
        @if(!empty($faltantesPersonales))
            <div style="margin-top:12px;padding:12px 16px;background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">Faltan campos obligatorios:</span>
                </div>
                <ul style="margin:0;padding-left:24px;list-style:disc;">
                    @foreach($faltantesPersonales as $campo)
                        <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="wizard-actions">
            @if($secciones['personales'])
                <button type="button" class="btn btn-primary" @click="next()">Continuar →</button>
            @else
                <button type="button" class="btn-locked" wire:click="verificarYAvanzar('contacto')" wire:loading.attr="disabled">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                    Verificar y continuar
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'contacto'" x-cloak>
        @include('livewire.solicitud-sections.contacto')
        @php $faltantesContacto = $this->camposFaltantesPorSeccion('contacto'); @endphp
        @if(!empty($faltantesContacto))
            <div style="margin-top:12px;padding:12px 16px;background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">Faltan campos obligatorios:</span>
                </div>
                <ul style="margin:0;padding-left:24px;list-style:disc;">
                    @foreach($faltantesContacto as $campo)
                        <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="wizard-actions-between">
            <button type="button" class="btn btn-ghost" @click="prev()">← Atrás</button>
            @if($secciones['contacto'])
                <button type="button" class="btn btn-primary" @click="next()">Continuar →</button>
            @else
                <button type="button" class="btn-locked" wire:click="verificarYAvanzar('estudios')" wire:loading.attr="disabled">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                    Verificar y continuar
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'estudios'" x-cloak>
        @include('livewire.solicitud-sections.estudios')
        @php $faltantesEstudios = $this->camposFaltantesPorSeccion('estudios'); @endphp
        @if(!empty($faltantesEstudios))
            <div style="margin-top:12px;padding:12px 16px;background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">Faltan campos obligatorios:</span>
                </div>
                <ul style="margin:0;padding-left:24px;list-style:disc;">
                    @foreach($faltantesEstudios as $campo)
                        <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="wizard-actions-between">
            <button type="button" class="btn btn-ghost" @click="prev()">← Atrás</button>
            @if($secciones['estudios'])
                <button type="button" class="btn btn-primary" @click="next()">Continuar →</button>
            @else
                <button type="button" class="btn-locked" wire:click="verificarYAvanzar('laboral')" wire:loading.attr="disabled">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                    Verificar y continuar
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'laboral'" x-cloak>
        @include('livewire.solicitud-sections.laboral')
        @php $faltantesLaboral = $this->camposFaltantesPorSeccion('laboral'); @endphp
        @if(!empty($faltantesLaboral))
            <div style="margin-top:12px;padding:12px 16px;background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">Faltan campos obligatorios:</span>
                </div>
                <ul style="margin:0;padding-left:24px;list-style:disc;">
                    @foreach($faltantesLaboral as $campo)
                        <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="wizard-actions-between">
            <button type="button" class="btn btn-ghost" @click="prev()">← Atrás</button>
            @if($secciones['laboral'])
                <button type="button" class="btn btn-primary" @click="next()">Continuar →</button>
            @else
                <button type="button" class="btn-locked" wire:click="verificarYAvanzar('extras')" wire:loading.attr="disabled">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                    Verificar y continuar
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'extras'" x-cloak>
        @include('livewire.solicitud-sections.extras')
        @php $faltantesExtras = $this->camposFaltantesPorSeccion('extras'); @endphp
        @if(!empty($faltantesExtras))
            <div style="margin-top:12px;padding:12px 16px;background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span style="font-size:.82rem;font-weight:700;color:#92400e;">Faltan campos obligatorios:</span>
                </div>
                <ul style="margin:0;padding-left:24px;list-style:disc;">
                    @foreach($faltantesExtras as $campo)
                        <li style="font-size:.78rem;color:#92400e;">{{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
        <div class="wizard-actions-between">
            <button type="button" class="btn btn-ghost" @click="prev()">← Atrás</button>
            <div class="wizard-actions-end">
                <button type="button" class="btn btn-secondary" x-on:click="guardar()" :class="!$wire.tieneCambios ? 'btn-disabled' : ''" :disabled="!$wire.tieneCambios">
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
                        <button type="button" disabled class="btn-locked"
                            title="Completa todos los campos obligatorios para enviar">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                            Enviar solicitud
                        </button>
                    @endif
                @else
                    <button type="button" class="btn btn-primary" x-on:click="guardar()" :class="!$wire.tieneCambios ? 'btn-disabled' : ''" :disabled="!$wire.tieneCambios">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                        Guardar cambios
                    </button>
                @endif
            </div>
        </div>
    </div>

    @endif

    {{-- Estilos del stepper --}}
    <style>
    .stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 4px;
        min-width: max-content;
    }
    .stepper-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
    }
    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: #fff;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s ease;
        padding: 0;
        font-family: var(--font);
    }
    .step-circle:hover:not(.step-circle-locked):not(.step-circle-done) {
        border-color: var(--accent);
        color: var(--accent);
    }
    .step-circle-active {
        border-color: var(--accent) !important;
        background: var(--accent) !important;
        color: #fff !important;
        box-shadow: 0 0 0 4px rgba(37,99,235,.15);
    }
    .step-circle-done {
        border-color: #10b981;
        background: #10b981;
        color: #fff;
    }
    .step-circle-locked {
        opacity: .5;
        cursor: not-allowed;
    }
    .step-label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--text-muted);
        text-align: center;
        white-space: nowrap;
        transition: color .2s;
    }
    .step-label-active {
        color: var(--accent);
        font-weight: 700;
    }
    .step-line {
        flex: 1 1 auto;
        height: 2px;
        background: #e2e8f0;
        margin-top: 18px;
        min-width: 20px;
        border-radius: 1px;
        transition: background .3s;
    }
    .step-line-done {
        background: #10b981;
    }
    .btn-locked {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        border: 1.5px solid #e2e8f0;
    }
    .btn-disabled {
        opacity: .55;
        cursor: not-allowed;
        font-family: var(--font);
    }
    @media (max-width: 640px) {
        .step-label { display: none; }
        .step-circle { width: 32px; height: 32px; }
        .step-line { margin-top: 15px; min-width: 8px; }
    }
    [x-cloak] { display: none !important; }
    </style>
</div>
