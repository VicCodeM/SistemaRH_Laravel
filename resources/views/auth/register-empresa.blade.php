<x-guest-layout max-width="800px">
    @php
        $pasos = [
            1 => [
                'titulo' => 'Acceso',
                'detalle' => 'Crea el acceso del responsable o contacto principal.',
            ],
            2 => [
                'titulo' => 'Empresa',
                'detalle' => 'Registra el nombre de la empresa y su identificación.',
            ],
            3 => [
                'titulo' => 'Ubicación',
                'detalle' => 'Agrega contacto, dirección, ciudad y giro para completar el alta.',
            ],
        ];

        $paso = $pasos[$step] ?? $pasos[1];
        $progreso = max(1, min(3, (int) $step));
        $avance = $progreso === 1 ? '33%' : ($progreso === 2 ? '66%' : '100%');
    @endphp

    <div style="display:grid; gap:20px;">
        <div style="text-align:center;">
            <div style="width:52px; height:52px; background:rgba(37,99,235,.12); border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2563eb" style="width:26px; height:26px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <h3 style="font-size:1.45rem; font-weight:800; margin-bottom:6px;">Registro de empresa</h3>
            <p class="text-muted" style="font-size:0.92rem; margin:0;">Hazlo en 3 pasos. Guardamos cada avance para que no tengas que repetir datos.</p>
        </div>

        <div style="background:linear-gradient(135deg, rgba(37,99,235,.08), rgba(168,85,247,.06)); border:1px solid rgba(148,163,184,.2); border-radius:16px; padding:16px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#64748b;">Paso actual</div>
                    <div style="font-size:1.1rem; font-weight:800; color:#0f172a;">Paso {{ $progreso }} de 3 · {{ $paso['titulo'] }}</div>
                    <p style="margin:4px 0 0; color:#64748b; font-size:0.88rem;">{{ $paso['detalle'] }}</p>
                </div>
                <div style="min-width:180px; flex:1;">
                    <div style="height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
                        <div style="height:100%; width:{{ $avance }}; background:linear-gradient(90deg, #2563eb, #7c3aed); border-radius:999px;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:0.75rem; color:#94a3b8;">
                        <span>Acceso</span>
                        <span>Empresa</span>
                        <span>Ubicación</span>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px; align-items:start;">
            <div class="card" style="padding:22px;">
                <form method="POST" action="{{ route('register.empresa') }}">
                    @csrf
                    <input type="hidden" name="step" value="{{ $progreso }}">

                    @if($progreso === 1)
                        <div style="margin-bottom:18px;">
                            <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">Datos de acceso</h4>
                            <p style="margin:0; color:#64748b; font-size:0.86rem;">Este acceso será del responsable que administrará la solicitud.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="name">Nombre del responsable</label>
                            <input id="name" class="form-input" type="text" name="name" value="{{ old('name', $wizardData['name'] ?? '') }}" required autofocus autocomplete="name" placeholder="Nombre de la persona de contacto" spellcheck="true" autocorrect="on" autocapitalize="words" lang="es-MX">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Correo de acceso</label>
                            <input id="email" class="form-input" type="email" name="email" value="{{ old('email', $wizardData['email'] ?? '') }}" required autocomplete="username" placeholder="correo@empresa.com">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                            <div class="form-group">
                                <label class="form-label" for="password">Contraseña</label>
                                <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                                @error('password') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                                <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite la contraseña">
                            </div>
                        </div>
                    @elseif($progreso === 2)
                        <div style="margin-bottom:18px;">
                            <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">Datos de la empresa</h4>
                            <p style="margin:0; color:#64748b; font-size:0.86rem;">Aquí distinguimos la empresa del usuario para que no se confundan los nombres.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nombre_empresa">Nombre de la empresa</label>
                            <input id="nombre_empresa" class="form-input" type="text" name="nombre_empresa" value="{{ old('nombre_empresa', $wizardData['nombre_empresa'] ?? '') }}" required placeholder="Nombre comercial o razón social corta" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            @error('nombre_empresa') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                            <div class="form-group">
                                <label class="form-label" for="razon_social">Razón social</label>
                                <input id="razon_social" class="form-input" type="text" name="razon_social" value="{{ old('razon_social', $wizardData['razon_social'] ?? '') }}" placeholder="Opcional" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                @error('razon_social') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="rfc">RFC</label>
                                <input id="rfc" class="form-input" type="text" name="rfc" value="{{ old('rfc', $wizardData['rfc'] ?? '') }}" placeholder="Opcional" maxlength="20" style="text-transform:uppercase;" spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                                @error('rfc') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="giro_o_industria">Giro o industria</label>
                            <input id="giro_o_industria" class="form-input" type="text" name="giro_o_industria" value="{{ old('giro_o_industria', $wizardData['giro_o_industria'] ?? '') }}" placeholder="Ej.: Manufactura, TI, Salud" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            @error('giro_o_industria') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div style="margin-bottom:18px;">
                            <h4 style="margin:0 0 4px; font-size:1rem; font-weight:800;">Ubicación y contacto</h4>
                            <p style="margin:0; color:#64748b; font-size:0.86rem;">Con estos datos el admin puede ubicarte y aprobar tu solicitud más rápido.</p>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                            <div class="form-group">
                                <label class="form-label" for="telefono">Teléfono</label>
                                <input id="telefono" class="form-input" type="text" name="telefono" value="{{ old('telefono', $wizardData['telefono'] ?? '') }}" placeholder="55 0000 0000" spellcheck="true" autocorrect="on" autocapitalize="off" lang="es-MX">
                                @error('telefono') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="ciudad">Ciudad</label>
                                <input id="ciudad" class="form-input" type="text" name="ciudad" value="{{ old('ciudad', $wizardData['ciudad'] ?? '') }}" placeholder="Ciudad, Estado" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                @error('ciudad') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="direccion">Dirección completa</label>
                            <input id="direccion" class="form-input" type="text" name="direccion" value="{{ old('direccion', $wizardData['direccion'] ?? '') }}" placeholder="Calle, número, colonia y referencias" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                            @error('direccion') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                            <div class="form-group">
                                <label class="form-label" for="municipio">Municipio</label>
                                <input id="municipio" class="form-input" type="text" name="municipio" value="{{ old('municipio', $wizardData['municipio'] ?? '') }}" placeholder="Municipio de la empresa" required spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX">
                                @error('municipio') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="codigo_postal">Código postal</label>
                                <input id="codigo_postal" class="form-input" type="text" name="codigo_postal" value="{{ old('codigo_postal', $wizardData['codigo_postal'] ?? '') }}" placeholder="Opcional" spellcheck="true" autocorrect="on" autocapitalize="none" lang="es-MX">
                                @error('codigo_postal') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:22px;">
                        <div style="font-size:0.84rem; color:#64748b;">
                            @if($progreso === 1)
                                Crea primero el acceso. Después capturamos la empresa.
                            @elseif($progreso === 2)
                                Separa el nombre del responsable del nombre de la empresa.
                            @else
                                Último paso. La empresa quedará pendiente de aprobación.
                            @endif
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            @if($progreso > 1)
                                <a href="{{ route('register.empresa', ['step' => $progreso - 1]) }}" class="btn btn-secondary">Anterior</a>
                            @endif
                            <button type="submit" class="btn btn-primary btn-lg" style="min-width:180px;">
                                {{ $progreso < 3 ? 'Siguiente' : 'Registrar empresa' }}
                            </button>
                        </div>
                    </div>

                    @if($progreso >= 3)
                        <label style="display:flex; align-items:flex-start; gap:8px; margin-top:16px; font-size:0.84rem; color:#475569; line-height:1.5; cursor:pointer;">
                            <input type="checkbox" name="acepta_terminos" value="1" {{ old('acepta_terminos') ? 'checked' : '' }} required style="margin-top:3px; width:16px; height:16px; flex-shrink:0;">
                            <span>
                                Acepto los
                                <a href="{{ route('paginas.terminos') }}" target="_blank" style="color:var(--accent);">Términos del servicio</a>
                                y la
                                <a href="{{ route('paginas.privacidad') }}" target="_blank" style="color:var(--accent);">Política de privacidad</a>
                                en nombre de la empresa.
                            </span>
                        </label>
                        @error('acepta_terminos')
                            <p style="margin:6px 0 0; font-size:0.8rem; color:#dc2626;">{{ $message }}</p>
                        @enderror
                    @endif
                </form>
            </div>

            <aside class="card" style="padding:18px; background:linear-gradient(180deg, rgba(37,99,235,.06), rgba(15,23,42,.03)); border:1px solid rgba(148,163,184,.16);">
                <h4 style="margin:0 0 10px; font-size:1rem; font-weight:800;">Así se entiende mejor</h4>
                <div style="display:grid; gap:10px; font-size:0.88rem; color:#475569; line-height:1.6;">
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">1. Responsable</strong>
                        El acceso pertenece a la persona que administrará la cuenta.
                    </div>
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">2. Empresa</strong>
                        El nombre comercial y la razón social quedan separados del usuario.
                    </div>
                    <div style="padding:10px 12px; border-radius:12px; background:#fff; border:1px solid rgba(148,163,184,.18);">
                        <strong style="display:block; color:#0f172a; margin-bottom:2px;">3. Ubicación</strong>
                        El admin puede revisarte rápido y aprobarte sin pedir lo mismo dos veces.
                    </div>
                </div>

                <div style="margin-top:16px; padding:12px; border-radius:12px; background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.18); color:#1e3a8a; font-size:0.84rem; line-height:1.5;">
                    Tu empresa quedará pendiente de aprobación después del envío. El equipo interno la revisará.
                </div>
            </aside>
        </div>

        <div class="divider"></div>

        <div class="text-center" style="font-size:0.88rem; color:#64748b;">
            ¿Buscas empleo?
            <a href="{{ route('register.candidato') }}" style="color:var(--accent); font-weight:600; text-decoration:none;">Regístrate como candidato</a>
            &nbsp;·&nbsp;
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" style="color:var(--accent); font-weight:600; text-decoration:none;">Inicia sesión</a>
        </div>
    </div>
</x-guest-layout>
