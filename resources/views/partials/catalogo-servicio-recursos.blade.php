@php
    $tieneTablaRecursos = \App\Models\CatalogoServicio::tieneTablaRecursos();

    if ($tieneTablaRecursos) {
        $catalogo->loadMissing(['recursos.subidoPor']);

        $recursos = $catalogo->recursos->sortBy([
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
    $slideInicial = $slides->first();
    $siguienteOrden = (int) ($recursos->max('orden') ?? 0) + 1;
@endphp

<div
    class="card servicio-recursos"
    x-data="{
        slides: @js($slides->pluck('id')->map(fn ($id) => (string) $id)->values()),
        activo: '{{ $slideInicial?->id ? (string) $slideInicial->id : '' }}',
        anterior() {
            if (! this.slides.length) return;
            const indice = this.slides.indexOf(this.activo);
            const siguiente = indice <= 0 ? this.slides.length - 1 : indice - 1;
            this.activo = this.slides[siguiente];
        },
        siguiente() {
            if (! this.slides.length) return;
            const indice = this.slides.indexOf(this.activo);
            const siguiente = indice === -1 || indice >= this.slides.length - 1 ? 0 : indice + 1;
            this.activo = this.slides[siguiente];
        },
        seleccionar(id) {
            this.activo = String(id);
        }
    }"
>
    <div class="servicio-recursos__head">
        <div>
            <h3 class="servicio-recursos__title">Presentacion del servicio</h3>
            <p class="servicio-recursos__text">
                Esta presentacion se arma con imagenes. Empresa y candidato la ven en vivo; el admin puede subirlas, ordenarlas y poner texto opcional debajo de cada diapositiva.
            </p>
        </div>
        <span class="badge badge-blue">{{ $slides->count() }} diapositiva(s)</span>
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

            <div style="display:grid; grid-template-columns:minmax(0, 1.05fr) minmax(0, .95fr); gap:16px;">
                <form
                    method="POST"
                    action="{{ route('admin.catalogo.recursos.store', $catalogo) }}"
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
                                nombre: archivo.name,
                                url: URL.createObjectURL(archivo),
                                tamano: archivo.size ? `${Math.max(1, Math.round(archivo.size / 1024))} KB` : '',
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
                            <p class="servicio-recursos__preview-title" x-text="archivos.length === 1 ? archivos[0].nombre : `${archivos.length} diapositivas listas para agregarse`"></p>
                            <p class="servicio-recursos__preview-subtitle">
                                Despues de subirlas podras editar titulo, texto opcional y orden individual de cada diapositiva.
                            </p>

                            <div class="servicio-recursos__preview-grid">
                                <template x-for="archivo in archivos" :key="archivo.clave">
                                    <article class="servicio-recursos__preview-mini">
                                        <img :src="archivo.url" alt="Vista previa" class="servicio-recursos__preview-mini-media">
                                        <div class="servicio-recursos__preview-mini-body">
                                            <strong x-text="archivo.nombre"></strong>
                                            <small x-text="archivo.tamano || 'Imagen lista'"></small>
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

                <div class="servicio-recursos__preview-card" style="align-self:start;">
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
                                            <small style="display:block; margin-top:4px; color:#64748b; word-break:break-word;">
                                                {{ $recurso->archivo_original }} @if($recurso->tamano_bytes) &middot; {{ $recurso->tamanoHumano() }} @endif
                                            </small>
                                        </div>
                                        <span style="color:#94a3b8; font-size:12px;">Editar</span>
                                    </summary>

                                    <div style="padding:0 16px 16px; border-top:1px solid var(--border-light); display:grid; gap:14px;">
                                        <img src="{{ $recurso->url() }}" alt="{{ $recurso->titulo }}" class="servicio-recursos__preview-media" style="max-height:200px; margin-top:14px;">

                                        <form
                                            method="POST"
                                            action="{{ route('admin.catalogo.recursos.update', $recurso) }}"
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
                                            <form method="POST" action="{{ route('admin.catalogo.recursos.destroy', $recurso) }}" onsubmit="return confirm('Eliminar esta diapositiva?')">
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
        </section>
    @endif

    @if($tieneTablaRecursos && $slides->isNotEmpty())
        <div class="servicio-recursos__viewer">
            <div class="servicio-recursos__viewer-head">
                <div>
                    <h4 class="servicio-recursos__viewer-title">Vista en vivo</h4>
                    <p class="servicio-recursos__viewer-text">
                        Navega entre las diapositivas preparadas por el administrador.
                    </p>
                </div>

                @if($slides->count() > 1)
                    <div class="toolbar-wrap">
                        <button type="button" class="btn btn-secondary btn-sm" @click="anterior()">Anterior</button>
                        <button type="button" class="btn btn-secondary btn-sm" @click="siguiente()">Siguiente</button>
                    </div>
                @endif
            </div>

            <div class="servicio-recursos__slide-stage">
                @foreach($slides as $recurso)
                    <section class="servicio-recursos__slide" x-show="activo === '{{ $recurso->id }}'" x-cloak>
                        <div class="servicio-recursos__slide-head">
                            <div>
                                <h5 class="servicio-recursos__slide-title">{{ $recurso->titulo }}</h5>
                                <div class="servicio-recursos__slide-meta">
                                    <span class="badge badge-green">Imagen</span>
                                    <span>{{ $recurso->archivo_original }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $recurso->tamanoHumano() }}</span>
                                </div>
                            </div>
                        </div>

                        <img src="{{ $recurso->url() }}" alt="{{ $recurso->titulo }}" class="servicio-recursos__slide-media">

                        @if($recurso->descripcion)
                            <p class="servicio-recursos__slide-caption">{{ $recurso->descripcion }}</p>
                        @endif
                    </section>
                @endforeach
            </div>

            @if($slides->count() > 1)
                <div class="servicio-recursos__thumbnails">
                    @foreach($slides as $recurso)
                        <button type="button" class="servicio-recursos__thumb" :class="{ 'is-active': activo === '{{ $recurso->id }}' }" @click="seleccionar('{{ $recurso->id }}')">
                            <span class="servicio-recursos__thumb-icon">IMG</span>
                            <span>
                                <strong>{{ $recurso->titulo }}</strong>
                                <small>{{ $recurso->archivo_original }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($tieneTablaRecursos && $slides->isEmpty())
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
