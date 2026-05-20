# Arquitectura y convenciones de SistemaRH

## Organización de carpetas

```
app/
  Http/Controllers/          # Controllers livianos; delegan a Livewire/Services
    Admin/                   # CRUD administrativos
    Auth/                    # Login, registro, verificación (Breeze)
    Candidato/
    Empresa/
  Livewire/                  # Componentes Livewire 3
    CandidatoSolicitud.php   # Formulario wizard principal
    Chat/
    KanbanBoard.php
  Models/                    # Eloquent con casts JSON
  Services/                  # Reglas de negocio y persistencia
  Policies/                  # Autorización
  View/Components/           # Componentes Blade

resources/views/
  livewire/
    candidato-solicitud.blade.php
    solicitud-sections/      # Paneles del wizard
      personales.blade.php
      contacto.blade.php
      estudios.blade.php
      laboral.blade.php
      extras.blade.php
  layouts/
    app.blade.php            # Layout principal con @livewireScripts
```

## Rutas

- Admin: `Route::prefix('admin')->middleware(['auth', 'verified'])`
- Candidato: `Route::prefix('candidato')->middleware(['auth'])`
- Livewire update: manejado automáticamente por `Livewire\Mechanisms\HandleRequests\HandleRequests`

## Middleware relevante

- `web` — sesiones, CSRF, cookies
- `auth` / `verified` — autenticación Breeze
- `Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders` — validación de headers LW

## Vite y assets

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
```

En `resources/js/app.js` **NO importar Alpine.js manualmente**. Livewire 3 ya lo incluye.
```js
// CORRECTO
import './bootstrap';

// INCORRECTO (causa "Detected multiple instances of Alpine running")
import Alpine from 'alpinejs';
Alpine.start();
```

## Estilos UI

Predominan **estilos inline** con variables CSS custom (`--accent`, `--border`, `--text`, etc.).
No hay componentes Tailwind tipográficos extensos; cada vista define su propio CSS inline o en `<style>`.

Patrones visuales recurrentes:
- `.solicitud-card` — tarjeta blanca con borde y sombra
- `.radio-pill` — botones tipo chip con radio oculto
- `.stepper` — indicador de pasos con círculos y líneas
- `.btn-locked` — botón deshabilitado gris
