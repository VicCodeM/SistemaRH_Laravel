<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Candidato;
use App\Services\CandidatoService;
use Illuminate\Support\Facades\Auth;

class CandidatoSolicitud extends Component
{
    private CandidatoService $candidatoService;

    public function boot(CandidatoService $candidatoService)
    {
        $this->candidatoService = $candidatoService;
    }
    public int $pasoActual = 1;
    public $candidatoId;
    public bool $yaEnviada = false;
    
    // Paso 1: Personales
    public $nombre, $apellido_paterno, $apellido_materno, $edad, $sexo;
    public $fecha_nacimiento, $lugar_nacimiento, $nacionalidad;
    public $peso, $estatura, $estado_civil, $vive_con, $dependientes;
    
    // Paso 2: Contacto
    public $telefono, $celular, $domicilio, $colonia, $codigo_postal, $municipio, $ciudad;
    
    // Paso 3: Documentación y Redes
    public $curp, $nore_seguro_social, $rfc, $afore, $cartilla_militar, $pasaporte;
    public array $licencia_conducir = ['tiene' => '', 'clase' => '', 'numero' => '', 'vigencia' => ''];
    public array $redes_sociales = ['facebook' => '', 'twitter' => '', 'instagram' => '', 'linkedin' => ''];
    
    // Paso 4: Salud y Datos Generales
    public array $estado_salud = ['estado' => '', 'cronica_tiene' => '', 'cronica_detalle' => '', 'fuma' => '', 'bebe' => '', 'deporte' => '', 'pasatiempo' => '', 'meta' => ''];
    public array $conocimientos_generales = ['idiomas' => '', 'software' => '', 'maquinas' => '', 'otras_funciones' => ''];
    public array $datos_generales = ['fuente' => '', 'parientes' => '', 'afianzado' => '', 'sindicato' => '', 'seguro_vida' => '', 'viajar' => '', 'cambio_residencia' => '', 'disponibilidad' => '', 'fecha_disponible' => ''];
    public array $datos_economicos = ['otros_ingresos' => '', 'conyuge_trabaja' => '', 'casa_propia' => '', 'paga_renta' => '', 'auto_propio' => '', 'deudas' => '', 'gastos_mensuales' => ''];
    
    // Paso 5: Datos Familiares y Escolaridad
    public array $datos_familiares = [
        'Padre' => ['nombre' => '', 'vive' => '', 'ocupacion' => ''],
        'Madre' => ['nombre' => '', 'vive' => '', 'ocupacion' => ''],
        'Esposa(o)' => ['nombre' => '', 'vive' => '', 'ocupacion' => ''],
        'hijos' => ''
    ];
    public array $escolaridad_detallada = [];
    
    // Paso 6: Profesionales, Empleos y Referencias
    public $experiencia_anios, $puesto_deseado, $escolaridad, $sueldo_deseado, $habilidades;
    public array $historial_laboral = [];
    public array $referencias_personales = [];

    public function mount()
    {
        $candidato = $this->candidatoService->obtenerCandidatoPorUsuario(Auth::id());
        if ($candidato) {
            $this->candidatoId = $candidato->id;
            
            // Cargar datos directos
            $campos = [
                'nombre', 'apellido_paterno', 'apellido_materno', 'edad', 'sexo',
                'fecha_nacimiento', 'lugar_nacimiento', 'nacionalidad',
                'peso', 'estatura', 'vive_con', 'estado_civil', 'dependientes',
                'telefono', 'celular', 'domicilio', 'colonia', 'codigo_postal', 'municipio', 'ciudad',
                'curp', 'nore_seguro_social', 'rfc', 'afore', 'cartilla_militar', 'pasaporte',
                'experiencia_anios', 'puesto_deseado', 'escolaridad', 'sueldo_deseado', 'habilidades'
            ];
            
            foreach ($campos as $campo) {
                if ($campo === 'fecha_nacimiento' && $candidato->$campo) {
                    $this->$campo = $candidato->$campo->format('Y-m-d');
                } else {
                    $this->$campo = $candidato->$campo;
                }
            }
            
            // Cargar JSONs mapeando con default values para evitar nulos
            $this->licencia_conducir = array_merge($this->licencia_conducir, is_array($candidato->licencia_conducir) ? $candidato->licencia_conducir : []);
            $this->redes_sociales = array_merge($this->redes_sociales, is_array($candidato->redes_sociales) ? $candidato->redes_sociales : []);
            $this->estado_salud = array_merge($this->estado_salud, is_array($candidato->estado_salud) ? $candidato->estado_salud : []);
            $this->conocimientos_generales = array_merge($this->conocimientos_generales, is_array($candidato->conocimientos_generales) ? $candidato->conocimientos_generales : []);
            $this->datos_generales = array_merge($this->datos_generales, is_array($candidato->datos_generales) ? $candidato->datos_generales : []);
            $this->datos_economicos = array_merge($this->datos_economicos, is_array($candidato->datos_economicos) ? $candidato->datos_economicos : []);
            
            // Especiales estructurados
            if (is_array($candidato->datos_familiares)) {
                $this->datos_familiares = array_replace_recursive($this->datos_familiares, $candidato->datos_familiares);
            }
            
            // Escolaridad dinámica
            if (is_array($candidato->escolaridad_detallada)) {
                // Compatibilidad con sistema viejo
                if (isset($candidato->escolaridad_detallada['Primaria'])) {
                    foreach ($candidato->escolaridad_detallada as $nivel => $datos) {
                        if (is_array($datos) && (!empty($datos['nombre']) || !empty($datos['anios']) || !empty($datos['titulo']))) {
                            $this->escolaridad_detallada[] = ['nivel' => $nivel, 'nombre' => $datos['nombre'] ?? '', 'anios' => $datos['anios'] ?? '', 'titulo' => $datos['titulo'] ?? ''];
                        }
                    }
                } else {
                    $this->escolaridad_detallada = $candidato->escolaridad_detallada;
                }
            }
            
            // Listas dinámicas
            $this->historial_laboral = is_array($candidato->historial_laboral) ? $candidato->historial_laboral : [];
            $this->referencias_personales = is_array($candidato->referencias_personales) ? $candidato->referencias_personales : [];
            
            if (empty($this->escolaridad_detallada)) $this->agregarEscolaridad();
            if (empty($this->historial_laboral)) $this->agregarEmpleo();
            if (empty($this->referencias_personales)) $this->agregarReferencia();

            if ($candidato->solicitud_estado !== 'borrador') {
                $this->yaEnviada = true;
            }
        } else {
            $this->nombre = Auth::user()->name;
            $this->agregarEscolaridad();
            $this->agregarEmpleo();
            $this->agregarReferencia();
        }
    }
    
    public function agregarEscolaridad() {
        $this->escolaridad_detallada[] = ['nivel' => '', 'nombre' => '', 'anios' => '', 'titulo' => ''];
    }

    public function eliminarEscolaridad($index) {
        unset($this->escolaridad_detallada[$index]);
        $this->escolaridad_detallada = array_values($this->escolaridad_detallada);
    }
    
    public function agregarEmpleo() {
        $this->historial_laboral[] = ['empresa' => '', 'puesto' => '', 'jefe' => '', 'sueldo' => '', 'desde' => '', 'hasta' => '', 'motivo' => ''];
    }

    public function eliminarEmpleo($index) {
        unset($this->historial_laboral[$index]);
        $this->historial_laboral = array_values($this->historial_laboral);
    }
    
    public function agregarReferencia() {
        $this->referencias_personales[] = ['nombre' => '', 'telefono' => '', 'ocupacion' => '', 'tiempo' => '', 'domicilio' => ''];
    }

    public function eliminarReferencia($index) {
        unset($this->referencias_personales[$index]);
        $this->referencias_personales = array_values($this->referencias_personales);
    }

    public function siguientePaso()
    {
        if ($this->pasoActual == 1) {
            $this->validate(['nombre' => 'required', 'apellido_paterno' => 'required'], ['nombre.required' => 'Obligatorio.', 'apellido_paterno.required' => 'Obligatorio.']);
        } elseif ($this->pasoActual == 2) {
            $this->validate(['telefono' => 'required_without:celular', 'ciudad' => 'required'], ['telefono.required_without' => 'El teléfono o celular es obligatorio.', 'ciudad.required' => 'La ciudad es obligatoria.']);
        }
        
        $this->guardarBorrador();
        $this->pasoActual++;
    }
    
    public function pasoAnterior()
    {
        $this->pasoActual--;
    }
    
    public function guardarBorrador()
    {
        $datos = $this->getDatos();
        $candidato = $this->candidatoService->guardarBorrador(Auth::id(), $this->candidatoId ?: null, $datos);
        $this->candidatoId = $candidato->id;
    }

    private function getDatos(): array
    {
        return [
            'usuario_id' => Auth::id(),
            'nombre' => $this->nombre, 'apellido_paterno' => $this->apellido_paterno, 'apellido_materno' => $this->apellido_materno,
            'edad' => $this->edad, 'sexo' => $this->sexo, 'fecha_nacimiento' => $this->fecha_nacimiento,
            'lugar_nacimiento' => $this->lugar_nacimiento, 'nacionalidad' => $this->nacionalidad,
            'peso' => $this->peso, 'estatura' => $this->estatura, 'vive_con' => $this->vive_con,
            'estado_civil' => $this->estado_civil, 'dependientes' => $this->dependientes,
            'telefono' => $this->telefono, 'celular' => $this->celular, 'domicilio' => $this->domicilio,
            'colonia' => $this->colonia, 'codigo_postal' => $this->codigo_postal, 'municipio' => $this->municipio, 'ciudad' => $this->ciudad,
            'curp' => $this->curp, 'nore_seguro_social' => $this->nore_seguro_social, 'rfc' => $this->rfc,
            'afore' => $this->afore, 'cartilla_militar' => $this->cartilla_militar, 'pasaporte' => $this->pasaporte,
            'experiencia_anios' => $this->experiencia_anios ?? 0, 'puesto_deseado' => $this->puesto_deseado,
            'escolaridad' => $this->escolaridad, 'sueldo_deseado' => $this->sueldo_deseado, 'habilidades' => $this->habilidades,
            'licencia_conducir' => $this->licencia_conducir,
            'redes_sociales' => $this->redes_sociales,
            'estado_salud' => $this->estado_salud,
            'conocimientos_generales' => $this->conocimientos_generales,
            'datos_generales' => $this->datos_generales,
            'datos_economicos' => $this->datos_economicos,
            'datos_familiares' => $this->datos_familiares,
            'escolaridad_detallada' => $this->escolaridad_detallada,
            'historial_laboral' => $this->historial_laboral,
            'referencias_personales' => $this->referencias_personales,
        ];
    }
    
    public function enviarSolicitud()
    {
        $this->guardarBorrador();
        $this->candidatoService->enviarSolicitud($this->candidatoId);
        $this->yaEnviada = true;
        session()->flash('exito', '¡Tu solicitud ha sido enviada con éxito!');
    }

    public function render()
    {
        return view('livewire.candidato-solicitud');
    }
}
