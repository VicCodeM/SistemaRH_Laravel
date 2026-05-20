# Patrones y problemas conocidos de Livewire/Alpine

## 1. Alpine.js: NO importar manualmente

**Síntoma**: `Detected multiple instances of Alpine running` + `$wire is not defined`

**Causa**: Livewire 3 ya incluye Alpine.js. Importarlo en `app.js` crea dos instancias.

**Solución**:
```js
// resources/js/app.js
import './bootstrap';
```

Luego reconstruir assets: `npm run build`

## 2. wire:model.blur causa sincronización incompleta

**Síntoma**: El usuario llena campos de texto, selecciona un radio button (`wire:model.live`),
el auto-guardado ejecuta `getDatos()` pero los textos aún no llegaron al servidor,
por lo que se guardan como `null` en BD. Al re-renderizar, la sección sigue incompleta.

**Causa**: `wire:model.blur` solo sincroniza al perder foco. Si el usuario hace clic en un radio
antes de que el input de texto pierda foco, Livewire envía solo la actualización del radio.

**Solución**: Usar `wire:model` (sin modificador) para textos, numbers, dates y textareas.
Esto sincroniza con debounce automático (≈150ms).

## 3. Data truncated en columnas ENUM/DATE

**Síntoma**: `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'sexo'`

**Causa**: Se envía string vacío `''` a una columna `ENUM('M','F','Otro')` o `DATE`.
MySQL no acepta `''` en ENUMs.

**Solución en Service**: Normalizar vacíos a `null` antes de `fill()`/`create()`:
```php
private function normalizarVacios(array $datos): array
{
    return array_map(function ($valor) {
        if (is_string($valor) && $valor === '') {
            return null;
        }
        return $valor;
    }, $datos);
}
```

## 4. Radio pills CSS

Los radio buttons se ocultan con:
```css
.radio-pill input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
```

**Importante**: El `<label>` debe envolver al `<input>` para que los clics funcionen correctamente.
**Importante**: Agregar `name` al input para agrupación HTML nativa.

```blade
<label class="radio-pill">
    <input type="radio" wire:model.live="sexo" name="sexo" value="M">
    <span>Masculino</span>
</label>
```

## 5. Alpine x-data + Livewire re-renders

El stepper usa Alpine.js para navegar entre pestañas:
```html
<div x-data="{ tab: 'personales', ... }" data-secciones='@json($secciones)'>
```

`data-secciones` se lee una sola vez al inicializar Alpine. Si se necesita que Alpine
reaccione a cambios del servidor, usar `$wire` o eventos Livewire en lugar de leer atributos HTML.

## 6. Auto-guardado en updated()

Patrón usado en `CandidatoSolicitud`:
```php
public function updated(string $property): void
{
    // Lógica condicional de limpieza
    if ($property === 'licencia_conducir.tiene' && ...) {
        $this->licencia_conducir['clase'] = '';
    }

    $this->autoGuardar();
}
```

Cada cambio de cualquier propiedad dispara una petición al servidor y un guardado en BD.
Esto es intencional para el modo borrador.

## 7. Validación progresiva

Cada sección tiene un método `seccionXCompleta()` que usa `blank()`:
```php
private function campoLleno(mixed $valor): bool
{
    return ! blank($valor);
}
```

`blank(0)` retorna `false`, `blank('0')` retorna `false`, `blank('')` retorna `true`.

## 8. wire:click en botones dentro de x-data

Si un botón tiene `wire:click` y está dentro de un `x-data`, asegurarse de que el evento
no sea interceptado por Alpine. En general funciona bien, pero evitar `x-on:click` y `wire:click`
en el mismo elemento para acciones diferentes.
