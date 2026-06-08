@php
    // Parametros generalizados: por defecto trabaja con $catalogo (servicios),
    // pero acepta cualquier $owner (ej. una Vacante) con sus propias rutas.
    $owner = $owner ?? $catalogo;
    $rutaStore = $rutaStore ?? 'admin.catalogo.recursos.store';
    $rutaUpdate = $rutaUpdate ?? 'admin.catalogo.recursos.update';
    $rutaDestroy = $rutaDestroy ?? 'admin.catalogo.recursos.destroy';
    $tituloSeccion = $tituloSeccion ?? 'Presentacion del servicio';
    $tieneTablaRecursos = $tieneTablaRecursos ?? \App\Models\CatalogoServicio::tieneTablaRecursos();

    if ($tieneTablaRecursos) {
        $owner->loadMissing(['recursos.subidoPor']);

        $recursos = $owner->recursos->sortBy([
            ['orden', 'asc'],
            ['created_at', 'asc'],
        ])->values();
    } else {
        $recursos = collect();
    }

    $slides = $recursos
        ->filter(fn ($recurso) => $recurso->esImagen())
        ->values();

    $recursosNoImagen = $recursos
        ->reject(fn ($recurso) => $recurso->esImagen())
        ->values();

    $usuario = auth()->user();
    $puedeGestionar = $puedeGestionar ?? ($usuario?->rol === 'admin');
    $presentacionActiva = (bool) ($owner->presentacion_activa ?? false);
    $siguienteOrden = (int) ($recursos->max('orden') ?? 0) + 1;
    $slidesData = $slides->map(fn ($recurso) => [
        'id' => $recurso->id,
        'titulo' => $recurso->titulo,
        'descripcion' => $recurso->descripcion,
        'url' => $recurso->url(),
        'thumb_url' => $recurso->thumbUrl(),
    ])->values();
@endphp

<div class="card servicio-recursos">
    <div class="servicio-recursos__head">
        <div>
            <h3 class="servicio-recursos__title">{{ $tituloSeccion }}</h3>
            <p class="servicio-recursos__text">
                Esta presentacion se arma con imagenes. Empresa y candidato la ven en vivo; el admin puede subirlas, ordenarlas y poner texto opcional debajo de cada diapositiva.
            </p>
        </div>
        <span class="badge {{ $presentacionActiva ? 'badge-blue' : 'badge-gray' }}">
            {{ $presentacionActiva ? $slides->count() . ' diapositiva(s)' : 'Presentacion desactivada' }}
        </span>
    </div>

    @if(! $tieneTablaRecursos)
        <div class="alert alert-warning" style="margin-bottom:16px;">
            La seccion de presentacion aun no esta disponible porque falta aplicar una migracion pendiente del sistema.
        </div>
    @endif

    @if($puedeGestionar && $tieneTablaRecursos)
        <section class="servicio-recursos__viewer" style="border-top:none; padding-top:0;">
            <div class="servicio-recursos__viewer-head">
                <div>
                    <h4 class="servicio-recursos__viewer-title">Armado rapido</h4>
                    <p class="servicio-recursos__viewer-text">
                        Sube capturas o laminas como imagenes. El sistema crea una diapositiva por cada imagen y despues puedes acomodarlas o agregar texto breve.
                    </p>
                </div>
                <span class="badge badge-gray">{{ $slides->count() }} imagen(es)</span>
            </div>

            <div class="servicio-recursos__admin-builder">
                <form
                    method="POST"
                    action="{{ route($rutaStore, $owner) }}"
                    enctype="multipart/form-data"
                    class="servicio-recursos__form"
                    x-data="{
                        arrastrando: false,
                        archivos: [],
                        limpiarPreviews() {
                            this.archivos.forEach((archivo) => {
                                if (archivo.url && archivo.url.startsWith('blob:')) {
                                    URL.revokeObjectURL(archivo.url);
                                }
                            });
                        },
                        leerArchivos(lista) {
                            this.limpiarPreviews();

                            this.archivos = Array.from(lista || []).map((archivo, indice) => ({
                                clave: `${archivo.name}-${indice}-${archivo.size}`,
                                url: URL.createObjectURL(archivo),
                            }));
                        },
                        cambiarArchivo(evento) {
                            this.leerArchivos(evento.target.files);
                        },
                        soltarArchivos(evento) {
                            this.arrastrando = false;

                            if (! evento.dataTransfer?.files?.length) {
                                return;
                            }

                            const transferencia = new DataTransfer();

                            Array.from(evento.dataTransfer.files).forEach((archivo) => transferencia.items.add(archivo));

                            this.$refs.archivos.files = transferencia.files;
                            this.leerArchivos(transferencia.files);
                        }
                    }"
                >
                    @csrf
                    <input type="hidden" name="tipo" value="presentacion">
                    <input type="hidden" name="modo_carga" value="archivo">

                    <div class="servicio-recursos__form-grid">
                        <div>
                            <label class="form-label">Titulo base</label>
                            <input type="text" name="titulo" class="form-input" maxlength="140" value="{{ old('titulo') }}" placeholder="Ej. induccion comercial o propuesta RH">
                            <p class="servicio-recursos__hint">Opcional. Si subes 6 imagenes, el sistema generara nombres como "Titulo base 1", "Titulo base 2" y asi sucesivamente.</p>
                            @error('titulo') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">Orden inicial</label>
                            <input type="number" name="orden" class="form-input" min="0" max="9999" value="{{ old('orden', $siguienteOrden) }}">
                            <p class="servicio-recursos__hint">La primera imagen entra con este orden y las demas siguen consecutivamente.</p>
                            @error('orden') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="servicio-recursos__span-2">
                            <label class="form-label">Imagenes de la presentacion</label>
                            <input
                                x-ref="archivos"
                                type="file"
                                name="archivos[]"
                                class="form-input"
                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                multiple
                                @change="cambiarArchivo($event)"
                                style="display:none;"
                            >

                            <button
                                type="button"
                                class="servicio-recursos__dropzone"
                                :class="{ 'is-drag': arrastrando }"
                                @click="$refs.archivos.click()"
                                @dragover.prevent="arrastrando = true"
                                @dragleave.prevent="arrastrando = false"
                                @drop.prevent="soltarArchivos($event)"
                            >
                                <span class="servicio-recursos__dropzone-badge">Arrastrar y soltar</span>
                                <strong>Sube varias imagenes de una sola vez</strong>
                                <p>Ideal para capturas de una presentacion ya hecha. Cada imagen se convertira en una diapositiva.</p>
                                <small>Formatos recomendados: JPG, PNG o WEBP.</small>
                            </button>

                            @error('archivos') <p class="form-error">{{ $message }}</p> @enderror
                            @error('archivos.*') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="servicio-recursos__preview" x-show="archivos.length" x-cloak>
                        <div class="servicio-recursos__preview-card">
                            <div class="servicio-recursos__preview-badge">Vista previa local</div>
                            <p class="servicio-recursos__preview-title" x-text="archivos.length === 1 ? '1 diapositiva lista para agregarse' : `${archivos.length} diapositivas listas para agregarse`"></p>
                            <p class="servicio-recursos__preview-subtitle">
                                Despues de subirlas podras editar titulo, texto opcional y orden individual de cada diapositiva.
                            </p>

                            <div class="servicio-recursos__preview-grid">
                                <template x-for="(archivo, indice) in archivos" :key="archivo.clave">
                                    <article class="servicio-recursos__preview-mini">
                                        <img :src="archivo.url" alt="Vista previa" class="servicio-recursos__preview-mini-media">
                                        <div class="servicio-recursos__preview-mini-body">
                                            <strong x-text="'Diapositiva ' + (indice + 1)"></strong>
                                            <small>Imagen lista</small>
                                        </div>
                                    </article>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="servicio-recursos__actions">
                        <button type="submit" class="btn btn-primary">Agregar diapositivas</button>
                    </div>
                </form>

                <div class="servicio-recursos__preview-card servicio-recursos__preview-card--list">
                    <div class="servicio-recursos__preview-badge">Diapositivas actuales</div>

                    @if($slides->isEmpty())
                        <p class="servicio-recursos__preview-subtitle">Aun no has agregado imagenes a esta presentacion.</p>
                    @else
                        <div style="display:grid; gap:12px;">
                            @foreach($slides as $recurso)
                                <details style="border:1px solid var(--border); border-radius:14px; background:#fff;">
                                    <summary style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:14px 16px; cursor:pointer; list-style:none;">
                                        <div style="min-width:0;">
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px;">
                                                <span class="badge badge-blue">#{{ $recurso->orden }}</span>
                                                <span class="badge badge-green">Imagen</span>
                                            </div>
                                            <strong style="display:block; color:var(--text-primary);">{{ $recurso->titulo }}</strong>
                                        </div>
                                        <span style="color:#94a3b8; font-size:12px;">Editar</span>
                                    </summary>

                                    <div style="padding:0 16px 16px; border-top:1px solid var(--border-light); display:grid; gap:14px;">
                                        <img src="{{ $recurso->url() }}" alt="{{ $recurso->titulo }}" class="servicio-recursos__preview-media" style="max-height:200px; margin-top:14px;">

                                        <form
                                            method="POST"
                                            action="{{ route($rutaUpdate, $recurso) }}"
                                            enctype="multipart/form-data"
                                            style="display:grid; gap:14px;"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="tipo" value="presentacion">
                                            <input type="hidden" name="modo_carga" value="archivo">

                                            <div class="servicio-recursos__form-grid">
                                                <div>
                                                    <label class="form-label">Titulo</label>
                                                    <input type="text" name="titulo" class="form-input" maxlength="140" required value="{{ $recurso->titulo }}">
                                                </div>

                                                <div>
                                                    <label class="form-label">Orden</label>
                                                    <input type="number" name="orden" class="form-input" min="0" max="9999" value="{{ $recurso->orden }}">
                                                </div>

                                                <div class="servicio-recursos__span-2">
                                                    <label class="form-label">Texto opcional</label>
                                                    <textarea
                                                        name="descripcion"
                                                        rows="3"
                                                        maxlength="2000"
                                                        class="form-input"
                                                        placeholder="Texto breve para mostrar debajo de esta imagen."
                                                        spellcheck="true"
                                                        autocorrect="on"
                                                        autocapitalize="sentences"
                                                        lang="es"
                                                    >{{ $recurso->descripcion }}</textarea>
                                                </div>

                                                <div class="servicio-recursos__span-2">
                                                    <label class="form-label">Reemplazar imagen</label>
                                                    <input type="file" name="archivo" class="form-input" accept=".jpg,.jpeg,.png,.webp,.gif">
                                                    <p class="servicio-recursos__hint">Si no subes nada, la imagen actual se conserva.</p>
                                                </div>
                                            </div>

                                            <div style="display:flex; justify-content:flex-end;">
                                                <button type="submit" class="btn btn-primary">Guardar diapositiva</button>
                                            </div>
                                        </form>

                                        <div style="display:flex; justify-content:flex-start;">
                                            <form method="POST" action="{{ route($rutaDestroy, $recurso) }}" onsubmit="return confirm('Eliminar esta diapositiva?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost" style="color:#dc2626;">Eliminar diapositiva</button>
                                            </form>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if($recursosNoImagen->isNotEmpty())
                <div class="alert alert-info" style="margin-top:16px;">
                    Hay {{ $recursosNoImagen->count() }} recurso(s) antiguo(s) que no son imagen. La presentacion nueva ya solo trabaja con imagenes.
                </div>
            @endif

            @if($slides->isNotEmpty())
                <div
                    class="presentacion-preview"
                    x-data="{
                        actual: 0,
                        total: {{ $slides->count() }},
                        slides: @js($slidesData),
                        irA(indice) {
                            if (indice < 0 || indice >= this.total) {
                                return;
                            }

                            this.actual = indice;
                        },
                        siguiente() {
                            if (this.total <= 1) {
                                return;
                            }

                            this.actual = (this.actual + 1) % this.total;
                        },
                        anterior() {
                            if (this.total <= 1) {
                                return;
                            }

                            this.actual = (this.actual - 1 + this.total) % this.total;
                        },
                        slideActual() {
                            return this.slides[this.actual] || null;
                        }
                    }"
                >
                    <div class="presentacion-preview__head">
                        <div>
                            <p class="presentacion-preview__label">Vista previa del admin</p>
                            <p class="servicio-recursos__hint" style="margin-top:4px;">
                                Aqui revisas como se vera la presentacion sin hacer enorme la pantalla de edicion.
                            </p>
                        </div>
                        <span class="presentacion-preview__counter" x-text="(actual + 1) + ' / ' + total"></span>
                    </div>

                    <div class="presentacion-preview__stage" x-show="slideActual()" x-cloak>
                        <img
                            :src="slideActual()?.url || ''"
                            :alt="slideActual()?.titulo || 'Diapositiva del servicio'"
                            class="presentacion-preview__img"
                        >
                    </div>

                    @if($slides->count() > 1)
                        <div class="presentacion-preview__controls">
                            <button type="button" class="presentacion-preview__btn" @click="anterior()" aria-label="Anterior">&lsaquo;</button>
                            <div class="presentacion-preview__thumbs">
                                <template x-for="(slide, indice) in slides" :key="slide.id">
                                    <button
                                        type="button"
                                        class="presentacion-preview__thumb"
                                        :class="{ 'is-active': actual === indice }"
                                        :aria-label="'Ver diapositiva ' + (indice + 1)"
                                        @click="irA(indice)"
                                    >
                                        <img :src="slide.thumb_url || slide.url" :alt="slide.titulo || ('Diapositiva ' + (indice + 1))">
                                    </button>
                                </template>
                            </div>
                            <button type="button" class="presentacion-preview__btn" @click="siguiente()" aria-label="Siguiente">&rsaquo;</button>
                        </div>
                    @endif

                    <div style="margin-top:12px; padding:14px 16px; border:1px solid var(--border); border-radius:12px; background:#fff;" x-show="slideActual()">
                        <strong style="display:block; color:var(--text-primary);" x-text="slideActual()?.titulo || ''"></strong>
                        <p
                            style="margin:8px 0 0; font-size:.84rem; line-height:1.6; color:var(--text-secondary); white-space:pre-wrap;"
                            x-show="slideActual()?.descripcion"
                            x-text="slideActual()?.descripcion || ''"
                        ></p>
                    </div>
                </div>
            @endif
        </section>
    @endif

    @if(! $puedeGestionar && $tieneTablaRecursos && $presentacionActiva && $slides->isNotEmpty())
        <div
            class="servicio-recursos__viewer"
            x-data="{
                actual: 0,
                total: {{ $slides->count() }},
                multiple: @js($slides->count() > 1),
                slides: @js($slidesData),
                autoplay: @js($slides->count() > 1),
                temporizador: null,
                iniciar() {
                    this.programar();
                },
                toggleAutoplay() {
                    this.autoplay = !this.autoplay;
                    this.programar();
                },
                programar() {
                    this.detener();

                    if (!this.multiple || !this.autoplay) {
                        return;
                    }

                    this.temporizador = window.setInterval(() => {
                        this.siguiente();
                    }, 4500);
                },
                detener() {
                    if (!this.temporizador) {
                        return;
                    }

                    window.clearInterval(this.temporizador);
                    this.temporizador = null;
                },
                irA(indice) {
                    if (indice < 0 || indice >= this.total) {
                        return;
                    }

                    this.actual = indice;
                },
                siguiente() {
                    if (!this.multiple) {
                        return;
                    }

                    this.actual = (this.actual + 1) % this.total;
                },
                anterior() {
                    if (!this.multiple) {
                        return;
                    }

                    this.actual = (this.actual - 1 + this.total) % this.total;
                },
                slideActual() {
                    return this.slides[this.actual] || null;
                }
            }"
            x-init="iniciar()"
            @mouseenter="detener()"
            @mouseleave="programar()"
            @keydown.right.window="multiple && siguiente()"
            @keydown.left.window="multiple && anterior()"
        >
            <div class="servicio-recursos__viewer-head">
                <div>
                    <h4 class="servicio-recursos__viewer-title">Vista en vivo</h4>
                    <p class="servicio-recursos__viewer-text">
                        Navega entre las diapositivas preparadas por el administrador. Puedes usar flechas, miniaturas y reproduccion automatica.
                    </p>
                </div>

                <div class="toolbar-wrap">
                    @if($slides->count() > 1)
                        <button type="button" class="btn btn-secondary btn-sm" @click="toggleAutoplay()" x-text="autoplay ? 'Pausar movimiento' : 'Reanudar movimiento'"></button>
                    @endif
                    <span class="badge badge-gray" x-text="(actual + 1) + ' / ' + total"></span>
                </div>
            </div>

            <div class="presentacion__main">
                <div class="presentacion__stage" x-show="slideActual()" x-cloak>
                    <img
                        :src="slideActual()?.url || ''"
                        :alt="slideActual()?.titulo || 'Diapositiva del servicio'"
                        class="presentacion__slide-img"
                        decoding="async"
                    >
                </div>

                @if($slides->count() > 1)
                    <button type="button" class="presentacion__nav presentacion__nav--prev" @click="anterior()">&lsaquo;</button>
                    <button type="button" class="presentacion__nav presentacion__nav--next" @click="siguiente()">&rsaquo;</button>
                    <div class="presentacion__dots">
                        <template x-for="(slide, indice) in slides" :key="'dot-' + slide.id">
                            <button
                                type="button"
                                class="presentacion__dot"
                                :class="{ 'is-active': actual === indice }"
                                :aria-label="'Ir a la diapositiva ' + (indice + 1)"
                                @click="irA(indice)"
                            ></button>
                        </template>
                    </div>
                @endif
            </div>

            <div class="presentacion__info" x-show="slideActual()">
                <h5 class="servicio-recursos__slide-title" x-text="slideActual()?.titulo || ''"></h5>
                <p class="servicio-recursos__slide-caption" x-show="slideActual()?.descripcion" x-text="slideActual()?.descripcion || ''"></p>
            </div>

            @if($slides->count() > 1)
                <div class="presentacion__thumbs">
                    <div class="presentacion__thumbs-track">
                        <template x-for="(slide, indice) in slides" :key="'thumb-' + slide.id">
                            <button
                                type="button"
                                class="presentacion__thumb-slide"
                                :class="{ 'is-active': actual === indice }"
                                :aria-label="'Ver diapositiva ' + (indice + 1)"
                                @click="irA(indice)"
                            >
                                <img :src="slide.thumb_url || slide.url" :alt="slide.titulo || ('Diapositiva ' + (indice + 1))" loading="lazy" decoding="async">
                            </button>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(! $puedeGestionar && $tieneTablaRecursos && ! $presentacionActiva)
        <div class="servicio-recursos__empty">
            <div class="servicio-recursos__empty-icon">OFF</div>
            <p>Este servicio no tiene activada una presentacion visual por el momento.</p>
        </div>
    @endif

    @if(! $puedeGestionar && $tieneTablaRecursos && $presentacionActiva && $slides->isEmpty())
        <div class="servicio-recursos__empty">
            <div class="servicio-recursos__empty-icon">IMG</div>
            <p>
                Aun no hay imagenes preparadas para este servicio.
                @if($recursosNoImagen->isNotEmpty())
                    Hay material anterior, pero esta nueva presentacion solo muestra imagenes.
                @endif
            </p>
        </div>
    @endif
</div>
