<?php

namespace App\Livewire;

use App\Models\Candidato;
use App\Models\ConfiguracionSistema;
use App\Services\CandidatoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CandidatoSolicitud extends Component
{
    private CandidatoService $candidatoService;

    public function boot(CandidatoService $candidatoService): void
    {
        $this->candidatoService = $candidatoService;
    }

    public ?int $candidatoId = null;
    public ?int $usuarioId = null;
    public bool $yaEnviada = false;
    public bool $modoAdmin = false;
    public bool $requiereAprobacion = false;
    public bool $accesoPendiente = false;
    public string $estadoSolicitud = 'borrador';
    public bool $tieneCambios = false;

    public $nombre = '';
    public $apellido_paterno = '';
    public $apellido_materno = '';
    public $edad = null;
    public $sexo = '';
    public $fecha_nacimiento = '';
    public $lugar_nacimiento = '';
    public $nacionalidad = '';
    public $peso = '';
    public $estatura = '';
    public $estado_civil = '';
    public $vive_con = '';
    public $dependientes = '';

    public $telefono = '';
    public $celular = '';
    public $domicilio = '';
    public $colonia = '';
    public $codigo_postal = '';
    public $municipio = '';
    public $ciudad = '';

    public $curp = '';
    public $nore_seguro_social = '';
    public $rfc = '';
    public $afore = '';
    public $cartilla_militar = '';
    public $pasaporte = '';

    public string $cartilla_tiene = '';
    public string $pasaporte_tiene = '';

    public $experiencia_anios = 0;
    public $puesto_deseado = '';
    public $escolaridad = '';
    public $sueldo_deseado = '';
    public $habilidades = '';

    public array $licencia_conducir = [
        'tiene' => '',
        'clase' => '',
        'numero' => '',
        'vigencia' => '',
    ];

    public array $redes_sociales = [
        'facebook' => '',
        'twitter' => '',
        'instagram' => '',
        'linkedin' => '',
    ];

    public array $escolaridad_detallada = [];
    public array $historial_laboral = [];
    public array $referencias_personales = [];

    public function mount(?int $candidatoId = null, bool $modoAdmin = false): void
    {
        $this->modoAdmin = $modoAdmin;
        $this->requiereAprobacion = ConfiguracionSistema::boolean('candidato_requiere_aprobacion', false);

        $candidato = $candidatoId
            ? Candidato::with('usuario')->find($candidatoId)
            : $this->candidatoService->obtenerCandidatoPorUsuario(Auth::id());

        if (! $candidato && ! $this->modoAdmin) {
            $candidato = $this->candidatoService->guardarBorrador(Auth::id(), null, [
                'usuario_id' => Auth::id(),
                'nombre' => Auth::user()?->name ?? '',
                'solicitud_estado' => 'borrador',
            ]);
        }

        if ($candidato) {
            $this->candidatoId = $candidato->id;
            $this->usuarioId = $candidato->usuario_id;

            foreach ([
                'nombre', 'apellido_paterno', 'apellido_materno', 'edad', 'sexo',
                'fecha_nacimiento', 'lugar_nacimiento', 'nacionalidad',
                'peso', 'estatura', 'estado_civil', 'vive_con', 'dependientes',
                'telefono', 'celular', 'domicilio', 'colonia', 'codigo_postal',
                'municipio', 'ciudad', 'curp', 'nore_seguro_social', 'rfc',
                'afore', 'cartilla_militar', 'pasaporte', 'experiencia_anios',
                'puesto_deseado', 'escolaridad', 'sueldo_deseado', 'habilidades',
            ] as $campo) {
                if ($campo === 'fecha_nacimiento' && $candidato->$campo) {
                    $this->$campo = $candidato->$campo->format('Y-m-d');
                    continue;
                }

                $this->$campo = $candidato->$campo ?? $this->$campo;
            }

            $this->licencia_conducir = array_merge($this->licencia_conducir, is_array($candidato->licencia_conducir) ? $candidato->licencia_conducir : []);
            $this->redes_sociales = array_merge($this->redes_sociales, is_array($candidato->redes_sociales) ? $candidato->redes_sociales : []);

            $this->escolaridad_detallada = is_array($candidato->escolaridad_detallada) ? array_values($candidato->escolaridad_detallada) : [];
            $this->historial_laboral = is_array($candidato->historial_laboral) ? array_values($candidato->historial_laboral) : [];
            $this->referencias_personales = is_array($candidato->referencias_personales) ? array_values($candidato->referencias_personales) : [];

            $this->cartilla_tiene = $this->campoLleno($this->cartilla_militar) ? 'si' : '';
            $this->pasaporte_tiene = $this->campoLleno($this->pasaporte) ? 'si' : '';

            $this->yaEnviada = $candidato->solicitud_estado !== 'borrador';
            $this->estadoSolicitud = $candidato->solicitud_estado ?? 'borrador';
        } else {
            $this->nombre = Auth::user()?->name ?? '';
            $this->usuarioId = Auth::id();
        }

        $this->accesoPendiente = $this->requiereAprobacion
            && ! $this->modoAdmin
            && Auth::user()?->estado !== 'activo';

        if (empty($this->escolaridad_detallada)) {
            $this->agregarEscolaridad();
        }

        if (empty($this->historial_laboral)) {
            $this->agregarEmpleo();
        }

        if (empty($this->referencias_personales)) {
            $this->agregarReferencia();
        }
    }

    public function agregarEscolaridad(): void
    {
        $ultimo = end($this->escolaridad_detallada);
        if ($ultimo && (! $this->campoLleno($ultimo['nivel'] ?? '') || ! $this->campoLleno($ultimo['nombre'] ?? ''))) {
            $this->addError('solicitud', 'Completa el nivel y la institución del último estudio antes de agregar otro.');
            return;
        }

        $this->escolaridad_detallada = array_values([...$this->escolaridad_detallada, [
            'nivel' => '',
            'nombre' => '',
            'anios' => '',
            'titulo' => '',
        ]]);
        $this->tieneCambios = true;
        $this->autoGuardar();
    }

    public function eliminarEscolaridad(int $index): void
    {
        $nuevo = $this->escolaridad_detallada;
        array_splice($nuevo, $index, 1);
        $this->escolaridad_detallada = array_values($nuevo);
        $this->autoGuardar();
    }

    public function agregarEmpleo(): void
    {
        $ultimo = end($this->historial_laboral);
        if ($ultimo && (! $this->campoLleno($ultimo['empresa'] ?? '') || ! $this->campoLleno($ultimo['puesto'] ?? ''))) {
            $this->addError('solicitud', 'Completa la empresa y el puesto del último empleo antes de agregar otro.');
            return;
        }

        $this->historial_laboral = array_values([...$this->historial_laboral, [
            'empresa' => '',
            'puesto' => '',
            'jefe' => '',
            'sueldo' => '',
            'desde' => '',
            'hasta' => '',
            'motivo' => '',
        ]]);
        $this->tieneCambios = true;
        $this->autoGuardar();
    }

    public function eliminarEmpleo(int $index): void
    {
        $nuevo = $this->historial_laboral;
        array_splice($nuevo, $index, 1);
        $this->historial_laboral = array_values($nuevo);
        $this->autoGuardar();
    }

    public function agregarReferencia(): void
    {
        $ultima = end($this->referencias_personales);
        if ($ultima && (! $this->campoLleno($ultima['nombre'] ?? '') || ! $this->campoLleno($ultima['telefono'] ?? ''))) {
            $this->addError('solicitud', 'Completa el nombre y teléfono de la última referencia antes de agregar otra.');
            return;
        }

        $this->referencias_personales = array_values([...$this->referencias_personales, [
            'nombre' => '',
            'telefono' => '',
            'ocupacion' => '',
            'tiempo' => '',
            'domicilio' => '',
        ]]);
        $this->tieneCambios = true;
        $this->autoGuardar();
    }

    public function eliminarReferencia(int $index): void
    {
        $nuevo = $this->referencias_personales;
        array_splice($nuevo, $index, 1);
        $this->referencias_personales = array_values($nuevo);
        $this->autoGuardar();
    }

    public function updated(string $property): void
    {
        $this->tieneCambios = true;

        // Limpieza condicional al cambiar selecciones Sí/No
        if ($property === 'cartilla_tiene' && $this->cartilla_tiene === 'no') {
            $this->cartilla_militar = '';
        }
        if ($property === 'pasaporte_tiene' && $this->pasaporte_tiene === 'no') {
            $this->pasaporte = '';
        }
        if ($property === 'licencia_conducir.tiene' && ($this->licencia_conducir['tiene'] ?? '') === 'no') {
            $this->licencia_conducir['clase']    = '';
            $this->licencia_conducir['numero']   = '';
            $this->licencia_conducir['vigencia'] = '';
        }

        $this->autoGuardar();
    }

    public function guardarBorrador(): void
    {
        $this->autoGuardar();
        $this->tieneCambios = false;
        $this->dispatch('notificacion', mensaje: 'Solicitud guardada correctamente.', tipo: 'success');
    }

    private function autoGuardar(): void
    {
        $datos = $this->getDatos();
        $candidato = $this->candidatoService->guardarBorrador($this->usuarioId ?? Auth::id(), $this->candidatoId, $datos);
        $this->candidatoId = $candidato->id;
        $this->usuarioId   = $candidato->usuario_id;
    }

    public function puedeEnviarSolicitud(): bool
    {
        return $this->solicitudCompleta();
    }

    public function seccionesCompletadas(): array
    {
        return [
            'personales' => $this->seccionPersonalesCompleta(),
            'contacto'   => $this->seccionContactoCompleta(),
            'estudios'   => $this->seccionEstudiosCompleta(),
            'laboral'    => $this->seccionLaboralCompleta(),
            'extras'     => $this->seccionExtrasCompleta(),
        ];
    }

    public function camposRequeridos(): array
    {
        $faltantes = [];

        if (! $this->seccionPersonalesCompleta()) {
            $campos = [];
            if (! $this->campoLleno($this->nombre))           $campos[] = 'Nombre(s)';
            if (! $this->campoLleno($this->apellido_paterno)) $campos[] = 'Apellido paterno';
            if (! $this->campoLleno($this->apellido_materno)) $campos[] = 'Apellido materno';
            if (! $this->campoLleno($this->edad))             $campos[] = 'Edad';
            if (! $this->campoLleno($this->sexo))             $campos[] = 'Sexo';
            if (! $this->campoLleno($this->fecha_nacimiento)) $campos[] = 'Fecha de nacimiento';
            if (! $this->campoLleno($this->lugar_nacimiento)) $campos[] = 'Lugar de nacimiento';
            if (! $this->campoLleno($this->nacionalidad))     $campos[] = 'Nacionalidad';
            if (! $this->campoLleno($this->estado_civil))     $campos[] = 'Estado civil';
            if (! $this->campoLleno($this->vive_con))         $campos[] = 'Vive con';
            if (! $this->campoLleno($this->peso))             $campos[] = 'Peso';
            if (! $this->campoLleno($this->estatura))         $campos[] = 'Estatura';
            if (! $this->campoLleno($this->dependientes))     $campos[] = 'Dependientes económicos';
            $faltantes['personales'] = $campos;
        }

        if (! $this->seccionContactoCompleta()) {
            $campos = [];
            if (! $this->campoLleno($this->celular))           $campos[] = 'Celular (obligatorio)';
            if (! $this->campoLleno($this->domicilio))         $campos[] = 'Domicilio';
            if (! $this->campoLleno($this->colonia))           $campos[] = 'Colonia';
            if (! $this->campoLleno($this->codigo_postal))     $campos[] = 'Código postal';
            if (! $this->campoLleno($this->municipio))         $campos[] = 'Municipio';
            if (! $this->campoLleno($this->ciudad))            $campos[] = 'Ciudad';
            $faltantes['contacto'] = $campos;
        }

        if (! $this->seccionEstudiosCompleta()) {
            $campos = [];
            if (! $this->campoLleno($this->escolaridad))    $campos[] = 'Nivel de escolaridad';
            if (! $this->campoLleno($this->puesto_deseado)) $campos[] = 'Puesto deseado';
            if (! $this->campoLleno($this->habilidades))    $campos[] = 'Habilidades principales';
            $faltantes['estudios'] = $campos;
        }

        if (! $this->seccionLaboralCompleta()) {
            $campos = [];
            if (! $this->campoLleno($this->sueldo_deseado)) $campos[] = 'Sueldo deseado';
            $faltantes['laboral'] = $campos;
        }

        if (! $this->seccionExtrasCompleta()) {
            $campos = [];
            $licenciaTiene = $this->licencia_conducir['tiene'] ?? '';
            if (! $this->campoLleno($this->curp))               $campos[] = 'CURP';
            if (! $this->campoLleno($this->nore_seguro_social)) $campos[] = 'Número de seguro social (NSS)';
            if (! $this->campoLleno($this->rfc))                $campos[] = 'RFC';
            if (! $this->campoLleno($this->afore))              $campos[] = 'Afore';
            if (! in_array($this->cartilla_tiene, ['si', 'no']))
                $campos[] = 'Cartilla militar — selecciona Sí o No';
            elseif ($this->cartilla_tiene === 'si' && ! $this->campoLleno($this->cartilla_militar))
                $campos[] = 'Número de cartilla militar';
            if (! in_array($this->pasaporte_tiene, ['si', 'no']))
                $campos[] = 'Pasaporte — selecciona Sí o No';
            elseif ($this->pasaporte_tiene === 'si' && ! $this->campoLleno($this->pasaporte))
                $campos[] = 'Número de pasaporte';
            if (! in_array($licenciaTiene, ['si', 'no']))
                $campos[] = 'Licencia de conducir — selecciona Sí o No';
            elseif ($licenciaTiene === 'si') {
                if (! $this->campoLleno($this->licencia_conducir['clase'] ?? ''))   $campos[] = 'Clase de licencia';
                if (! $this->campoLleno($this->licencia_conducir['numero'] ?? ''))  $campos[] = 'Número de licencia';
                if (! $this->campoLleno($this->licencia_conducir['vigencia'] ?? '')) $campos[] = 'Vigencia de licencia';
            }
            if (!empty($campos)) $faltantes['extras'] = $campos;
        }

        return $faltantes;
    }

    public function progresoSolicitud(): int
    {
        $secciones = [
            $this->seccionPersonalesCompleta(),
            $this->seccionContactoCompleta(),
            $this->seccionEstudiosCompleta(),
            $this->seccionLaboralCompleta(),
            $this->seccionExtrasCompleta(),
        ];

        return (int) round((count(array_filter($secciones)) / 5) * 100);
    }

    public function solicitudCompleta(): bool
    {
        return $this->seccionPersonalesCompleta()
            && $this->seccionContactoCompleta()
            && $this->seccionEstudiosCompleta()
            && $this->seccionLaboralCompleta()
            && $this->seccionExtrasCompleta();
    }

    public function irAPestana(string $pestana): void
    {
        if (! $this->pestanaDesbloqueada($pestana)) {
            $this->addError('solicitud', 'Completa la sección anterior para continuar.');
            return;
        }
        $this->dispatch('cambiarPestana', pestana: $pestana);
    }

    public function pestanaDesbloqueada(string $pestana): bool
    {
        $orden = ['personales', 'contacto', 'estudios', 'laboral', 'extras'];
        $idx = array_search($pestana, $orden, true);
        if ($idx === false || $idx === 0) {
            return true;
        }
        $secciones = $this->seccionesCompletadas();
        for ($i = 0; $i < $idx; $i++) {
            if (! $secciones[$orden[$i]]) {
                return false;
            }
        }
        return true;
    }

    public function enviarSolicitud(): void
    {
        if (! $this->puedeEnviarSolicitud()) {
            $this->addError('solicitud', 'Completa todas las secciones obligatorias antes de enviar.');
            return;
        }

        $this->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'edad' => ['required', 'integer', 'min:14', 'max:100'],
            'sexo' => ['required', 'in:M,F,Otro'],
            'fecha_nacimiento' => ['required', 'date'],
            'lugar_nacimiento' => ['required', 'string', 'max:150'],
            'nacionalidad' => ['required', 'string', 'max:100'],
            'estado_civil' => ['required', 'string', 'max:50'],
            'telefono' => ['required_without:celular', 'nullable', 'string', 'max:30'],
            'celular' => ['required_without:telefono', 'nullable', 'string', 'max:30'],
            'domicilio' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:150'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'municipio' => ['required', 'string', 'max:150'],
            'ciudad' => ['required', 'string', 'max:120'],
            'curp' => ['required', 'string', 'max:20'],
            'escolaridad' => ['required', 'string', 'max:150'],
            'puesto_deseado' => ['required', 'string', 'max:150'],
            'experiencia_anios' => ['required', 'integer', 'min:0', 'max:60'],
        ], [
            'nombre.required' => 'Escribe tu nombre.',
            'apellido_paterno.required' => 'Escribe tu apellido paterno.',
            'apellido_materno.required' => 'Escribe tu apellido materno.',
            'edad.required' => 'Indica tu edad.',
            'sexo.required' => 'Selecciona tu sexo.',
            'fecha_nacimiento.required' => 'Indica tu fecha de nacimiento.',
            'lugar_nacimiento.required' => 'Escribe tu lugar de nacimiento.',
            'nacionalidad.required' => 'Escribe tu nacionalidad.',
            'estado_civil.required' => 'Escribe tu estado civil.',
            'telefono.required_without' => 'Captura teléfono o celular.',
            'celular.required_without' => 'Captura celular o teléfono.',
            'domicilio.required' => 'Escribe tu domicilio.',
            'colonia.required' => 'Escribe tu colonia.',
            'codigo_postal.required' => 'Escribe tu código postal.',
            'municipio.required' => 'Escribe tu municipio.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'curp.required' => 'La CURP es obligatoria.',
            'escolaridad.required' => 'Selecciona tu escolaridad general.',
            'puesto_deseado.required' => 'Escribe tu aspiración laboral.',
            'experiencia_anios.required' => 'Indica tu experiencia total.',
        ]);

        $this->guardarBorrador();
        $candidato = $this->candidatoService->enviarSolicitud($this->candidatoId);
        $this->yaEnviada = true;
        $this->estadoSolicitud = $candidato->solicitud_estado ?? 'enviada';

        session()->flash('exito', 'Tu solicitud se envió correctamente.');
    }

    private function getDatos(): array
    {
        return [
            'usuario_id' => $this->usuarioId ?? Auth::id(),
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'edad' => $this->edad,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'lugar_nacimiento' => $this->lugar_nacimiento,
            'nacionalidad' => $this->nacionalidad,
            'peso' => $this->peso,
            'estatura' => $this->estatura,
            'estado_civil' => $this->estado_civil,
            'vive_con' => $this->vive_con,
            'dependientes' => $this->dependientes,
            'telefono' => $this->telefono,
            'celular' => $this->celular,
            'domicilio' => $this->domicilio,
            'colonia' => $this->colonia,
            'codigo_postal' => $this->codigo_postal,
            'municipio' => $this->municipio,
            'ciudad' => $this->ciudad,
            'curp' => $this->curp,
            'nore_seguro_social' => $this->nore_seguro_social,
            'rfc' => $this->rfc,
            'afore' => $this->afore,
            'cartilla_militar' => $this->cartilla_militar,
            'pasaporte' => $this->pasaporte,
            'experiencia_anios' => $this->experiencia_anios ?? 0,
            'puesto_deseado' => $this->puesto_deseado,
            'escolaridad' => $this->escolaridad,
            'sueldo_deseado' => $this->sueldo_deseado,
            'habilidades' => $this->habilidades,
            'licencia_conducir' => $this->licencia_conducir,
            'redes_sociales' => $this->redes_sociales,
            'escolaridad_detallada' => $this->escolaridad_detallada,
            'historial_laboral' => $this->historial_laboral,
            'referencias_personales' => $this->referencias_personales,
        ];
    }

    public function seccionPersonalesCompleta(): bool
    {
        return $this->campoLleno($this->nombre)
            && $this->campoLleno($this->apellido_paterno)
            && $this->campoLleno($this->apellido_materno)
            && $this->campoLleno($this->edad)
            && $this->campoLleno($this->sexo)
            && $this->campoLleno($this->fecha_nacimiento)
            && $this->campoLleno($this->lugar_nacimiento)
            && $this->campoLleno($this->nacionalidad)
            && $this->campoLleno($this->estado_civil)
            && $this->campoLleno($this->vive_con)
            && $this->campoLleno($this->peso)
            && $this->campoLleno($this->estatura)
            && $this->campoLleno($this->dependientes);
    }

    public function seccionContactoCompleta(): bool
    {
        return $this->campoLleno($this->celular)
            && $this->campoLleno($this->domicilio)
            && $this->campoLleno($this->colonia)
            && $this->campoLleno($this->codigo_postal)
            && $this->campoLleno($this->municipio)
            && $this->campoLleno($this->ciudad);
    }

    public function seccionEstudiosCompleta(): bool
    {
        $estudiosOk = true;
        foreach ($this->escolaridad_detallada as $estudio) {
            if (! $this->campoLleno($estudio['nivel'] ?? '') || ! $this->campoLleno($estudio['nombre'] ?? '')) {
                $estudiosOk = false;
                break;
            }
        }

        return $this->campoLleno($this->escolaridad)
            && $this->campoLleno($this->puesto_deseado)
            && $this->campoLleno($this->habilidades)
            && $estudiosOk;
    }

    public function seccionLaboralCompleta(): bool
    {
        $empleosOk = true;
        foreach ($this->historial_laboral as $empleo) {
            if (! $this->campoLleno($empleo['empresa'] ?? '')
                || ! $this->campoLleno($empleo['puesto'] ?? '')
                || ! $this->campoLleno($empleo['desde'] ?? '')) {
                $empleosOk = false;
                break;
            }
        }

        return $this->campoLleno($this->sueldo_deseado) && $empleosOk;
    }

    public function seccionExtrasCompleta(): bool
    {
        $licenciaTiene = $this->licencia_conducir['tiene'] ?? '';
        $cartillaOk  = $this->cartilla_tiene === 'no'
            || ($this->cartilla_tiene === 'si' && $this->campoLleno($this->cartilla_militar));
        $pasaporteOk = $this->pasaporte_tiene === 'no'
            || ($this->pasaporte_tiene === 'si' && $this->campoLleno($this->pasaporte));
        $licenciaOk  = $licenciaTiene === 'no'
            || ($licenciaTiene === 'si'
                && $this->campoLleno($this->licencia_conducir['clase'] ?? '')
                && $this->campoLleno($this->licencia_conducir['numero'] ?? '')
                && $this->campoLleno($this->licencia_conducir['vigencia'] ?? ''));

        $referenciasOk = true;
        foreach ($this->referencias_personales as $ref) {
            if (! $this->campoLleno($ref['nombre'] ?? '') || ! $this->campoLleno($ref['telefono'] ?? '')) {
                $referenciasOk = false;
                break;
            }
        }

        return $this->campoLleno($this->curp)
            && $this->campoLleno($this->nore_seguro_social)
            && $this->campoLleno($this->rfc)
            && $this->campoLleno($this->afore)
            && in_array($this->cartilla_tiene, ['si', 'no']) && $cartillaOk
            && in_array($this->pasaporte_tiene, ['si', 'no']) && $pasaporteOk
            && in_array($licenciaTiene, ['si', 'no']) && $licenciaOk
            && $referenciasOk;
    }

    public function campoLleno(mixed $valor): bool
    {
        return ! blank($valor);
    }

    /**
     * Retorna los nombres de los campos faltantes de una sección específica.
     */
    public function camposFaltantesPorSeccion(string $seccion): array
    {
        return match ($seccion) {
            'personales' => $this->camposFaltantesPersonales(),
            'contacto'   => $this->camposFaltantesContacto(),
            'estudios'   => $this->camposFaltantesEstudios(),
            'laboral'    => $this->camposFaltantesLaboral(),
            'extras'     => $this->camposFaltantesExtras(),
            default      => [],
        };
    }

    private function camposFaltantesPersonales(): array
    {
        $faltan = [];
        if (! $this->campoLleno($this->nombre))           $faltan[] = 'Nombre(s)';
        if (! $this->campoLleno($this->apellido_paterno)) $faltan[] = 'Apellido paterno';
        if (! $this->campoLleno($this->apellido_materno)) $faltan[] = 'Apellido materno';
        if (! $this->campoLleno($this->edad))             $faltan[] = 'Edad';
        if (! $this->campoLleno($this->sexo))             $faltan[] = 'Sexo';
        if (! $this->campoLleno($this->fecha_nacimiento)) $faltan[] = 'Fecha de nacimiento';
        if (! $this->campoLleno($this->lugar_nacimiento)) $faltan[] = 'Lugar de nacimiento';
        if (! $this->campoLleno($this->nacionalidad))     $faltan[] = 'Nacionalidad';
        if (! $this->campoLleno($this->estado_civil))     $faltan[] = 'Estado civil';
        if (! $this->campoLleno($this->vive_con))         $faltan[] = 'Vive con';
        if (! $this->campoLleno($this->peso))             $faltan[] = 'Peso';
        if (! $this->campoLleno($this->estatura))         $faltan[] = 'Estatura';
        if (! $this->campoLleno($this->dependientes))     $faltan[] = 'Dependientes económicos';
        return $faltan;
    }

    private function camposFaltantesContacto(): array
    {
        $faltan = [];
        if (! $this->campoLleno($this->celular))       $faltan[] = 'Celular';
        if (! $this->campoLleno($this->domicilio))     $faltan[] = 'Domicilio';
        if (! $this->campoLleno($this->colonia))       $faltan[] = 'Colonia';
        if (! $this->campoLleno($this->codigo_postal)) $faltan[] = 'Código postal';
        if (! $this->campoLleno($this->municipio))     $faltan[] = 'Municipio';
        if (! $this->campoLleno($this->ciudad))        $faltan[] = 'Ciudad';
        return $faltan;
    }

    private function camposFaltantesEstudios(): array
    {
        $faltan = [];
        if (! $this->campoLleno($this->escolaridad))    $faltan[] = 'Nivel de escolaridad';
        if (! $this->campoLleno($this->puesto_deseado)) $faltan[] = 'Puesto deseado';
        if (! $this->campoLleno($this->habilidades))    $faltan[] = 'Habilidades principales';
        foreach ($this->escolaridad_detallada as $i => $estudio) {
            if (! $this->campoLleno($estudio['nivel'] ?? ''))  $faltan[] = 'Estudio #' . ($i + 1) . ' — Nivel';
            if (! $this->campoLleno($estudio['nombre'] ?? '')) $faltan[] = 'Estudio #' . ($i + 1) . ' — Institución / carrera';
        }
        return $faltan;
    }

    private function camposFaltantesLaboral(): array
    {
        $faltan = [];
        if (! $this->campoLleno($this->sueldo_deseado)) $faltan[] = 'Sueldo deseado';
        foreach ($this->historial_laboral as $i => $empleo) {
            if (! $this->campoLleno($empleo['empresa'] ?? ''))  $faltan[] = 'Empleo #' . ($i + 1) . ' — Empresa';
            if (! $this->campoLleno($empleo['puesto'] ?? ''))   $faltan[] = 'Empleo #' . ($i + 1) . ' — Puesto';
            if (! $this->campoLleno($empleo['desde'] ?? ''))    $faltan[] = 'Empleo #' . ($i + 1) . ' — Fecha desde';
        }
        return $faltan;
    }

    private function camposFaltantesExtras(): array
    {
        $faltan = [];
        $licenciaTiene = $this->licencia_conducir['tiene'] ?? '';
        if (! $this->campoLleno($this->curp))               $faltan[] = 'CURP';
        if (! $this->campoLleno($this->nore_seguro_social)) $faltan[] = 'Número de seguro social (NSS)';
        if (! $this->campoLleno($this->rfc))                $faltan[] = 'RFC';
        if (! $this->campoLleno($this->afore))              $faltan[] = 'Afore';
        if (! in_array($this->cartilla_tiene, ['si', 'no']))
            $faltan[] = 'Cartilla militar — selecciona Sí o No';
        elseif ($this->cartilla_tiene === 'si' && ! $this->campoLleno($this->cartilla_militar))
            $faltan[] = 'Número de cartilla militar';
        if (! in_array($this->pasaporte_tiene, ['si', 'no']))
            $faltan[] = 'Pasaporte — selecciona Sí o No';
        elseif ($this->pasaporte_tiene === 'si' && ! $this->campoLleno($this->pasaporte))
            $faltan[] = 'Número de pasaporte';
        if (! in_array($licenciaTiene, ['si', 'no']))
            $faltan[] = 'Licencia de conducir — selecciona Sí o No';
        elseif ($licenciaTiene === 'si') {
            if (! $this->campoLleno($this->licencia_conducir['clase'] ?? ''))   $faltan[] = 'Clase de licencia';
            if (! $this->campoLleno($this->licencia_conducir['numero'] ?? ''))  $faltan[] = 'Número de licencia';
            if (! $this->campoLleno($this->licencia_conducir['vigencia'] ?? '')) $faltan[] = 'Vigencia de licencia';
        }
        foreach ($this->referencias_personales as $i => $ref) {
            if (! $this->campoLleno($ref['nombre'] ?? ''))    $faltan[] = 'Referencia #' . ($i + 1) . ' — Nombre';
            if (! $this->campoLleno($ref['telefono'] ?? ''))  $faltan[] = 'Referencia #' . ($i + 1) . ' — Teléfono';
        }
        return $faltan;
    }

    public function verificarYAvanzar(string $pestanaDestino): void
    {
        $this->autoGuardar();
        $orden = ['personales', 'contacto', 'estudios', 'laboral', 'extras'];
        $idxDestino = array_search($pestanaDestino, $orden, true);
        $pestanaActual = $orden[$idxDestino - 1] ?? 'personales';
        $metodo = 'seccion' . ucfirst($pestanaActual) . 'Completa';

        if (method_exists($this, $metodo) && $this->$metodo()) {
            $this->dispatch('cambiarPestana', pestana: $pestanaDestino);
        } else {
            $faltantes = $this->camposFaltantesPorSeccion($pestanaActual);
            $this->addError('solicitud', 'Completa los siguientes campos para continuar: ' . implode(', ', $faltantes));
        }
    }

    private function primerElemento(?array $elementos): ?array
    {
        $elementos = array_values($elementos ?? []);

        foreach ($elementos as $elemento) {
            if (is_array($elemento)) {
                return $elemento;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.candidato-solicitud');
    }
}
