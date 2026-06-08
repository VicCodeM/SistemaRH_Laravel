<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidato extends Model
{
    protected $fillable = [
        'usuario_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'edad', 'sexo',
        'fecha_nacimiento', 'lugar_nacimiento', 'nacionalidad', 'telefono', 'celular',
        'domicilio', 'colonia', 'codigo_postal', 'municipio', 'ciudad', 'peso', 'estatura',
        'vive_con', 'estado_civil', 'dependientes', 'curp', 'nore_seguro_social', 'rfc',
        'afore', 'cartilla_militar', 'cartilla_tiene', 'pasaporte', 'pasaporte_tiene', 'experiencia_anios', 'puesto_deseado',
        'habilidades', 'escolaridad', 'sueldo_deseado', 'sueldo_aprobado', 'fecha_contratacion',
        'cv_path', 'solicitud_estado', 'solicitud_enviada_at', 'solicitud_revisada_at',
        'solicitud_revision_admin_id',
        'licencia_conducir', 'redes_sociales', 'estado_salud', 'datos_personales',
        'datos_familiares', 'escolaridad_detallada', 'conocimientos_generales',
        'historial_laboral', 'referencias_personales', 'datos_generales', 'datos_economicos'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_contratacion' => 'date',
        'solicitud_enviada_at' => 'datetime',
        'solicitud_revisada_at' => 'datetime',
        'licencia_conducir' => 'array',
        'redes_sociales' => 'array',
        'estado_salud' => 'array',
        'datos_personales' => 'array',
        'datos_familiares' => 'array',
        'escolaridad_detallada' => 'array',
        'conocimientos_generales' => 'array',
        'historial_laboral' => 'array',
        'referencias_personales' => 'array',
        'datos_generales' => 'array',
        'datos_economicos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    public function nombreCompleto(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
        ])));
    }

    public static function solicitudEstados(): array
    {
        return CatalogoOpcion::opciones('candidato_estados', [
            'borrador' => 'Borrador',
            'enviada' => 'Enviada',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
        ]);
    }

    public static function solicitudEstadoLabel(?string $estado): string
    {
        return CatalogoOpcion::label('candidato_estados', $estado);
    }

    public static function solicitudEstadoBadgeClass(?string $estado): string
    {
        return match ($estado) {
            'borrador' => 'badge-gray',
            'enviada', 'en_revision' => 'badge-yellow',
            'aprobada' => 'badge-green',
            'rechazada' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function solicitudSeccionesCompletas(): array
    {
        return [
            'personales' => $this->seccionPersonalesCompleta(),
            'contacto' => $this->seccionContactoCompleta(),
            'estudios' => $this->seccionEstudiosCompleta(),
            'laboral' => $this->seccionLaboralCompleta(),
            'extras' => $this->seccionExtrasCompleta(),
        ];
    }

    public function solicitudProgreso(): int
    {
        $secciones = $this->solicitudSeccionesCompletas();
        $total = count($secciones);

        if ($total === 0) {
            return 0;
        }

        $completas = count(array_filter($secciones));

        return (int) round(($completas / $total) * 100);
    }

    public function solicitudCompleta(): bool
    {
        return ! in_array(false, $this->solicitudSeccionesCompletas(), true);
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
        return $this->campoLleno($this->curp)
            && $this->campoLleno($this->nore_seguro_social)
            && $this->campoLleno($this->rfc)
            && $this->campoLleno($this->afore);
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
}
