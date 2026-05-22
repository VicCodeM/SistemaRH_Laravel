<!--
SYNC IMPACT REPORT
==================
Versión: (plantilla sin rellenar) → 1.0.0
Tipo de cambio: Ratificación inicial (relleno completo de la plantilla)

Principios definidos:
  I.   Preservación de lo Existente (No Romper)
  II.  Roles y Autorización Obligatoria
  III. Validación en Form Requests (NO-NEGOCIABLE)
  IV.  Lógica en Servicios, Controladores Delgados
  V.   Pruebas para Nueva Funcionalidad

Secciones añadidas:
  - Restricciones Técnicas y Convenciones
  - Flujo de Desarrollo y Calidad
  - Gobernanza

Secciones eliminadas: ninguna

Plantillas y artefactos revisados:
  ✅ .specify/templates/plan-template.md  — "Constitution Check" deriva del archivo de constitución (genérico, sin cambios)
  ✅ .specify/templates/spec-template.md  — alineado, sin secciones obligatorias afectadas
  ✅ .specify/templates/tasks-template.md — categorías de tareas compatibles (incluye pruebas y validación)
  ✅ CLAUDE.md del proyecto — consistente con los principios (lógica en Services, validación en FormRequests, español)

TODOs diferidos: ninguno
-->

# SistemaRH_Laravel Constitution

Sistema de Recursos Humanos construido en Laravel 13. Conecta empresas con candidatos
y administra servicios de RH bajo los roles `admin`, `empresa`, `candidato` e `interno`.
Esta constitución rige cómo se agrega y mantiene funcionalidad SIN romper lo que ya
opera en producción con datos reales.

## Core Principles

### I. Preservación de lo Existente (No Romper)

La base de datos contiene datos reales de empresas, candidatos y postulaciones. Por tanto:

- NUNCA se modifican migraciones existentes; los cambios de esquema SIEMPRE se hacen con
  migraciones nuevas usando `alter table`.
- NUNCA se borran tablas existentes ni se cambian o eliminan columnas existentes.
- NUNCA se modifican los modelos principales (User, Empresa, Vacante, Candidato,
  Postulacion, ServicioAsignado, CatalogoServicio, PersonalExterno, ChatRoom, ChatMessage,
  ChatRoomMember, Bitacora, ConfiguracionSistema, CatalogoOpcion, ComentarioServicio) sin
  autorización explícita.
- SIEMPRE se reutilizan los modelos existentes; está prohibido crear modelos duplicados
  para conceptos que ya existen.

**Rationale**: una migración o cambio de columna destructivo sobre datos reales es
irreversible y de alto impacto. La regla elimina esa clase de error de raíz.

### II. Roles y Autorización Obligatoria

- SIEMPRE se respetan los cuatro roles del sistema: `admin`, `empresa`, `candidato`,
  `interno`.
- Toda ruta que muta datos DEBE estar protegida con una Policy o con el middleware `role`.
- Ningún endpoint queda accesible a un rol que no le corresponde según el flujo de negocio.

**Rationale**: el control de acceso por rol es la frontera de seguridad del sistema;
dejarlo implícito o "para después" abre fugas de datos entre empresas y candidatos.

### III. Validación en Form Requests (NO-NEGOCIABLE)

- TODA validación de entrada vive en un Form Request dedicado.
- NUNCA se valida dentro de un controlador.
- Las reglas rechazan datos inválidos antes de que toquen la capa de negocio.

**Rationale**: centralizar la validación la hace testeable, reutilizable y auditable, y
mantiene a los controladores libres de lógica condicional dispersa.

### IV. Lógica en Servicios, Controladores Delgados

- TODA la lógica de negocio reside en clases de Servicio (`app/Services`).
- Los controladores solo reciben la petición, delegan en un Servicio y responden.
- Los estados del dominio se gestionan mediante `CatalogoOpcion`, nunca hardcodeados.

**Rationale**: separar negocio de transporte permite probar la lógica de forma aislada,
reutilizarla entre controladores y evitar el "código espagueti".

### V. Pruebas para Nueva Funcionalidad

- SIEMPRE se agregan pruebas (unitarias y/o de feature) para cada nueva funcionalidad.
- Las pruebas cubren al menos el camino feliz y las reglas de autorización por rol.
- Una funcionalidad sin pruebas no se considera terminada.

**Rationale**: las pruebas son la red que protege un sistema en producción de regresiones
al seguir agregando módulos sobre datos reales.

## Restricciones Técnicas y Convenciones

**Stack fijo**: Laravel 13, autenticación con Laravel Breeze, Tailwind CSS, vistas Blade
con el layout actual, rutas en `web.php` con middleware `auth` y `role`.

**Convenciones de nombres**:

- Tablas en plural y snake_case: `empresas`, `vacantes`, `candidatos`, `postulaciones`.
- Modelos en singular y StudlyCase: `Empresa`, `Vacante`, `Candidato`, `Postulacion`.
- Controladores agrupados por carpeta de rol: `Admin/`, `Empresa/`, `Candidato/`,
  `Interno/`, `Auth/`.
- Rutas con prefijo por rol: `admin/`, `empresa/`, `candidato/`, `interno/`.
- Estados manejados por `CatalogoOpcion`, nunca hardcodeados.

**Idioma**: todo el código, variables, comentarios, interfaz y mensajes en español.

## Flujo de Desarrollo y Calidad

- SIEMPRE se respeta el flujo de negocio central:
  **empresa publica vacante → candidato postula → admin asigna servicio**.
- Los commits son descriptivos y están escritos en español.
- Antes de dar por terminada una funcionalidad se verifica: cumplimiento de los principios,
  validación en Form Request, autorización por rol y pruebas agregadas.
- Cualquier complejidad adicional debe justificarse; si no aporta valor, se elimina (YAGNI).

## Governance

Esta constitución prevalece sobre cualquier otra práctica del proyecto. En caso de
conflicto entre una conveniencia puntual y un principio aquí descrito, gana el principio.

- **Enmiendas**: toda modificación a esta constitución se documenta en el Sync Impact
  Report, se versiona y se justifica.
- **Versionado** (semántico):
  - MAJOR: eliminación o redefinición incompatible de principios o reglas de gobernanza.
  - MINOR: se añade un principio/sección o se amplía materialmente la guía.
  - PATCH: aclaraciones, redacción o correcciones sin cambio semántico.
- **Cumplimiento**: toda revisión de cambios verifica el apego a estos principios. Un cambio
  que viole un principio NO se integra hasta corregirse o justificarse formalmente.
- La guía operativa de desarrollo en tiempo real se mantiene en `CLAUDE.md`, que debe
  permanecer consistente con esta constitución.

**Version**: 1.0.0 | **Ratified**: 2026-05-21 | **Last Amended**: 2026-05-21
