# Reporte de Seguridad — SistemaRH_Laravel

**Fecha:** 2026-05-25  
**Proyecto:** SistemaRH (Laravel 13 + Livewire 4.3)  
**Alcance:** Código fuente, configuración, dependencias, control de acceso, manejo de datos sensibles y exposición de secretos.

---

## Resumen Ejecutivo

Se identificaron **3 hallazgos críticos**, **7 de severidad alta**, **6 de severidad media** y **4 de severidad baja**. Los riesgos más urgentes son la exposición del `APP_KEY`, la configuración de debug activa y la ausencia de rate limiting en rutas administrativas. No se detectaron inyecciones SQL directas ni XSS reflejados en flujos críticos, pero existen vectores de **CSV Injection** y **fuerza bruta** sin mitigar.

---

## 🔴 Crítico

### C1. `APP_KEY` expuesta en archivo `.env` local
- **Archivo:** `.env`
- **Hallazgo:** `APP_KEY=base64:lNQX3L8YKDLumKbws1Vmyqi20aeOCIHBTZ/TdR69828=`
- **Riesgo:** Si el servidor web expone accidentalmente `.env` (por mala configuración de `.htaccess`, despliegue incorrecto, o backup descargable), un atacante puede descifrar cookies de sesión, generar firmas CSRF válidas y realizar ataques de deserialización en Laravel.
- **Corrección:**
  1. Rotar la clave inmediatamente: `php artisan key:generate`
  2. Asegurar que el servidor web bloquea el acceso a archivos que empiecen con `.` (ya está en `.htaccess`, pero verificar en nginx/Apache).
  3. Nunca subir `.env` a repositorios (ya está en `.gitignore`; validar que no esté trackeado).

### C2. `APP_DEBUG=true` en entorno local
- **Archivo:** `.env`
- **Hallazgo:** `APP_DEBUG=true`
- **Riesgo:** Si se despliega en producción sin cambiar a `false`, se exponen stack traces completos, variables de entorno, rutas internas y consultas SQL en pantalla, facilitando reconocimiento para ataques posteriores.
- **Corrección:**
  ```env
  APP_DEBUG=false
  ```
  Adicionalmente, agregar en el pipeline de despliegue una validación que falle si `APP_DEBUG=true`.

### C3. Sesiones sin encriptar (`SESSION_ENCRYPT=false`)
- **Archivo:** `.env` → `config/session.php`
- **Hallazgo:** `SESSION_ENCRYPT=false`
- **Riesgo:** En servidores compartidos o en caso de acceso al filesystem, los archivos de sesión almacenan datos en texto plano (IDs de usuario, tokens CSRF, flashes).
- **Corrección:**
  ```env
  SESSION_ENCRYPT=true
  ```
  Requiere que `APP_KEY` esté protegido (ver C1).

---

## 🟠 Alto

### A1. Middleware `CheckRole` no verifica estado de cuenta
- **Archivo:** `app/Http/Middleware/CheckRole.php`
- **Hallazgo:** Solo valida el campo `rol`. No verifica si la cuenta está `activa`, `pendiente` o `bloqueada`.
- **Riesgo:** Un usuario `bloqueado` o `pendiente` con sesión activa puede seguir navegando y ejecutando acciones si su rol coincide con el de la ruta.
- **Corrección:** Agregar verificación de estado:
  ```php
  if (! $request->user()->estaActivo()) {
      abort(403, 'Tu cuenta no está activa.');
  }
  ```

### A2. Ausencia total de Rate Limiting en rutas de dominio
- **Archivo:** `routes/web.php`
- **Hallazgo:** Las rutas de `admin`, `empresa`, `candidato` e `interno` no tienen throttle. Solo login y verificación de email lo tienen.
- **Riesgo:** Fuerza bruta en endpoints sensibles (aprobar/rechazar empresas, crear usuarios, exportar CSVs, búsquedas costosas) puede causar DoS o manipulación masiva de datos.
- **Corrección:** Agregar grupo de middleware `throttle` en rutas críticas:
  ```php
  Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
      // Rutas de empresa, candidato, interno
  });
  
  Route::middleware(['auth', 'verified', 'role:admin', 'throttle:120,1'])->prefix('admin')->group(function () {
      // Rutas administrativas
  });
  ```

### A3. Posible CSV Injection en exportaciones
- **Archivos:** `app/Services/ExportService.php`, `app/Services/ExportadorService.php`
- **Hallazgo:** Los datos de candidatos, empresas o personal se escriben directamente en CSV sin sanitización de prefijos peligrosos (`=`, `+`, `-`, `@`, `\t`, `\r`).
- **Riesgo:** Si un candidato ingresa `=CMD|' /C calc'!A0` en su nombre, al abrir el CSV en Excel se ejecuta código.
- **Corrección:** Sanitizar cada celda antes de `fputcsv`:
  ```php
  private function sanitizarCsv(mixed $valor): string
  {
      $texto = "\t" . (string) $valor; // prefijo tab previene ejecución de fórmulas
      return str_replace(["\r", "\n"], '', $texto);
  }
  ```

### A4. Archivos subidos (CVs y avatares) accesibles públicamente sin restricción
- **Archivos:** `app/Http/Controllers/Admin/PersonalExternoController.php`, `app/Http/Controllers/ProfileController.php`
- **Hallazgo:** Los CVs se guardan en `storage/app/public/cv-personal-externo` y los avatares en `storage/app/public/avatars`. El disk `public` está vinculado a `public/storage`, por lo que cualquiera con la URL puede descargarlos.
- **Riesgo:** Fuga de información personal (CVs con datos de identidad). Adivinación de rutas por fuerza bruta.
- **Corrección:**
  - Servir archivos privados a través de un controller con autorización, en lugar de URLs directas:
    ```php
    Route::get('/cv/{personalExterno}', [PersonalExternoController::class, 'descargarCv'])
        ->middleware('auth');
    ```
  - Mover al disk `local` o `private`:
    ```php
    $request->file('cv')->store('cv-personal-externo', 'local');
    ```

### A5. `User` tiene campos sensibles en `fillable`
- **Archivo:** `app/Models/User.php`
- **Hallazgo:** El atributo `#[Fillable([..., 'rol', 'estado', 'carga_trabajo_horas', ...])]` incluye campos de control de acceso y asignación de trabajo.
- **Riesgo:** Si en algún futuro se usa `$request->all()` o `$request->validated()` con más campos de los previstos en un Form Request, un usuario podría auto-escalar privilegios o modificar su carga laboral.
- **Corrección:** Reducir `fillable` al mínimo necesario para registro público (`name`, `email`, `password`). Los campos administrativos (`rol`, `estado`, etc.) deben asignarse explícitamente en controladores admin:
  ```php
  $user = User::create($request->only(['name', 'email', 'password']));
  $user->rol = 'candidato'; // asignación explícita
  $user->save();
  ```

### A6. `database/database.sqlite` presente en el repositorio
- **Archivo:** `database/database.sqlite`
- **Hallazgo:** El archivo SQLite de desarrollo existe en disco y **no está en `.gitignore`** (solo `.env` y backups están ignorados).
- **Riesgo:** Si se sube a Git, se expone la base de datos completa con datos de prueba (incluyendo hashes de contraseña `password`).
- **Corrección:**
  ```gitignore
  /database/*.sqlite
  /database/*.sqlite-journal
  ```
  Y ejecutar: `git rm --cached database/database.sqlite`

### A7. Contraseñas de demostración hardcodeadas en seeders
- **Archivos:** `database/seeders/DatabaseSeeder.php`, `database/seeders/DummyDataSeeder.php`
- **Hallazgo:** `Hash::make('password')` y mensajes informativos con credenciales.
- **Riesgo:** Si `DummyDataSeeder` se ejecuta accidentalmente en producción, se crean cuentas con contraseña conocida.
- **Corrección:**
  - Agregar protección en `DummyDataSeeder`:
    ```php
    if (app()->environment('production')) {
        $this->command->error('No ejecutar DummyDataSeeder en producción.');
        return;
    }
    ```
  - Documentar explícitamente que el seeder es solo para desarrollo.

---

## 🟡 Medio

### M1. Falta de headers de seguridad HTTP
- **Archivo:** `bootstrap/app.php` / middleware global
- **Hallazgo:** No se configuran `X-Frame-Options`, `Content-Security-Policy`, `X-Content-Type-Options`, `Strict-Transport-Security` ni `Referrer-Policy`.
- **Riesgo:** Clickjacking, MIME-sniffing, ejecución de scripts inline inyectados.
- **Corrección:** Crear un middleware de seguridad:
  ```php
  // app/Http/Middleware/SecurityHeaders.php
  public function handle($request, Closure $next)
  {
      $response = $next($request);
      $response->headers->set('X-Frame-Options', 'DENY');
      $response->headers->set('X-Content-Type-Options', 'nosniff');
      $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
      $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
      return $response;
  }
  ```
  Registrarlo como middleware global en `bootstrap/app.php`.

### M2. `pagina_web` en empresa sin validación de URL
- **Archivo:** `app/Http/Controllers/Admin/AdminController.php` (actualizarEmpresa)
- **Hallazgo:** El campo `pagina_web` se valida como `string|max:255` sin formato URL.
- **Riesgo:** Un valor como `javascript:alert(document.cookie)` podría renderizarse en una vista sin escapado adecuado y ejecutar XSS.
- **Corrección:** Cambiar la regla de validación:
  ```php
  'pagina_web' => ['nullable', 'url', 'max:255'],
  ```

### M3. Chat accesible por usuarios no-admin sin validación en ruta
- **Archivo:** `routes/web.php:202-207`
- **Hallazgo:** La ruta `GET /chat/{room}` no tiene middleware de verificación de membresía. Aunque el componente Livewire sí valida, un usuario podría acceder a la vista shell del chat sin ser miembro.
- **Riesgo:** Leak de metadatos (nombre de la sala, estructura HTML) y posible bypass futuro.
- **Corrección:** Agregar middleware o policy:
  ```php
  Route::get('/{room}', fn (ChatRoom $room) => view('chat.show', compact('room')))
      ->can('view', 'room')
      ->name('chat.show');
  ```

### M4. `orderByRaw` sin validación estricta de parámetros
- **Archivos:** `app/Http/Controllers/Admin/ServicioAsignadoController.php`, `app/Http/Controllers/Interno/InternoController.php`, etc.
- **Hallazgo:** Se usa `orderByRaw("CASE estado WHEN 'activo' THEN 1 ...")` con strings hardcodeadas. No es inyección SQL directa porque las strings están controladas, pero el patrón `orderByRaw` es sensible.
- **Riesgo:** Bajo en el estado actual, pero si en el futuro se parametriza, es un vector de SQLi.
- **Corrección:** Documentar que `orderByRaw` solo debe usarse con literales hardcodeados. Para ordenamientos dinámicos usar whitelisting:
  ```php
  $columnasPermitidas = ['created_at', 'estado', 'nombre'];
  abort_if(! in_array($sort, $columnasPermitidas), 400);
  ```

### M5. Posible información sensible en logs
- **Archivo:** `storage/logs/laravel.log`
- **Hallazgo:** El archivo existe y Laravel en modo debug puede logear queries, parámetros de request y excepciones completas.
- **Riesgo:** Si los logs son accesibles (por ejemplo, via path traversal o mala configuración), se filtran datos de candidatos y empresas.
- **Corrección:**
  - Asegurar que `storage/` no es accesible desde web.
  - Configurar `LOG_LEVEL=warning` en producción.
  - Agregar `storage/logs/` a `.gitignore` (generalmente ya está, verificar).

### M6. Dependencias PHP no auditadas (composer audit no disponible)
- **Hallazgo:** No fue posible ejecutar `composer audit` por falta de binario. No se detectaron vulnerabilidades conocidas en `composer.json` manualmente, pero no hay certeza.
- **Riesgo:** Dependencias con CVEs no detectados.
- **Corrección:**
  ```bash
  composer audit --no-interaction
  ```
  Integrar en CI/CD. Actualizar Laravel y Livewire periódicamente.

---

## 🟢 Bajo

### B1. `npm audit` limpio pero con dependencias obsoletas
- **Hallazgo:** `npm audit` reporta 0 vulnerabilidades. Sin embargo, se usa Tailwind CSS v3 y AlpineJS v3; mantener monitoreo.
- **Corrección:** Incluir `npm audit` en el pipeline de CI.

### B2. `APP_URL` apunta a ruta de desarrollo local
- **Archivo:** `.env`
- **Hallazgo:** `APP_URL=http://localhost/RHLaravel/SistemaRH_Laravel/public`
- **Riesgo:** URLs generadas en correos, notificaciones y exports apuntan a localhost.
- **Corrección:** Actualizar por dominio de producción.

### B3. `DB_PASSWORD` vacío para usuario root
- **Archivo:** `.env`
- **Hallazgo:** `DB_USERNAME=root`, `DB_PASSWORD=`
- **Riesgo:** Si se migra a MySQL/MariaDB en producción sin cambiar estas credenciales, la base queda sin protección.
- **Corrección:** Usar usuario dedicado y contraseña fuerte. Documentar en runbook de despliegue.

### B4. `Public Storage` symlink sin verificación de acceso
- **Hallazgo:** `php artisan storage:link` crea `public/storage` → `storage/app/public`.
- **Riesgo:** Archivos que deberían ser privados (como CVs) quedan al descubierto.
- **Corrección:** Separar en dos disks: `public` para avatares/logos y `private` (disk `local`) para documentos sensibles.

---

## Matriz de Riesgo

| ID | Hallazgo | Severidad | Esfuerzo de Corrección |
|---|---|---|---|
| C1 | APP_KEY expuesta | Crítico | Bajo |
| C2 | APP_DEBUG=true | Crítico | Bajo |
| C3 | SESSION_ENCRYPT=false | Crítico | Bajo |
| A1 | CheckRole sin estado | Alto | Bajo |
| A2 | Sin rate limiting | Alto | Medio |
| A3 | CSV Injection | Alto | Bajo |
| A4 | Archivos públicos sin control | Alto | Medio |
| A5 | User fillable excesivo | Alto | Medio |
| A6 | SQLite en repo | Alto | Bajo |
| A7 | Seeders con password hardcodeado | Alto | Bajo |
| M1 | Sin headers de seguridad | Medio | Bajo |
| M2 | pagina_web sin validar URL | Medio | Bajo |
| M3 | Chat sin verificación de ruta | Medio | Bajo |
| M4 | orderByRaw patrón sensible | Medio | Bajo |
| M5 | Logs potencialmente sensibles | Medio | Bajo |
| M6 | Dependencias no auditadas | Medio | Bajo |
| B1-B4 | Configuraciones de desarrollo | Bajo | Bajo |

---

## Recomendaciones Prioritarias (Top 5)

1. **Rotar APP_KEY** y establecer `APP_DEBUG=false` + `SESSION_ENCRYPT=true` antes de cualquier despliegue.
2. **Agregar verificación de estado de cuenta** en `CheckRole` para evitar que usuarios bloqueados sigan operando.
3. **Implementar rate limiting** en todas las rutas autenticadas, especialmente admin.
4. **Separar almacenamiento de archivos sensibles** (CVs) a un disk privado y servirlos con controller autorizado.
5. **Sanitizar exportaciones CSV** y validar URLs (`pagina_web`) antes de guardar.

---

## Notas del Análisis

- No se encontraron inyecciones SQL directas: todas las consultas dinámicas usan Eloquent con parámetros preparados.
- No se encontró XSS reflejado en flujos críticos; las vistas usan `e()` o `{{ }}` para escapado.
- No se detectó exposición de `.env` por configuración de servidor (`.htaccess` bloquea `.*` correctamente), pero el riesgo persiste por human error en despliegue.
- Las dependencias Node.js (`npm audit`) están limpias. Las dependencias PHP requieren auditoría con `composer audit`.

---

*Fin del reporte.*
