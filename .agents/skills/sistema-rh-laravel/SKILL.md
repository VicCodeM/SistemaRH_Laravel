---
name: sistema-rh-laravel
description: >
  Sistema de Recursos Humanos (SistemaRH) construido con Laravel 13 + Livewire 3.
  Gestiona candidatos, vacantes, postulaciones, empresas, personal interno/externo,
  tickets, chat interno, catálogos dinámicos y workflow de aprobaciones.
  Usar cuando se necesite: (1) modificar formularios multi-paso de candidatos,
  (2) trabajar con componentes Livewire, (3) ajustar reglas de negocio de workflows,
  (4) modificar modelos con campos JSON/ENUM, (5) crear nuevos módulos administrativos,
  (6) solucionar problemas de sincronización Livewire/Alpine.js, o (7) agregar
  catálogos dinámicos (CatalogoOpcion).
---

# SistemaRH — Guía de desarrollo

## Stack y versiones

- **Laravel**: 13.8
- **PHP**: 8.5
- **Livewire**: 3.x (incluye Alpine.js automáticamente)
- **Frontend**: Vite + Tailwind CSS (estilos inline predominantes)
- **Base de datos**: MySQL
- **Auth**: Laravel Breeze (blade views adaptadas)

## Arquitectura general

Los controllers son **livianos**; la lógica vive en:

1. **Livewire Components** (`app/Livewire/`) — formularios interactivos, tableros Kanban, chat.
2. **Services** (`app/Services/`) — reglas de negocio, persistencia con validaciones.
3. **Models** (`app/Models/`) — casts JSON a arrays, scopes mínimos.

Leer [references/arquitectura.md](references/arquitectura.md) para convenciones de rutas, middleware y organización de carpetas.

## Formularios multi-paso (patrón crítico)

El componente `CandidatoSolicitud` es la referencia para formularios wizard:

- Pestañas: `personales → contacto → estudios → laboral → extras`
- **Auto-guardado** en `updated()` tras cada cambio (`autoGuardar()`)
- Validación progresiva: `seccionXCompleta()` usa `blank()` para verificar campos
- Bloqueo de pestañas: no se puede avanzar sin completar la anterior

### Reglas de wire:model en este proyecto

| Tipo de input | Modificador | Razón |
|---------------|-------------|-------|
| Texto, number, date, textarea | `wire:model` | Sincronización reactiva con debounce; **evitar `wire:model.blur`** porque el auto-guardado puede ejecutarse antes de que el input pierda foco, causando valores vacíos en el servidor. |
| Radio buttons | `wire:model.live` | Sincronización inmediata; **siempre agregar `name`** para agrupación HTML correcta. |
| Selects | `wire:model` | Sincronización en `change`. |

### Problemas conocidos y soluciones

Leer [references/livewire-patrones.md](references/livewire-patrones.md) para:
- Error "Detected multiple instances of Alpine running"
- Error `Data truncated for column` en columnas ENUM/DATE
- Sincronización incompleta con `wire:model.blur`
- Radio pills con CSS (`opacity: 0; width: 0; height: 0`)

## Modelos y datos

Leer [references/modelos-datos.md](references/modelos-datos.md) para:
- Estructura de `Candidato`, `Vacante`, `Empresa`, `Postulacion`, `User`
- Campos JSON (`licencia_conducir`, `historial_laboral`, etc.)
- Columnas ENUM (`sexo`, `solicitud_estado`)
- Patrón `CatalogoOpcion` para catálogos dinámicos

## Workflow de aprobaciones

`WorkflowService` decide el estado de entidades según configuración (`config/workflow.php`):

- `manual` → siempre `pendiente`
- `auto` → evalúa campos obligatorios y activa/aprueba automáticamente

Entidades soportadas: `empresas`, `candidatos`, `vacantes`.

## Catálogos dinámicos

`CatalogoOpcion` reemplaza enums hardcodeados. Ejemplo:

```php
CatalogoOpcion::opciones('candidato_estados', ['borrador' => 'Borrador', ...]);
CatalogoOpcion::label('candidato_estados', $estado);
```

## Convenciones de código

- **Idioma**: Español para UI, variables, modelos, tablas.
- **Nombres de tablas**: plural snake_case (`candidatos`, `vacantes`).
- **Services**: un service por dominio (`CandidatoService`, `VacanteService`).
- **Livewire**: propiedades públicas tipadas, método `getDatos()` para extraer array plano.
- **JSON en BD**: siempre castear a `array` en el modelo; normalizar vacíos a `null` en el Service antes de guardar.
