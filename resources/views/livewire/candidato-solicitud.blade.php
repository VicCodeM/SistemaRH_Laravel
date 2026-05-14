<div>
    @if($yaEnviada)
        <div class="card fade-in" style="text-align: center; padding: 48px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">🎉</div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">¡Solicitud Enviada!</h2>
            <p class="text-muted" style="margin-bottom: 24px;">Tu solicitud está siendo evaluada.</p>
            <a wire:navigate href="{{ route('dashboard') }}" class="btn btn-primary">Ir al Dashboard</a>
        </div>
    @else
        <div class="card fade-in">
            <div class="flex items-center justify-between" style="border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 24px;">
                <h3 style="font-size: 1.15rem; font-weight: 600;">Solicitud de Empleo</h3>
                <span class="text-muted text-sm font-medium">Paso {{ $pasoActual }} de 6</span>
            </div>

            @if($pasoActual == 1)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">1. Datos Personales</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <div class="form-group"><label class="form-label">Nombre(s)</label><input type="text" wire:model="nombre" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Apellido Paterno</label><input type="text" wire:model="apellido_paterno" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Apellido Materno</label><input type="text" wire:model="apellido_materno" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Fecha de Nacimiento</label><input type="date" wire:model="fecha_nacimiento" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Edad</label><input type="number" wire:model="edad" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Sexo</label>
                            <select wire:model="sexo" class="form-input">
                                <option value="">Seleccione</option><option value="M">Masculino</option><option value="F">Femenino</option><option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Lugar de Nacimiento</label><input type="text" wire:model="lugar_nacimiento" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Nacionalidad</label><input type="text" wire:model="nacionalidad" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Estado Civil</label>
                            <select wire:model="estado_civil" class="form-input">
                                <option value="">Seleccione</option><option value="Soltero(a)">Soltero(a)</option><option value="Casado(a)">Casado(a)</option><option value="Unión Libre">Unión Libre</option>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Peso (kg)</label><input type="text" wire:model="peso" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Estatura (m)</label><input type="text" wire:model="estatura" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Vive Con</label><input type="text" wire:model="vive_con" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3;"><label class="form-label">Dependientes</label><input type="text" wire:model="dependientes" class="form-input"></div>
                    </div>
                </div>
            @endif

            @if($pasoActual == 2)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">2. Contacto y Domicilio</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group"><label class="form-label">Teléfono</label><input type="text" wire:model="telefono" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Celular</label><input type="text" wire:model="celular" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 2;"><label class="form-label">Domicilio</label><input type="text" wire:model="domicilio" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Colonia</label><input type="text" wire:model="colonia" class="form-input"></div>
                        <div class="form-group"><label class="form-label">C.P.</label><input type="text" wire:model="codigo_postal" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Municipio</label><input type="text" wire:model="municipio" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Ciudad / Estado</label><input type="text" wire:model="ciudad" class="form-input"></div>
                    </div>
                </div>
            @endif

            @if($pasoActual == 3)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">3. Documentación y Redes</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <div class="form-group"><label class="form-label">CURP</label><input type="text" wire:model="curp" class="form-input"></div>
                        <div class="form-group"><label class="form-label">RFC</label><input type="text" wire:model="rfc" class="form-input"></div>
                        <div class="form-group"><label class="form-label">NSS</label><input type="text" wire:model="nore_seguro_social" class="form-input"></div>
                        <div class="form-group"><label class="form-label">AFORE</label><input type="text" wire:model="afore" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Cartilla Militar</label><input type="text" wire:model="cartilla_militar" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Pasaporte</label><input type="text" wire:model="pasaporte" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3; padding-top: 8px;"><label class="form-label font-semibold">Licencia</label></div>
                        <div class="form-group"><label class="form-label">¿Tiene?</label><select wire:model="licencia_conducir.tiene" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">Clase</label><input type="text" wire:model="licencia_conducir.clase" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Vigencia</label><input type="text" wire:model="licencia_conducir.vigencia" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3; padding-top: 8px;"><label class="form-label font-semibold">Redes Sociales</label></div>
                        <div class="form-group"><label class="form-label">Facebook</label><input type="text" wire:model="redes_sociales.facebook" class="form-input"></div>
                        <div class="form-group"><label class="form-label">LinkedIn</label><input type="text" wire:model="redes_sociales.linkedin" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Instagram</label><input type="text" wire:model="redes_sociales.instagram" class="form-input"></div>
                    </div>
                </div>
            @endif

            @if($pasoActual == 4)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">4. Salud y Conocimientos</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="grid-column: span 3;"><label class="form-label font-semibold">Salud</label></div>
                        <div class="form-group"><label class="form-label">Estado</label><select wire:model="estado_salud.estado" class="form-input"><option value="">-</option><option value="Bueno">Bueno</option><option value="Regular">Regular</option><option value="Malo">Malo</option></select></div>
                        <div class="form-group"><label class="form-label">¿Enfermedad Crónica?</label><select wire:model="estado_salud.cronica_tiene" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">¿Cuál?</label><input type="text" wire:model="estado_salud.cronica_detalle" class="form-input"></div>
                        <div class="form-group"><label class="form-label">¿Fuma?</label><select wire:model="estado_salud.fuma" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">¿Bebe?</label><select wire:model="estado_salud.bebe" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">Deporte</label><input type="text" wire:model="estado_salud.deporte" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3;"><label class="form-label">Meta en la vida</label><input type="text" wire:model="estado_salud.meta" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3;"><label class="form-label font-semibold">Conocimientos</label></div>
                        <div class="form-group"><label class="form-label">Idiomas</label><input type="text" wire:model="conocimientos_generales.idiomas" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Software</label><input type="text" wire:model="conocimientos_generales.software" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Máquinas</label><input type="text" wire:model="conocimientos_generales.maquinas" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 3;"><label class="form-label font-semibold">Disponibilidad</label></div>
                        <div class="form-group"><label class="form-label">¿Viajar?</label><select wire:model="datos_generales.viajar" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">¿Cambio residencia?</label><select wire:model="datos_generales.cambio_residencia" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                        <div class="form-group"><label class="form-label">Fecha disponible</label><input type="date" wire:model="datos_generales.fecha_disponible" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Deudas mensuales</label><input type="text" wire:model="datos_economicos.deudas" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Gastos mensuales</label><input type="text" wire:model="datos_economicos.gastos_mensuales" class="form-input"></div>
                        <div class="form-group"><label class="form-label">¿Auto propio?</label><select wire:model="datos_economicos.auto_propio" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                    </div>
                </div>
            @endif

            @if($pasoActual == 5)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">5. Familia y Escolaridad</h4>
                    <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; margin-bottom: 16px;">
                        <h5 style="font-weight: 600; margin-bottom: 12px;">Datos Familiares</h5>
                        @foreach(['Padre', 'Madre', 'Esposa(o)'] as $familiar)
                        <div style="display: grid; grid-template-columns: 2fr 1fr 2fr; gap: 12px; margin-bottom: 8px;">
                            <div><label class="form-label">{{ $familiar }}</label><input type="text" wire:model="datos_familiares.{{ $familiar }}.nombre" class="form-input"></div>
                            <div><label class="form-label">¿Vive?</label><select wire:model="datos_familiares.{{ $familiar }}.vive" class="form-input"><option value="">-</option><option value="Si">Sí</option><option value="No">No</option></select></div>
                            <div><label class="form-label">Ocupación</label><input type="text" wire:model="datos_familiares.{{ $familiar }}.ocupacion" class="form-input"></div>
                        </div>
                        @endforeach
                        <div style="margin-top: 8px;"><label class="form-label">Hijos</label><textarea wire:model="datos_familiares.hijos" class="form-input" rows="2"></textarea></div>
                    </div>
                    <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px;">
                        <div class="flex items-center justify-between mb-4">
                            <h5 style="font-weight: 600; margin: 0;">Escolaridad</h5>
                            <button wire:click="agregarEscolaridad" class="btn btn-secondary btn-sm">+ Agregar</button>
                        </div>
                        @foreach($escolaridad_detallada as $index => $escuela)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; margin-bottom: 8px; position: relative;">
                            @if(count($escolaridad_detallada) > 1)
                                <button wire:click="eliminarEscolaridad({{ $index }})" style="position: absolute; top: 12px; right: 12px; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1rem;">✕</button>
                            @endif
                            <div style="display: grid; grid-template-columns: 1fr 2fr 1fr 2fr; gap: 12px;">
                                <div><label class="form-label">Nivel</label>
                                    <select wire:model="escolaridad_detallada.{{ $index }}.nivel" class="form-input">
                                        <option value="">-</option>
                                        <option value="Primaria">Primaria</option>
                                        <option value="Secundaria">Secundaria</option>
                                        <option value="Preparatoria">Preparatoria</option>
                                        <option value="Profesional">Profesional</option>
                                        <option value="Maestría">Maestría</option>
                                        <option value="Curso/Diplomado">Curso/Diplomado</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div><label class="form-label">Escuela</label><input type="text" wire:model="escolaridad_detallada.{{ $index }}.nombre" class="form-input"></div>
                                <div><label class="form-label">Años</label><input type="text" wire:model="escolaridad_detallada.{{ $index }}.anios" class="form-input"></div>
                                <div><label class="form-label">Título</label><input type="text" wire:model="escolaridad_detallada.{{ $index }}.titulo" class="form-input"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pasoActual == 6)
                <div class="fade-in">
                    <h4 style="font-weight: 600; color: var(--accent); margin-bottom: 16px;">6. Perfil y Experiencia</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                        <div class="form-group"><label class="form-label">Puesto Deseado</label><input type="text" wire:model="puesto_deseado" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Sueldo Deseado</label><input type="text" wire:model="sueldo_deseado" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Años Exp.</label><input type="number" wire:model="experiencia_anios" class="form-input"></div>
                        <div class="form-group"><label class="form-label">Escolaridad (resumen)</label><input type="text" wire:model="escolaridad" class="form-input"></div>
                        <div class="form-group" style="grid-column: span 2;"><label class="form-label">Habilidades</label><textarea wire:model="habilidades" class="form-input" rows="2"></textarea></div>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h5 style="font-weight: 600; margin: 0;">Historial Laboral</h5>
                        <button wire:click="agregarEmpleo" class="btn btn-secondary btn-sm">+ Agregar</button>
                    </div>
                    @foreach($historial_laboral as $index => $empleo)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; margin-bottom: 8px; position: relative;">
                            @if(count($historial_laboral) > 1)
                                <button wire:click="eliminarEmpleo({{ $index }})" style="position: absolute; top: 12px; right: 12px; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1rem;">✕</button>
                            @endif
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                                <div><label class="form-label">Empresa</label><input type="text" wire:model="historial_laboral.{{ $index }}.empresa" class="form-input"></div>
                                <div><label class="form-label">Puesto</label><input type="text" wire:model="historial_laboral.{{ $index }}.puesto" class="form-input"></div>
                                <div><label class="form-label">Jefe</label><input type="text" wire:model="historial_laboral.{{ $index }}.jefe" class="form-input"></div>
                                <div><label class="form-label">Sueldo</label><input type="text" wire:model="historial_laboral.{{ $index }}.sueldo" class="form-input"></div>
                                <div><label class="form-label">Desde</label><input type="text" wire:model="historial_laboral.{{ $index }}.desde" class="form-input"></div>
                                <div><label class="form-label">Hasta</label><input type="text" wire:model="historial_laboral.{{ $index }}.hasta" class="form-input"></div>
                                <div style="grid-column: span 3;"><label class="form-label">Motivo de Salida</label><input type="text" wire:model="historial_laboral.{{ $index }}.motivo" class="form-input"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between mb-4" style="margin-top: 24px;">
                        <h5 style="font-weight: 600; margin: 0;">Referencias Personales</h5>
                        <button wire:click="agregarReferencia" class="btn btn-secondary btn-sm">+ Agregar</button>
                    </div>
                    @foreach($referencias_personales as $index => $ref)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; margin-bottom: 8px; position: relative;">
                            @if(count($referencias_personales) > 1)
                                <button wire:click="eliminarReferencia({{ $index }})" style="position: absolute; top: 12px; right: 12px; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1rem;">✕</button>
                            @endif
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div><label class="form-label">Nombre</label><input type="text" wire:model="referencias_personales.{{ $index }}.nombre" class="form-input"></div>
                                <div><label class="form-label">Teléfono</label><input type="text" wire:model="referencias_personales.{{ $index }}.telefono" class="form-input"></div>
                                <div><label class="form-label">Ocupación</label><input type="text" wire:model="referencias_personales.{{ $index }}.ocupacion" class="form-input"></div>
                                <div><label class="form-label">Domicilio / Tiempo</label><input type="text" wire:model="referencias_personales.{{ $index }}.domicilio" class="form-input"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex items-center justify-between" style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 24px;">
                @if($pasoActual > 1)
                    <button wire:click="pasoAnterior" class="btn btn-secondary">← Atrás</button>
                @else
                    <div></div>
                @endif
                @if($pasoActual < 6)
                    <button wire:click="siguientePaso" class="btn btn-primary">Siguiente →</button>
                @else
                    <button wire:click="enviarSolicitud" class="btn btn-success">Enviar Solicitud</button>
                @endif
            </div>

            <div wire:loading class="text-center mt-4">
                <span class="text-muted text-sm">Guardando...</span>
            </div>
        </div>
    @endif
</div>
