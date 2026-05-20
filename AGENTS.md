# SistemaRH — Guía para Agentes de Código

> Este documento describe la arquitectura, convenciones y comandos esenciales del proyecto para que un agente de IA pueda trabajar de forma efectiva sin conocimiento previo del codebase.

---

## 1. Resumen del proyecto

**SistemaRH** es un sistema de gestión de recursos humanos (RRHH) desarrollado en **Laravel 13** con **PHP 8.3+**. Permite la operación entre cuatro roles de usuario:

- **admin** — Control total: aprueba empresas/candidatos, gestiona catálogos, publica vacantes, asigna tareas, genera reportes.
- **empresa** — Cliente que publica solicitudes de servicio (vacantes) y abre tickets de soporte.
- **candidato** — Usuario que completa una solicitud de empleo detallada y se postula a vacantes.
- **interno** — Personal operativo que toma y ejecuta tareas (servicios asignados) y atiende tickets.

El idioma del proyecto es **español** (nombres de tablas, columnas, rutas, variables, vistas y comentarios de código).

---

## 2. Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 13.8, PHP 8.3 |
| Frontend | Blade, Livewire 4.3, AlpineJS 3, Vite 8 |
| Estilos | CSS custom puro (no Tailwind utility-first; `resources/css/app.css` tiene ~1100 líneas de diseño propio) |
| Base de datos | SQLite por defecto (`database/database.sqlite`); soporta MySQL/MariaDB/PostgreSQL |
| Autenticación | Laravel Breeze (sesiones) + middleware de roles propio (`CheckRole`) |
| Logs de actividad | `spatie/laravel-activitylog` |
| Testing | PHPUnit 12.5 |
| Calidad de código | Laravel Pint |

Dependencias clave en `composer.json`:
- `laravel/framework ^13.7`
- `livewire/livewire ^4.3`
- `spatie/laravel-activitylog ^5.0`
- `laravel/socialite ^5.27`
- `laravel/breeze ^2.4` (dev)
- `laravel/pint ^1.27` (dev)

---

## 3. Estructura de directorios relevante

```
app/
  Http/
    Controllers/
      Admin/          → Dashboard, reportes, aprobaciones, vacantes, catálogos, personal, tareas
      Auth/           → Login, registro (candidato y empresa), recuperación de contraseña
      Candidato/      → Dashboard del candidato, postulaciones, vacantes
      Empresa/        → Dashboard de empresa, solicitudes/vacantes
      Interno/        → Dashboard interno, gestión de tareas propias
      ProfileController.php
    Middleware/
      CheckRole.php   → Middleware propio para restringir rutas por rol(es)
    Requests/         → Form requests (LoginRequest, ProfileUpdateRequest)
  Livewire/
    CandidatoSolicitud.php
    Chat/             → ChatConversacion, ChatList, ChatNotificaciones
    KanbanBoard.php
  Models/             → 15+ modelos (User, Empresa, Candidato, Vacante, Postulacion,
                        ServicioAsignado, Ticket, CatalogoOpcion, CatalogoServicio,
                        PersonalExterno, ChatRoom, ChatMessage, etc.)
  Policies/           → CandidatoPolicy, VacantePolicy
  Providers/
    AppServiceProvider.php  → Registra singletons: WorkflowService, SlaInteligenteService
  Services/           → Lógica de negocio desacoplada:
    DashboardService, WorkflowService, SlaInteligenteService,
    CandidatoService, PostulacionService, VacanteService,
    TicketService, BitacoraService, BusquedaService, ExportService,
    ReporteService, SolicitudCompatibilidadService
  View/
    Components/       → AppLayout, GuestLayout

database/
  migrations/         → ~30 migraciones (usuarios, empresas, candidatos, vacantes,
                        postulaciones, tickets, chat, catálogos, bitácoras, etc.)
  seeders/
    DatabaseSeeder.php    → Crea admin por defecto + llama a DummyDataSeeder
    DummyDataSeeder.php   → Genera datos de demostración (empresas, candidatos, vacantes, postulaciones, tareas)
  factories/
    UserFactory.php

resources/
  views/              → Blade organizadas por rol y funcionalidad
    layouts/          → app.blade.php (con sidebar), guest.blade.php, landing.blade.php
    admin/            → Dashboard, empresas, candidatos, vacantes, catálogos, reportes, etc.
    candidato/        → Dashboard, solicitud, vacantes, postulaciones
    empresa/          → Dashboard, solicitudes/vacantes
    interno/          → Dashboard, tareas
    chat/             → Índice y conversación
    tickets/          → Listado, creación, detalle
    livewire/         → Vistas de componentes Livewire
    components/       → Componentes Blade reutilizables
  css/app.css         → Estilos globales custom (no utilizar Tailwind utility-first)
  js/app.js           → Solo importa bootstrap

routes/
  web.php             → Rutas principales agrupadas por rol
  auth.php            → Rutas de autenticación (Breeze modificadas para candidato/empresa)
```

---

## 4. Arquitectura y patrones

### 4.1 Roles y autorización
- El campo `users.rol` determina el rol (`admin`, `empresa`, `candidato`, `interno`).
- `users.estado` controla si la cuenta está `activo`, `pendiente` o `bloqueado`.
- El middleware `CheckRole` verifica `rol` contra los parámetros recibidos. Ejemplo:
  ```php
  Route::middleware(['role:admin'])->prefix('admin')->...
  ```
- El dashboard raíz (`/dashboard`) redirige según el rol del usuario autenticado.

### 4.2 Catálogos dinámicos (`CatalogoOpcion`)
- En lugar de enums de PHP, el proyecto usa la tabla `catalogo_opciones` para opciones administrables.
- Cada modelo expone métodos estáticos como `estados()`, `estadoLabel()`, `estadoBadgeClass()` que consultan `CatalogoOpcion::opciones('grupo', [...fallback...])`.
- Esto permite que el administrador modifique etiquetas y estados desde el panel sin tocar código.

### 4.3 Workflow de aprobaciones
- `config/workflow.php` define si las aprobaciones de empresas, candidatos y vacantes son `manual` o `auto`.
- `WorkflowService` encapsula la lógica de decisión. Si el modo es `auto`, valida datos mínimos y aprueba/rechaza automáticamente.

### 4.4 Servicios (`app/Services/`)
- La lógica compleja vive en clases de servicio inyectadas o resueltas desde `AppServiceProvider`.
- `DashboardService` arma los datos de cada dashboard por rol (stats, listados recientes, alertas operativas).
- `SlaInteligenteService` calcula tiempos de vencimiento de tickets según prioridad (`config/sla.php`).

### 4.5 Solicitud del candidato
- El modelo `Candidato` tiene múltiples columnas JSON (casteadas a `array`): `escolaridad_detallada`, `historial_laboral`, `referencias_personales`, `licencia_conducir`, `redes_sociales`, etc.
- `Candidato::solicitudProgreso()` calcula el porcentaje de completitud de la solicitud en 5 secciones: personales, contacto, estudios, laboral y extras.
- El componente Livewire `CandidatoSolicitud` permite editar la solicitud por pasos.

### 4.6 Servicios asignados (tareas)
- `ServicioAsignado` usa una relación polimórfica (`asignable`) para vincular tareas a `Empresa` o `Candidato`.
- El componente Livewire `KanbanBoard` muestra un tablero tipo kanban con columnas según estado.
- Los usuarios `interno` toman tareas (`en_proceso`), las completan o cancelan.

### 4.7 Chat
- Implementado con Livewire (no WebSockets): `ChatList`, `ChatConversacion`, `ChatNotificaciones`.
- Modelos: `ChatRoom`, `ChatMessage`, `ChatRoomMember`.

### 4.8 Tickets
- Sistema de tickets con SLA (`sla_due_at`).
- Estados: abierto, en_proceso, resuelto, cerrado.
- Prioridades: baja, media, alta, urgente (con tiempos SLA configurables en `.env`).

---

## 5. Comandos de build, desarrollo y pruebas

### Setup inicial
```bash
composer setup
```
Este script (`composer.json` scripts) ejecuta:
1. `composer install`
2. Copia `.env.example` a `.env` si no existe
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm install --ignore-scripts`
6. `npm run build`

### Desarrollo local
```bash
composer dev
```
Ejecuta en paralelo (concurrently):
- `php artisan serve`
- `php artisan queue:listen --tries=1 --timeout=0`
- `php artisan pail --timeout=0` (logs en tiempo real)
- `npm run dev` (Vite HMR)

### Compilar assets para producción
```bash
npm run build
```

### Migraciones y seeders
```bash
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
# o solo datos de demo:
php artisan db:seed --class=DummyDataSeeder
```

### Pruebas
```bash
composer test
# Equivalente a:
php artisan config:clear --ansi
php artisan test
```
- `phpunit.xml` configura entorno `testing` con SQLite en memoria (`:memory:`).
- Los tests existentes cubren autenticación Breeze (registro, login, reset de contraseña, verificación de email, perfil).

### Calidad de código
```bash
./vendor/bin/pint        # Formatea según el estilo Laravel
```

---

## 6. Convenciones de código

- **Idioma**: Todo el código, nombres de variables, rutas y comentarios están en **español**.
- **Nombres de tablas/columnas**: En español (`empresas`, `candidatos`, `servicios_asignados`, `solicitud_estado`, etc.).
- **Rutas**: Prefijos por rol (`/admin/*`, `/empresa/*`, `/candidato/*`, `/interno/*`).
- **Modelos**: Métodos estáticos para catálogos (`estados()`, `estadoLabel()`, `estadoBadgeClass()`).
- **Vistas**: Organizadas por carpeta según el rol o funcionalidad (`admin/`, `candidato/`, `empresa/`, `interno/`, `chat/`, `tickets/`).
- **CSS**: No se usa Tailwind como utility-first. Todo el diseño está en `resources/css/app.css` con variables CSS custom (`--sidebar-width`, `--accent`, etc.). Si se agregan nuevos componentes visuales, preferir agregar clases semánticas en ese archivo.
- **JavaScript**: El frontend es mayormente server-rendered (Blade + Livewire). Para interacciones ligeras se usa AlpineJS. No hay React/Vue en el proyecto.
- **Modales**: La aplicación usa un sistema de modal global basado en AJAX (`rh-modal-overlay`, `rh-modal-box`) que carga contenido vía `fetch` en lugar de modales nativos de Blade.

---

## 7. Configuración importante

Archivos de configuración propios del dominio:

- `config/workflow.php` — Modo de aprobación (`manual`/`auto`) para empresas, candidatos y vacantes.
- `config/sla.php` — Tiempos SLA base en minutos para tickets según prioridad.

Variables de entorno relevantes (`.env`):
- `DB_CONNECTION=sqlite` (por defecto; también soporta mysql/mariadb/pgsql)
- `DB_DATABASE=database/database.sqlite`
- `WORKFLOW_EMPRESAS=manual`
- `WORKFLOW_CANDIDATOS=manual`
- `WORKFLOW_VACANTES=manual`
- `SLA_ALTA_MINUTOS=45`
- `SLA_MEDIA_MINUTOS=180`
- `SLA_BAJA_MINUTOS=480`

---

## 8. Testing

- PHPUnit 12.5 con suite `Unit` y `Feature`.
- Base de datos de pruebas: SQLite `:memory:` (ver `phpunit.xml`).
- Tests de autenticación están en `tests/Feature/Auth/`.
- No hay tests de dominio aún (servicios, modelos, policies); sería buena práctica agregarlos.

---

## 9. Consideraciones de seguridad

- Autenticación basada en sesiones de Laravel Breeze.
- Contraseñas hasheadas con bcrypt.
- Verificación de email disponible pero no forzada en todas las rutas (algunos flujos la omiten).
- Roles verificados explícitamente en rutas con `CheckRole`; no se usa Spatie Permissions ni Policies extensivamente (solo `CandidatoPolicy` y `VacantePolicy` existen).
- El seeder `DummyDataSeeder` crea cuentas de demo con contraseña `password`; **no debe ejecutarse en producción**.
- No se observa el uso de Laravel Sanctum ni API tokens; la app es 100% web tradicional.

---

## 10. Notas para el agente

- Si necesitas agregar un nuevo estado o tipo, revisa si ya existe un grupo en `CatalogoOpcion::gruposGestionables()` y usa ese patrón antes de hardcodear valores.
- Si modificas la estructura de la solicitud del candidato (campos JSON), actualiza también `solicitudSeccionesCompletas()` y los métodos de sección correspondientes en `Candidato`.
- El CSS custom es extenso; antes de crear nuevas clases, busca en `resources/css/app.css` si ya existe una utilidad semántica (`.card`, `.btn`, `.badge-*`, `.metric-card`, etc.).
- Las migraciones del proyecto incluyen correcciones de datos (`fix_*`); cuando crees nuevas migraciones, usa nombres descriptivos y evita alterar migraciones ya ejecutadas en producción.
- El proyecto usa `array` casts extensivamente para columnas JSON; mantén esa convención para nuevos campos multivalor.
