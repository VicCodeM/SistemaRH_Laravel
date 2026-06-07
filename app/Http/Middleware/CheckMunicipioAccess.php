<?php

namespace App\Http\Middleware;

use App\Services\AccesoMunicipioService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMunicipioAccess
{
    public function __construct(private readonly AccesoMunicipioService $accesoMunicipio)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return $next($request);
        }

        if ($this->accesoMunicipio->puedeAcceder($usuario)) {
            return $next($request);
        }

        abort(403, $this->accesoMunicipio->mensajeDenegado($usuario));
    }
}
