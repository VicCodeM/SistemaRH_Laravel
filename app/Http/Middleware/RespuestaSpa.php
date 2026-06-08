<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Para los formularios enviados por el interceptor SPA del front (cabecera X-RH-SPA),
 * convierte el redirect del servidor en una respuesta JSON { redirect: url } y conserva
 * el flash (success/error) y los errores de validación para la siguiente navegación.
 *
 * Así el front puede SEGUIR el redirect real (ej. ir a la lista tras guardar) en vez de
 * recargar la misma página, sin perder el toast ni los mensajes de error.
 */
class RespuestaSpa
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->headers->get('X-RH-SPA') === '1' && $response instanceof RedirectResponse) {
            // Conserva flash (success/error), errores de validación e input para la próxima request.
            if ($request->hasSession()) {
                $request->session()->reflash();
            }

            return response()->json(['redirect' => $response->getTargetUrl()]);
        }

        return $response;
    }
}
