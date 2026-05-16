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
        $this->escolaridad_detallada[] = [
            'nivel' => '',
            'nombre' => '',
            'anios' => '',
            'titulo' => '',
        ];
    }

    public function eliminarEscolaridad(int $index): void
    {
        unset($this->escolaridad_detallada[$index]);
        $this->escolaridad_detallada = array_values($this->escolaridad_detallada);
    }

    public function agregarEmpleo(): void
    {
        $this->historial_laboral[] = [
            'empresa' => '',
            'puesto' => '',
            'jefe' => '',
            'sueldo' => '',
            'desde' => '',
            'hasta' => '',
            'motivo' => '',
        ];
    }

    public function eliminarEmpleo(int $index): void
    {
        unset($this->historial_laboral[$index]);
        $this->historial_laboral = array_values($this->historial_laboral);
    }

    public function agregarReferencia(): void
    {
        $this->referencias_personales[] = [
            'nombre' => '',
            'telefono' => '',
            'ocupacion' => '',
            'tiempo' => '',
            'domicilio' => '',
        ];
    }

    public function eliminarReferencia(int $index): void
    {
        unset($this->referencias_personales[$index]);
        $this->referencias_personales = array_values($this->referencias_personales);
    }

    public function setSexo(string $val): void
    {
        $this->sexo = $val;
        $this->autoGuardar();
    }

    public function setEstadoCivil(string $val): void
    {
        $this->estado_civil = $val;
        $this->autoGuardar();
    }

    public function setViveCon(string $val): void
    {
        $this->vive_con = $val;
        $this->autoGuardar();
    }

    public function setLicenciaTiene(string $val): void
    {
        $this->licencia_conducir['tiene'] = $val;
        if ($val === 'no') {
            $this->licencia_conducir['clase']    = '';
            $this->licencia_conducir['numero']   = '';
            $this->licencia_conducir['vigencia'] = '';
        }
        $this->autoGuardar();
    }

    public function setCartillaTiene(string $val): void
    {
        $this->cartilla_tiene = $val;
        if ($val === 'no') {
            $this->cartilla_militar = '';
        }
        $this->autoGuardar();
    }

    public function setPasaporteTiene(string $val): void
    {
        $this->pasaporte_tiene = $val;
        if ($val === 'no') {
            $this->pasaporte = '';
        }
        $this->autoGuardar();
    }

    public function updated(string $property): void
    {
        $this->autoGuardar();
    }

    public function guardarBorrador(): void
    {
        $this->autoGuardar();
        session()->flash('exito', 'Avance guardado correctamente.');
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

    private function seccionPersonalesCompleta(): bool
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

    private function seccionContactoCompleta(): bool
    {
        return $this->campoLleno($this->celular)
            && $this->campoLleno($this->domicilio)
            && $this->campoLleno($this->colonia)
            && $this->campoLleno($this->codigo_postal)
            && $this->campoLleno($this->municipio)
            && $this->campoLleno($this->ciudad);
    }

    private function seccionEstudiosCompleta(): bool
    {
        return $this->campoLleno($this->escolaridad)
            && $this->campoLleno($this->puesto_deseado)
            && $this->campoLleno($this->habilidades);
    }

    private function seccionLaboralCompleta(): bool
    {
        return $this->campoLleno($this->sueldo_deseado);
    }

    private function seccionExtrasCompleta(): bool
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

        return $this->campoLleno($this->curp)
            && $this->campoLleno($this->nore_seguro_social)
            && $this->campoLleno($this->rfc)
            && $this->campoLleno($this->afore)
            && in_array($this->cartilla_tiene, ['si', 'no']) && $cartillaOk
            && in_array($this->pasaporte_tiene, ['si', 'no']) && $pasaporteOk
            && in_array($licenciaTiene, ['si', 'no']) && $licenciaOk;
    }

    private function campoLleno(mixed $valor): bool
    {
        return ! blank($valor);
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
