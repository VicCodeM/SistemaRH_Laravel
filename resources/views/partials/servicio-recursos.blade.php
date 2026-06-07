@php
    $servicio->loadMissing(['recursos.subidoPor']);

    $recursos = $servicio->recursos->sortBy([
        ['tipo', 'desc'],
        ['orden', 'asc'],
        ['created_at', 'asc'],
    ])->values();

    $presentaciones = $recursos->where('tipo', 'presentacion')->values();
    $archivos = $recursos->where('tipo', 'archivo')->values();
    $usuario = auth()->user();

    $puedeGestionar = $usuario?->rol === 'admin';
    $slideInicial = $presentaciones->first();
@endphp

<div
    class="card servicio-recursos"
    x-data="{
        slides: @js($presentaciones->pluck('id')->map(fn ($id) => (string) $id)->values()),
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
            <h3 class="servicio-recursos__title">Archivos y presentaciones</h3>
            <p class="servicio-recursos__text">
                El administrador sube aqui los archivos, PDF y presentaciones. Empresa y candidatos solo los consultan en esta misma pantalla.
            </p>
        </div>
        <span class="badge badge-blue">Material del servicio</span>
    </div>

    @if($puedeGestionar)
        <form
            method="POST"
            action="{{ route('pedidos.recursos.store', $servicio) }}"
            enctype="multipart/form-data"
            class="servicio-recursos__form"
            x-data="{
                previewUrl: null,
                previewTipo: '',
                previewNombre: '',
                cambiar(evento) {
                    const archivo = evento.target.files?.[0];

                    if (! archivo) {
                        if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(this.previewUrl);
                        }

                        this.previewUrl = null;
                        this.previewTipo = '';
                        this.previewNombre = '';
                        return;
                    }

                    if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                        URL.revokeObjectURL(this.previewUrl);
                    }

                    this.previewNombre = archivo.name;
                    this.previewTipo = archivo.type || '';
                    this.previewUrl = (archivo.type.startsWith('image/') || archivo.type === 'application/pdf')
                        ? URL.createObjectURL(archivo)
                        : null;
                }
            }"
        >
            @csrf

            <div class="servicio-recursos__form-grid">
                <div>
                    <label class="form-label">Titulo</label>
                    <input type="text" name="titulo" class="form-input" maxlength="140" required placeholder="Ej. presentacion comercial, contrato o PDF de apoyo">
                    @error('titulo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-input" required>
                        <option value="archivo">Archivo</option>
                        <option value="presentacion">Presentacion</option>
                    </select>
                    @error('tipo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="servicio-recursos__span-2">
                    <label class="form-label">Archivo</label>
                    <input
                        type="file"
                        name="archivo"
                        class="form-input"
                        required
                        accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.txt,.md,.csv,.doc,.docx,.ppt,.pptx"
                        @change="cambiar($event)"
                    >
                    <p class="servicio-recursos__hint">Para vista previa completa, sube PDF, imagen o archivo de texto simple.</p>
                    @error('archivo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="servicio-recursos__span-2">
                    <label class="form-label">Descripcion opcional</label>
                    <textarea
                        name="descripcion"
                        rows="3"
                        maxlength="2000"
                        class="form-input"
                        placeholder="Que contiene, para quien sirve, observaciones..."
                        spellcheck="true"
                        autocorrect="on"
                        autocapitalize="sentences"
                        lang="es"
                    ></textarea>
                    @error('descripcion') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="servicio-recursos__preview" x-show="previewNombre" x-cloak>
                <div class="servicio-recursos__preview-card">
                    <div class="servicio-recursos__preview-badge">Vista previa local</div>
                    <p class="servicio-recursos__preview-title" x-text="previewNombre"></p>
                    <p class="servicio-recursos__preview-subtitle" x-show="! previewUrl">
                        El archivo seleccionado no tiene vista previa completa en el navegador, pero si se guardara dentro del servicio.
                    </p>

                    <template x-if="previewUrl && previewTipo.startsWith('image/')">
                        <img :src="previewUrl" alt="Vista previa" class="servicio-recursos__preview-media">
                    </template>

                    <template x-if="previewUrl && previewTipo === 'application/pdf'">
                        <iframe :src="previewUrl" class="servicio-recursos__preview-media servicio-recursos__preview-media--pdf" title="Vista previa PDF"></iframe>
                    </template>
                </div>
            </div>

            <div class="servicio-recursos__actions">
                <button type="submit" class="btn btn-primary">Guardar recurso</button>
            </div>
        </form>
    @endif

    @if($presentaciones->isNotEmpty())
        <div class="servicio-recursos__viewer">
            <div class="servicio-recursos__viewer-head">
                <div>
                    <h4 class="servicio-recursos__viewer-title">Presentacion destacada</h4>
                    <p class="servicio-recursos__viewer-text">
                        Usa los botones para avanzar o retroceder entre laminas y recursos destacados.
                    </p>
                </div>

                @if($presentaciones->count() > 1)
                    <div class="toolbar-wrap">
                        <button type="button" class="btn btn-secondary btn-sm" @click="anterior()">Anterior</button>
                        <button type="button" class="btn btn-secondary btn-sm" @click="siguiente()">Siguiente</button>
                    </div>
                @endif
            </div>

            <div class="servicio-recursos__slide-stage">
                @foreach($presentaciones as $recurso)
                    @php
                        $textoPlano = null;

                        if ($recurso->esTexto() && \Illuminate\Support\Facades\Storage::disk('public')->exists($recurso->archivo_path)) {
                            $textoPlano = \Illuminate\Support\Facades\Storage::disk('public')->get($recurso->archivo_path);
                        }
                    @endphp

                    <section class="servicio-recursos__slide" x-show="activo === '{{ $recurso->id }}'" x-cloak>
                        <div class="servicio-recursos__slide-head">
                            <div>
                                <h5 class="servicio-recursos__slide-title">{{ $recurso->titulo }}</h5>
                                <div class="servicio-recursos__slide-meta">
                                    <span class="badge {{ $recurso->tipoBadgeClass() }}">{{ $recurso->tipoLabel() }}</span>
                                    <span>{{ $recurso->archivo_original }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $recurso->tamanoHumano() }}</span>
                                </div>
                            </div>
                        </div>

                        @if($recurso->esImagen())
                            <img src="{{ $recurso->url() }}" alt="{{ $recurso->titulo }}" class="servicio-recursos__slide-media">
                        @elseif($recurso->esPdf())
                            <iframe src="{{ $recurso->url() }}" class="servicio-recursos__slide-media servicio-recursos__slide-media--pdf" title="{{ $recurso->titulo }}"></iframe>
                        @elseif($recurso->esTexto())
                            <pre class="servicio-recursos__slide-text">{{ $textoPlano ?? 'No se pudo leer el contenido de este archivo.' }}</pre>
                        @else
                            <div class="servicio-recursos__slide-fallback">
                                <div class="servicio-recursos__slide-icon">{{ $recurso->icono() }}</div>
                                <p>
                                    Este formato no se puede mostrar en vivo. Si quieres vista directa en la web, subelo como PDF o imagen.
                                </p>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            @if($presentaciones->count() > 1)
                <div class="servicio-recursos__thumbnails">
                    @foreach($presentaciones as $recurso)
                        <button type="button" class="servicio-recursos__thumb" :class="{ 'is-active': activo === '{{ $recurso->id }}' }" @click="seleccionar('{{ $recurso->id }}')">
                            <span class="servicio-recursos__thumb-icon">{{ $recurso->icono() }}</span>
                            <span>
                                <strong>{{ $recurso->titulo }}</strong>
                                <small>{{ $recurso->tipoLabel() }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($archivos->isNotEmpty())
        <div class="servicio-recursos__files">
            <div class="servicio-recursos__files-head">
                <h4 class="servicio-recursos__viewer-title">Archivos adjuntos</h4>
                <span class="badge badge-gray">{{ $archivos->count() }} archivo(s)</span>
            </div>

            <div class="servicio-recursos__grid">
                @foreach($archivos as $recurso)
                    <article class="servicio-recurso-card">
                        <div class="servicio-recurso-card__icon">{{ $recurso->icono() }}</div>
                        <div class="servicio-recurso-card__body">
                            <div class="servicio-recurso-card__top">
                                <div>
                                    <h5>{{ $recurso->titulo }}</h5>
                                    <p>{{ $recurso->archivo_original }}</p>
                                </div>
                                <span class="badge {{ $recurso->tipoBadgeClass() }}">{{ $recurso->tipoLabel() }}</span>
                            </div>

                            @if($recurso->descripcion)
                                <p class="servicio-recurso-card__desc">{{ $recurso->descripcion }}</p>
                            @endif

                            <div class="servicio-recurso-card__meta">
                                <span>{{ $recurso->tamanoHumano() }}</span>
                                <span>&middot;</span>
                                <span>{{ $recurso->subidoPor?->name ?? 'Sistema' }}</span>
                                <span>&middot;</span>
                                <span>{{ $recurso->created_at?->format('d/m/Y') }}</span>
                            </div>

                            <div class="servicio-recurso-card__actions">
                                <a href="{{ $recurso->url() }}" target="_blank" class="btn btn-secondary btn-sm">Abrir</a>
                                @if($puedeGestionar)
                                    <form method="POST" action="{{ route('pedidos.recursos.destroy', $recurso) }}" onsubmit="return confirm('Eliminar este archivo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="color:#dc2626;">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    @if($recursos->isEmpty())
        <div class="servicio-recursos__empty">
            <div class="servicio-recursos__empty-icon">DOC</div>
            <p>Aun no hay archivos ni presentaciones para este servicio.</p>
        </div>
    @endif
</div>
