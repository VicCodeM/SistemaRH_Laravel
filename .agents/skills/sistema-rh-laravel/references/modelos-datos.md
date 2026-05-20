# Modelos principales y estructura de datos

## Candidato

Tabla: `candidatos`

Campos clave:
- `usuario_id` → FK a `users`
- `nombre`, `apellido_paterno`, `apellido_materno`
- `edad` (int)
- `sexo` → `ENUM('M','F','Otro')` nullable
- `fecha_nacimiento` → `DATE` nullable
- `peso`, `estatura` → strings
- `estado_civil`, `vive_con` → strings
- `dependientes` → TEXT
- `curp`, `nore_seguro_social`, `rfc`, `afore` → strings
- `experiencia_anios` → int, default 0
- `puesto_deseado`, `escolaridad`, `sueldo_deseado` → strings
- `solicitud_estado` → `ENUM('borrador','enviada','en_revision','aprobada','rechazada')` default 'borrador'
- `solicitud_enviada_at`, `solicitud_revisada_at` → timestamps

Campos JSON (cast a `array`):
- `licencia_conducir` → `['tiene' => '', 'clase' => '', 'numero' => '', 'vigencia' => '']`
- `redes_sociales` → `['facebook' => '', 'twitter' => '', 'instagram' => '', 'linkedin' => '']`
- `escolaridad_detallada` → array de `['nivel' => '', 'nombre' => '', 'anios' => '', 'titulo' => '']`
- `historial_laboral` → array de `['empresa' => '', 'puesto' => '', 'jefe' => '', 'sueldo' => '', 'desde' => '', 'hasta' => '', 'motivo' => '']`
- `referencias_personales` → array de `['nombre' => '', 'telefono' => '', 'ocupacion' => '', 'tiempo' => '', 'domicilio' => '']`

## Vacante

Tabla: `vacantes`

- `titulo`, `descripcion`
- `empresa_id`
- `nivel_estudio` → string (catálogo)
- `sueldo_min`, `sueldo_max`
- `estado` → string

## Empresa

Tabla: `empresas`

- `nombre_empresa`, `rfc`, `telefono`, `direccion`
- `estado` → string (`activa`, `pendiente`)

## Postulacion

Tabla: `postulaciones`

- `candidato_id`, `vacante_id`
- `estado` → string

## User

Tabla: `users`

- Campos estándar Breeze + `estado` (`activo`, `pendiente`)
- Relación con `candidato` (perfil de candidato)

## CatalogoOpcion

Patrón para catálogos dinámicos:
```php
CatalogoOpcion::opciones('clave_catalogo', $fallbackArray);
CatalogoOpcion::label('clave_catalogo', $valor);
```

Usado en:
- `candidato_estados`
- `niveles_estudio`
- Otros catálogos administrables desde el panel

## ConfiguracionSistema

Patrón para settings clave-valor:
```php
ConfiguracionSistema::boolean('candidato_requiere_aprobacion', false);
```

## WorkflowService

Reglas de aprobación por entidad:
```php
$workflow->decideEmpresaRegistration($empresa);
$workflow->decideCandidatoRegistration($candidato);
$workflow->decideVacanteCreation($vacante);
```

Modos: `manual` (siempre pendiente) o `auto` (evalúa campos obligatorios).
