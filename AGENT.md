# AGENT.md

## Proposito
Este archivo define como trabajar en `SistemaRH_Laravel`.
La prioridad es conservar la logica de negocio del sistema RH y llevarla a una experiencia simple, profesional, rapida y facil de usar.

## Regla general
- Tomar la idea de negocio del sistema PHP anterior solo como referencia funcional.
- No copiar su interfaz, atajos ni complejidad innecesaria.
- Cada cambio debe simplificar el uso sin perder capacidad operativa.
- Mantener la logica de negocio, simplificar UI/UX y priorizar rendimiento, seguridad y mantenibilidad.

## Modulos del sistema
- Autenticacion y acceso
- Dashboard por rol
- Solicitudes
- Catalogos del sistema
- Catalogo de servicios
- Empresas
- Candidatos
- Admin
- Interno / tareas
- Servicios asignados
- Chat
- Notificaciones
- SLA
- Bitacora / auditoria
- Configuracion
- Reportes y consultas

## Principios obligatorios
- Primero entender el negocio, despues tocar codigo.
- No asumir una regla si se puede leer en el codigo, en este archivo o en la documentacion del proyecto.
- Preferir flujos cortos: una accion principal por pantalla.
- Evitar pantallas duplicadas para la misma tarea.
- Todo texto visible al usuario, validaciones, mensajes y comentarios nuevos deben estar en espanol.
- No introducir placeholders, rutas vacias, enlaces `#` ni secciones "en construccion" en el flujo real.
- No usar IDs fijos, datos dummy ni magia escondida en el negocio.
- No revertir trabajo ajeno.
- Mantener cambios pequenos, claros y verificables.

## Mapa funcional del sistema

### Modulo central: Solicitudes
- `Solicitudes` es el modulo principal del negocio.
- `Vacantes` viven dentro de `Solicitudes`, no como menu superior separado.
- La interfaz debe mostrar un solo punto de entrada para crear, consultar y seguir solicitudes.
- Cada solicitud puede guardar requisitos estructurados: nivel de estudios minimo, area o carrera requerida y experiencia minima.
- El sistema debe clasificar candidatos en `Aptos`, `Dudosos` y `No aptos` segun esos requisitos.
- El admin puede forzar una asignacion, pero siempre dejando un motivo claro de excepcion.

### Tipos de solicitud
- Vacante
- Entrevista
- Soporte
- Capacitacion
- Seguimiento

### Entrevista simple
- La entrevista no es un modulo pesado.
- Se maneja como accion y estado dentro de `Solicitudes`.
- El boton o accion principal debe ser `Ya entrevistado`.
- El seguimiento se limita a estados claros y una bitacora breve.

### Servicios por jerarquia
- Los servicios se clasifican por jerarquia de atencion.
- La jerarquia es un catalogo maestro.
- Ejemplos: Operativo, Supervision, Gerencia, Direccion.
- La jerarquia influye en prioridad, asignacion y SLA.

### Catalogos del sistema
- Todas las opciones repetibles del sistema deben vivir en un catalogo administrable.
- La clave tecnica de una opcion es estable; lo que cambia es la etiqueta visible, el orden y el estado.
- Los catalogos del sistema no deben depender de arrays dispersos en vistas o controladores.
- La pantalla de catalogos debe mostrarse agrupada por grupo, no como una tabla plana repetitiva.
- Los registros base del sistema no se eliminan por accidente; se editan con cuidado y se protegen.
- Los grupos minimos incluyen: roles, estados de empresa, estados de candidato, estados de solicitud, estados de postulacion, estados de ticket, tipos de servicio, niveles de estudio, jerarquias, categorias, prioridades, tipos de chat y disponibilidad externa.

### Roles
- Admin: centro operativo.
- Empresa: crea solicitudes, mantiene perfil y da seguimiento.
- Candidato: completa perfil y solicita entrevista.
- Interno: toma y resuelve tareas asignadas.

### Soporte de operacion
- Chat
- Notificaciones
- SLA
- Bitacora / auditoria
- Configuracion
- Catalogos
- Reportes y consultas

## Catalogos maestros
Todo selector, opcion y motivo del sistema debe venir de un catalogo administrable o de una lista formal de dominio. No quemar opciones en la vista si el negocio puede cambiar.

Catalogos minimos:
  - Tipos de solicitud
  - Niveles de estudio
  - Jerarquias de servicio
- Estados de solicitud
- Prioridades
- Areas
- Motivos de cierre
- Motivos de rechazo
- Estados operativos
- Roles
- Configuracion general visible al admin

Regla de catalogos:
- Si una opcion se muestra en la UI y puede cambiar en el futuro, debe salir de un catalogo.
- Si una opcion define seguridad del sistema, puede quedar fija en codigo, pero su uso debe ser claro y consistente.
- Las claves de catalogo son estables; no se cambian una vez usadas en procesos reales.

## Flujo por rol

### Admin
- Revisa la cola de solicitudes.
- Clasifica por tipo y jerarquia.
- Aprueba, rechaza, reasigna o marca `Ya entrevistado`.
- Supervisa SLA, chat y bitacora.
- Gestiona catalogos y configuracion.

### Empresa
- Entra al modulo `Solicitudes`.
- Crea solicitudes de tipo Vacante, Soporte, Capacitacion o Seguimiento.
- Completa los datos minimos, requisitos de compatibilidad y espera atencion.
- Ve solo lo propio.
- No administra candidatos fuera de lo asignado.

### Candidato
- Completa su perfil.
- Solicita entrevista desde el flujo simple.
- Consulta el estado de su proceso.
- No recibe pantallas innecesarias ni multiples pasos.
- Su perfil debe permitir comparar escolaridad, area/carrera y experiencia contra los requisitos de una solicitud.

### Interno
- Ve solo tareas asignadas.
- Toma tarea.
- Resuelve tarea.
- Deja una nota o cierre breve.
- No compite con admin; ejecuta trabajo operativo.

### Servicios asignados
- Cada servicio asignado se registra contra un catalogo de servicio.
- La tarea se asigna a un responsable interno.
- El interno toma la tarea, la procesa y la cierra con nota breve.
- El admin crea y supervisa la cola.

## Reglas tecnicas
- Controladores delgados.
- Logica de negocio en servicios.
- Consultas optimizadas con eager loading cuando haga falta.
- Usar paginacion para listados.
- Evitar consultas repetidas.
- Reutilizar catalogos y servicios.
- No hardcodear `vacante_id = 1`, estados sueltos o textos de negocio dentro de la logica.
- No mezclar logica de vista con reglas de negocio.
- Si una pantalla solo muestra datos, no debe recalcular demasiada logica ahi mismo.
- La compatibilidad de candidatos debe calcularse en un servicio dedicado para que el controlador solo organice datos.

## Reglas de calidad y UX
- Interfaz limpia, sobria y profesional.
- Menos clics, menos campos, menos pasos.
- Cada pantalla debe tener una accion principal clara.
- Los vacios deben resolverse con un estado amigable, no con placeholders.
- Los mensajes de exito y error deben decir exactamente que paso.
- El lenguaje debe ser claro, directo y en espanol.
- La navegacion debe ser simple y consistente por rol.

## Reglas de contenido
- Todo comentario nuevo en codigo debe estar en espanol.
- Todo texto visible al usuario debe estar en espanol.
- Evitar anglicismos innecesarios en labels, titulos y mensajes.
- Mantener el tono profesional, simple y util.

## Reglas de trabajo
- Antes de cambiar algo, revisar el contexto actual del modulo.
- Si una regla de negocio no esta clara, buscarla en el codigo o en este archivo antes de inventarla.
- Si el cambio afecta procesos reales, agregar o actualizar pruebas.
- Si el cambio toca UI, validar que siga simple y facil de usar.
- Si una ruta o flujo deja de tener sentido, eliminarlo o redirigirlo bien; no dejarlo vivo por inercia.

## No hacer
- No copiar el sistema viejo completo.
- No recrear pantallas complicadas solo porque existian antes.
- No dejar menu, rutas o botones muertos.
- No introducir pasos extra para el usuario sin valor real.
- No usar datos de prueba en flujo productivo.
- No esconder reglas de negocio en la vista.
- No usar nombres, estados o textos inconsistentes entre modulos.

## Criterio final
Un cambio esta bien hecho solo si:
- cumple la logica del negocio,
- se entiende rapido,
- requiere pocos pasos,
- se ve profesional,
- esta en espanol,
- y no complica el mantenimiento.
