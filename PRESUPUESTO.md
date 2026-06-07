# Presupuesto — SistemaRH (Sistema de Recursos Humanos)

> Fecha: 02 de junio de 2026  
> Proyecto: SistemaRH — Laravel 13 + Livewire 3 + SQLite/MySQL  
> Alcance: Terminación, módulos faltantes, calidad y despliegue.

---

## 1. Resumen Ejecutivo

El sistema cuenta con una base sólida y funcional en aproximadamente **85–90%**. Los módulos core (autenticación, empresas, candidatos, vacantes, postulaciones, servicios asignados, catálogos, chat, personal interno/externo y dashboard administrativo) están **completos y operativos**.

Sin embargo, existen módulos documentados que **no han sido implementados**, funcionalidades parciales y una cobertura de pruebas prácticamente nula que deben atenderse para considerar el sistema como producto terminado.

---

## 2. Estado Actual por Módulo

| Módulo | Estado |
|--------|--------|
| Autenticación (Breeze multirol) | ✅ Completo |
| Admin Dashboard + Reportes | ✅ Completo |
| Gestión de Empresas | ✅ Completo |
| Gestión de Candidatos + Solicitud multipaso | ✅ Completo |
| Vacantes / Reclutamiento + Matching | ✅ Completo |
| Postulaciones + Kanban | ✅ Completo |
| Servicios Asignados (tareas) + Kanban | ✅ Completo |
| Catálogo de Opciones | ✅ Completo |
| Catálogo de Servicios | ✅ Completo |
| Personal Externo / Interno | ⚠️ Parcial (falta editar interno) |
| Chat Interno | ✅ Completo |
| Workflow de Aprobaciones | ⚠️ Parcial (solo aplica a vacantes) |
| Notificaciones | ⚠️ Parcial (tabla existe, sin panel ni emails) |
| Bitácora de Actividad | ⚠️ Parcial (solo últimas 20 en config) |
| Activity Log (Spatie) | ⚠️ Instalado pero sin interfaz |
| Tickets + SLA | ❌ Inexistente (a pesar de documentación) |
| Tests de Dominio | ❌ Inexistentes (solo auth Breeze) |
| API REST | ❌ No contemplado |

---

## 3. Desglose de Trabajo Propuesto

### Fase 1 — Módulos Faltantes (Críticos)

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 1.1 | **Tickets / Mesa de Ayuda**: modelo, migraciones, políticas, CRUD admin, vistas de listado/creación/detalle, estados (abierto → en_proceso → resuelto → cerrado), asignación a internos, comentarios, carga de archivos adjuntos. | 24 h |
| 1.2 | **SLA Inteligente**: integrar `config/sla.php` + `SlaInteligenteService` al ciclo de vida del ticket, cálculo de vencimiento por prioridad, indicadores visuales (badges de urgencia), alertas de vencimiento próximo. | 10 h |
| 1.3 | **Workflow de Aprobaciones Real**: conectar `WorkflowService` a aprobaciones de empresas y candidatos en `AdminController`, respetar `config/workflow.php` (manual vs auto), notificar al solicitante. | 8 h |
| 1.4 | **Editar Personal Interno**: rutas, controlador, vista, validaciones, actualización de capacidades. | 6 h |
| | **Subtotal Fase 1** | **48 h** |

### Fase 2 — Notificaciones y Comunicaciones

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 2.1 | **Sistema de Notificaciones**: panel de notificaciones por rol (dropdown/badge), marcar como leída, notificaciones en tiempo real vía polling Livewire, limpieza automática. | 12 h |
| 2.2 | **Emails Transaccionales**: plantillas para — bienvenida/registro, aprobación/rechazo de empresa/candidato/vacante, asignación de ticket, recordatorio de SLA, cambio de estado de postulación. | 14 h |
| 2.3 | **Notificaciones Push Internas**: eventos de negocio que generen notificaciones (postulación nueva, ticket asignado, tarea completada, aprobación pendiente). | 8 h |
| | **Subtotal Fase 2** | **34 h** |

### Fase 3 — Bitácora, Auditoría y Reportes

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 3.1 | **Bitácora Completa**: panel admin con filtros (usuario, módulo, fecha, acción), búsqueda, paginación, exportación CSV/PDF de logs. | 10 h |
| 3.2 | **Activity Log (Spatie)**: interfaz de consulta vinculada a registros (ver historial de cambios de una empresa, candidato o vacante específica). | 8 h |
| 3.3 | **Reportes Adicionales**: reporte de tickets (por estado, SLA cumplido/vencido, por asignado), reporte de actividad de internos (tareas completadas, tiempo promedio). | 10 h |
| | **Subtotal Fase 3** | **28 h** |

### Fase 4 — Testing y Calidad

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 4.1 | **Tests Unitarios**: modelos, casts JSON, scopes, policies (`CandidatoPolicy`, `VacantePolicy`). | 10 h |
| 4.2 | **Tests de Feature**: controladores de admin, empresa, candidato, interno; flujos de aprobación, postulación, asignación de servicios, tickets. | 18 h |
| 4.3 | **Tests de Servicios**: `WorkflowService`, `SlaInteligenteService`, `DashboardService`, `BitacoraService`, matching de compatibilidad. | 10 h |
| 4.4 | **Revisión de Seguridad**: validar autorización en rutas, prevenir mass assignment en campos JSON, sanitizar inputs de búsqueda, revisar subida de archivos. | 8 h |
| | **Subtotal Fase 4** | **46 h** |

### Fase 5 — Refinamiento Frontend y UX

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 5.1 | **Responsive y Mobile**: revisar tablas admin, kanban en móvil, sidebar colapsable, formularios multipaso del candidato. | 12 h |
| 5.2 | **Optimización de Consultas**: eager loading faltante, índices en búsquedas frecuentes, paginación en listados grandes. | 8 h |
| 5.3 | **Páginas Legales Editable**: CRUD admin para términos y condiciones / aviso de privacidad con editor enriquecido. | 6 h |
| 5.4 | **Landing Page Pública**: página de inicio corporativa (servicios, contacto, login/registro). | 8 h |
| | **Subtotal Fase 5** | **34 h** |

### Fase 6 — Despliegue y Entrega

| Ítem | Descripción | Horas Est. |
|------|-------------|------------|
| 6.1 | **Configuración de Producción**: archivo `.env.production`, optimización (`php artisan optimize`, `config:cache`, `route:cache`), configuración de colas (database/redis), scheduler para tareas automáticas. | 6 h |
| 6.2 | **Documentación Técnica**: guía de instalación, diagrama de base de datos, documentación de API interna (si aplica), manual de administrador. | 8 h |
| 6.3 | **Deploy y Smoke Tests**: deploy en servidor (VPS/Cloud), SSL, dominio, pruebas de flujo end-to-end en producción. | 8 h |
| | **Subtotal Fase 6** | **22 h** |

---

## 4. Resumen de Horas

| Fase | Horas | % del Proyecto |
|------|-------|----------------|
| 1. Módulos Faltantes (Críticos) | 48 h | 26% |
| 2. Notificaciones y Comunicaciones | 34 h | 18% |
| 3. Bitácora, Auditoría y Reportes | 28 h | 15% |
| 4. Testing y Calidad | 46 h | 25% |
| 5. Refinamiento Frontend y UX | 34 h | 18% |
| 6. Despliegue y Entrega | 22 h | 12% |
| **Contingencia (15%)** | **~32 h** | — |
| **TOTAL ESTIMADO** | **~244 h** | **100%** |

> *Equivalente aproximado: **6 semanas** de trabajo dedicado (1 desarrollador senior a 40 h/semana).*

---

## 5. Propuesta Económica

Tarifa base de referencia para desarrollo Laravel senior: **$35–50 USD/hora**.

| Escenario | Tarifa/Hr | Horas | Subtotal | Contingencia 15% | **Total** |
|-----------|-----------|-------|----------|------------------|-----------|
| 🟢 **Conservador** | $35 USD | 244 h | $8,540 USD | $1,281 USD | **$9,821 USD** |
| 🟡 **Estándar** | $42 USD | 244 h | $10,248 USD | $1,537 USD | **$11,785 USD** |
| 🔵 **Premium** | $50 USD | 244 h | $12,200 USD | $1,830 USD | **$14,030 USD** |

> Los precios no incluyen IVA/impuestos.  
> Hosting, dominio, licencias de terceros (email/SMS) y diseño gráfico externo se cotizan aparte.

---

## 6. Plan de Pagos Sugerido

| Entrega | Hitos | % del Total |
|---------|-------|-------------|
| **Anticipo** | Inicio de Fases 1 y 2 | 30% |
| **Pago 2** | Cierre Fase 3 (Bitácora + Reportes) | 30% |
| **Pago 3** | Cierre Fase 4 (Testing y Calidad) | 20% |
| **Pago Final** | Entrega productiva + Capacitación | 20% |

---

## 7. Extras Opcionales (Fuera del Alcance Base)

| Extra | Descripción | Estimación |
|-------|-------------|------------|
| API REST con Sanctum | Para app móvil futura o integraciones | +40 h / $1,680 USD |
| App Móvil (PWA / Flutter) | Versión móvil ligera para candidatos/internos | +80–120 h |
| Integración WhatsApp Business | Notificaciones por WhatsApp (
