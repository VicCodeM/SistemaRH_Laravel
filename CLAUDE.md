# CLAUDE.md — Sistema RH
> Laravel 11 · MySQL · Livewire · Todo en español · POO limpia · Simple para el usuario

---

## PROYECTO

Plataforma web donde:
- **Empresas** publican **vacantes** y solicitan **servicios** (capacitación, coaching, etc.).
- **Candidatos** postulan a vacantes.
- **Personal Interno (equipo RH)** ejecuta los **servicios** solicitados.
- **Admin** orquesta todo.

> Regla de oro: **mantén el sistema simple**. Si una funcionalidad requiere explicación al usuario, está mal diseñada.

### ⚠️ Glosario — NO confundir conceptos

| Término en la UI | Significado | Modelo | Quién lo usa |
|---|---|---|---|
| **Solicitud del candidato** | El perfil/CV: datos personales, estudios, experiencia. Lo llena UNA vez para poder postular. | `Candidato` (campos del perfil) | Candidato |
| **Postulación** | El candidato aplica a UNA vacante específica. | `Postulacion` | Candidato → Vacante |
| **Vacante** | Oferta de empleo publicada por una empresa. Tiene `nivel_jerarquico`. | `Vacante` | Empresa |
| **Pedido de servicio** (UI) / `ServicioAsignado` (interno) | Una empresa o candidato pide un servicio (capacitación, coaching, mantenimiento, etc.) y se asigna a un interno. | `ServicioAsignado` | Empresa o Candidato → Interno |
| **Capacidades / Especialidades** | Qué servicios sabe brindar un interno. | pivote `interno_servicio` | Personal interno |

**NO renombrar `ServicioAsignado` a "Solicitud*"** — la palabra "solicitud" ya está tomada por el perfil del candidato y causaría confusión.

---

## COMANDOS

```bash
# Dev (servidor + queue + logs + vite)
composer run dev

# Frontend
npm run dev
npm run build

# Tests
php artisan test
php artisan test --filter=NombreTest

# Limpiar vistas compiladas (después de cambios en Blade)
php artisan view:clear

# Sintaxis de un archivo PHP
php -l app/Ruta/Archivo.php
```

**WAMP:** `APP_URL` debe apuntar al subdirectorio:
```
APP_URL=http://localhost/RHLaravel/SistemaRH_Laravel/public
```

---

## DIAGRAMA DE CASOS DE USO

```
                            ┌──────────────────────────────┐
                            │       SISTEMA RH (web)       │
                            └──────────────────────────────┘

   ┌───────────┐                                                    ┌────────────┐
   │  EMPRESA  │                                                    │ CANDIDATO  │
   └─────┬─────┘                                                    └─────┬──────┘
         │                                                                │
         │  ▸ Registrarse                                                 │  ▸ Registrarse
         │  ▸ Publicar vacante (con nivel jerárquico)                     │  ▸ Subir CV / completar perfil
         │  ▸ Ver postulaciones                                           │  ▸ Buscar vacantes
         │  ▸ Mover postulación en pipeline                               │  ▸ Postularse
         │  ▸ Solicitar servicio (capacitación / coaching / etc.)         │  ▸ Solicitar servicio (curso)
         │  ▸ Ver estado de servicios solicitados                         │  ▸ Ver estado de servicios
         │                                                                │
         └────────────────────────┐                ┌────────────────────────┘
                                  ▼                ▼
                            ┌─────────────────────────────┐
                            │            ADMIN            │
                            └─────────────────────────────┘
                            │  ▸ Aprobar/rechazar empresas y candidatos
                            │  ▸ Gestionar catálogos (servicios, opciones)
                            │  ▸ Hacer matching vacante ↔ candidato
                            │  ▸ Asignar interno a una solicitud de servicio
                            │       (manual o asignación inteligente)
                            │  ▸ Gestionar personal interno + capacidades
                            │  ▸ Ver tablero KPIs y reportes
                                  ▲
                                  │  asigna
                                  ▼
                          ┌──────────────────┐
                          │ PERSONAL INTERNO │
                          └──────────────────┘
                          │  ▸ Ver "Mis servicios asignados"
                          │  ▸ Tomar, ejecutar y completar
                          │  ▸ Ver carga de trabajo / disponibilidad
                          │  ▸ Registrar capacidades (qué servicios sabe brindar)
```

---

## DOS FLUJOS, NADA MÁS

```
🔵 FLUJO 1 — VACANTES (reclutamiento)
─────────────────────────────────────
Empresa publica Vacante (con nivel_jerarquico)
   ↓
Admin aprueba
   ↓
Candidato postula  →  Postulacion (nueva → revision → entrevista → ofertada → contratada/rechazada)
   ↓
Admin mueve en pipeline + matching
   ↓
Vacante: cerrada


🟢 FLUJO 2 — SERVICIOS (capacitación, coaching, mantenimiento, etc.)
───────────────────────────────────────────────────────────────────
Empresa o Candidato solicita servicio  →  ServicioAsignado
   ↓
Admin la ve en KANBAN
   ↓
Admin asigna a 1 Interno capacitado (manual o sugerencia inteligente)
   ↓
Estado: pendiente → activo → en_proceso → completado
                        ↘ cancelado
   ↓
Interno completa → queda libre para más servicios
```

---

## ESTRUCTURA

```
app/
  Models/          # 1 archivo por modelo, en español
  Http/
    Controllers/   # Delgados: reciben y responden, NADA más
    Requests/      # Toda validación aquí
  Livewire/        # Componentes reactivos (kanban, chat)
  Services/        # TODA la lógica de negocio aquí
  Policies/        # Control de acceso por modelo
database/
  migrations/      # Orden cronológico
  seeders/         # Catálogos base + admin
resources/
  views/
    admin/         # Panel administrativo
    empresa/       # Panel empresa
    candidato/     # Panel candidato
    interno/       # Panel personal interno
    livewire/      # Vistas de componentes
routes/
  web.php          # Todas las rutas web (con middlewares de rol)
```

---

## MODELOS (limpios, sin redundancias)

```php
User              · name · email · rol[admin|empresa|candidato|interno] · estado
                  · carga_trabajo_horas · capacidad_maxima_horas · disponibilidad
                  · departamento

Empresa           · usuario_id · nombre_empresa · rfc · sector
                  · estado[pendiente|activa|suspendida|rechazada]
Candidato         · usuario_id · nombre · apellidos · nivel_estudios · area · experiencia_anios
                  · solicitud_estado[pendiente|aprobada|rechazada]

Vacante           · empresa_id · titulo · descripcion · requerimientos
                  · nivel_jerarquico[operativo|supervision|gerencia|direccion]
                  · nivel_estudios_minimo · area_requerida · experiencia_minima
                  · salario_min · salario_max · ubicacion · tipo_contrato
                  · estado[pendiente|activa|cerrada|rechazada] · fecha_publicacion
Postulacion       · vacante_id · candidato_id · estado · notas · fecha_postulacion

CatalogoServicio  · nombre · descripcion · tipo · activo · orden
                  // Catálogo maestro: "Capacitación Excel", "Coaching ejecutivo", etc.
CatalogoOpcion    · clave · valor · etiqueta · activo  // Opciones configurables

ServicioAsignado · servicio_id (→ CatalogoServicio)
                  · solicitante_type · solicitante_id  (morph: Empresa | Candidato)
                  · interno_id  (User rol=interno · responsable que la ejecuta)
                  · estado[pendiente|activo|en_proceso|completado|cancelado]
                  · notas · cierre_resumen
                  · solicitado_por  (User que registró la solicitud)
                  · asignado_por    (User admin que asignó al interno)
                  · fecha_inicio · fecha_fin

PersonalExterno   · nombre · puesto · empresa_id · contacto
Bitacora          · usuario_id · modulo · accion · datos · ip
ConfiguracionSistema · clave · valor

ChatRoom · ChatMessage · ChatRoomMember   // Mensajería interna
```

**Tabla pivote**

```php
interno_servicio   // qué servicios sabe brindar cada interno
  · user_id (interno)
  · catalogo_servicio_id
```

---

## RELACIONES

```php
Empresa           → hasOne(User) · hasMany(Vacante, ServicioAsignado[morph])
Candidato         → hasOne(User) · hasMany(Postulacion, ServicioAsignado[morph])
Vacante           → belongsTo(Empresa) · hasMany(Postulacion)
Postulacion       → belongsTo(Vacante, Candidato)
ServicioAsignado → belongsTo(CatalogoServicio, User[interno_id])
                  → morphTo(solicitante)
User (interno)    → belongsToMany(CatalogoServicio) [pivote interno_servicio]
                  → hasMany(ServicioAsignado[interno_id])
```

---

## SERVICES (TODA la lógica vive aquí)

```php
ServicioAsignadoService
  registrar(array $datos, Model $solicitante): ServicioAsignado
    // crea solicitud · estado='pendiente' · solicitado_por=auth
  asignarInterno(ServicioAsignado $s, User $interno): void
    // valida capacidad del interno · suma horas · estado='activo' · asignado_por=auth
  cambiarEstado(ServicioAsignado $s, string $nuevoEstado): void
    // graba timestamps · si 'completado' → libera horas del interno
  cancelar(ServicioAsignado $s, string $motivo): void
  completar(ServicioAsignado $s, string $resumen): void

AsignacionInternoService
  candidatos(ServicioAsignado $s): Collection
    // users rol=interno · estado=activo · disponibilidad=disponible
    // con capacidad y especialidad. Ordena por menor carga.
  sugerirMejor(ServicioAsignado $s): ?User
  evaluarCompatibilidad(ServicioAsignado $s, User $interno): array
    // ['puede'=>bool, 'puntuacion'=>0-100, 'detalles'=>[...]]

VacanteService
  publicar(array $datos, Empresa $empresa): Vacante
  cerrar(Vacante $v): void
  match(Vacante $v): Collection<Candidato>
    // candidatos que cumplen estudios + área + experiencia + nivel_jerarquico

PostulacionService
  registrar(Vacante $v, Candidato $c): Postulacion
  mover(Postulacion $p, string $nuevoEstado): void

PersonalInternoService
  actualizarCapacidades(User $interno, array $servicios): void
  ocupacion(User $interno): array
  tieneCapacidadPara(User $interno, CatalogoServicio $s): bool
```

---

## ESTADOS

```
VACANTE              pendiente → activa → cerrada
                                      ↘ rechazada

POSTULACION          nueva → revision → entrevista → ofertada → contratada
                                                               ↘ rechazada

SOLICITUD_SERVICIO   pendiente → activo → en_proceso → completado
                                              ↘ cancelado

Timestamps automáticos en ServicioAsignado:
  en_proceso  → fecha_inicio
  completado  → fecha_fin
  cancelado   → fecha_fin
```

---

## REGLAS DURAS

```
✔ TODA la lógica de negocio en Services. Controlador SOLO recibe y responde.
✔ TODA la validación en FormRequests. Nada en el controlador.
✔ Eager load OBLIGATORIO en queries de colección (with([...])).
✔ User.carga_trabajo_horas SOLO se modifica desde ServicioAsignadoService.
✔ Al completar/cancelar una ServicioAsignado, SIEMPRE liberar horas del interno.
✔ Un interno NO puede tomar una solicitud si no tiene esa especialidad en interno_servicio.
✔ nivel_jerarquico aplica SOLO a Vacante. NO a ServicioAsignado.
✔ Variables, comentarios, nombres de método: TODO en español.
✔ Métodos pequeños y reutilizables. Si pasa de 30 líneas, refactor.
✖ Vacante.tipo_servicio NO existe (era confuso). Vacante = reclutamiento siempre.
✖ Modelo Tarea NO existe. Solo ServicioAsignado para todo lo no-reclutamiento.
```

---

## ACCESO POR ROL

```php
admin     → todo
empresa   → SUS vacantes, SUS postulaciones, SUS solicitudes de servicio
candidato → SU perfil, SUS postulaciones, SUS solicitudes de servicio
interno   → SUS solicitudes asignadas, SUS capacidades, SU carga

class PoliticaServicioAsignado {
  verCualquiera(User $u): bool      => in_array($u->rol, ['admin','interno']);
  ver(User $u, ServicioAsignado $s): bool
    => $u->rol === 'admin'
    || ($u->rol === 'interno' && $s->interno_id === $u->id)
    || $s->solicitado_por === $u->id;
  asignar(User $u, ServicioAsignado $s): bool => $u->rol === 'admin';
  completar(User $u, ServicioAsignado $s): bool
    => $u->rol === 'admin' || ($u->rol === 'interno' && $s->interno_id === $u->id);
}
```

---

## RUTAS (web.php por rol, prefijos claros)

```
/                        welcome
/dashboard               redirige según rol

/admin/*                 (middleware role:admin)
  dashboard · empresas · candidatos · vacantes · catalogo · catalogos
  solicitudes-servicio (CRUD + kanban + asignar inteligente)
  personal-interno (CRUD + capacidades)
  configuracion · reportes · buscar

/empresa/*               (middleware role:empresa)
  dashboard · vacantes (CRUD) · solicitudes-servicio (CRUD)
  postulaciones (mover estado)

/candidato/*             (middleware role:candidato)
  dashboard · solicitud · vacantes (listar + postular) · postulaciones
  solicitudes-servicio (solicitar curso)

/interno/*               (middleware role:interno)
  dashboard · solicitudes-asignadas (tomar / completar / cancelar)
  capacidades (qué servicios sé brindar)

/chat/*                  (mensajería entre roles)
```

---

## CONVENCIONES DE CÓDIGO

```
Modelos        : singular, StudlyCase, español       (Vacante, ServicioAsignado)
Tablas         : plural, snake_case, español         (vacantes, solicitudes_servicio)
Controladores  : modelo + Controller                 (ServicioAsignadoController)
Services       : modelo + Service                    (ServicioAsignadoService)
FormRequests   : accion + modelo + Request           (CrearServicioAsignadoRequest)
Políticas      : Politica + modelo                   (PoliticaServicioAsignado)
Métodos        : verbo + sustantivo, camelCase       (asignarInterno, liberarHoras)
Variables      : español                             ($solicitud, $interno, $vacante)
Abreviaciones  : evitarlas. Preferir nombres claros.
```

---

## EAGER LOADING (obligatorio)

```php
ServicioAsignado::with(['servicio','interno','solicitante','solicitadoPor','asignadoPor'])
Vacante::with(['empresa','postulaciones.candidato'])
Postulacion::with(['vacante.empresa','candidato'])
User::with(['serviciosCapacitados'])  // para internos
```

---

## REGLAS PARA CLAUDE CODE

```
· Respuestas ≤ 30 líneas salvo solicitud explícita
· Código primero · explicación solo si se pide
· No repetir contexto de este archivo
· No explicar Laravel básico
· Todo en español: variables, comentarios, mensajes, etiquetas
· Lógica de negocio SIEMPRE en Service
· Validación SIEMPRE en FormRequest
· Antes de tocar User.carga_trabajo_horas: usar ServicioAsignadoService
· Antes de duplicar lógica: extraer a método reutilizable
· Si el flujo confunde al usuario: pausar y rediseñar, NO parchar
```

<!-- SPECKIT START -->
For additional context about technologies to be used, project structure,
shell commands, and other important information, read the current plan
<!-- SPECKIT END -->
