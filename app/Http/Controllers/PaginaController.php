<?php

namespace App\Http\Controllers;

use App\Services\SitioService;

/**
 * Páginas públicas estáticas (contenido editable desde Configuración).
 */
class PaginaController extends Controller
{
    public function privacidad(SitioService $sitio)
    {
        return view('paginas.legal', [
            'titulo'    => 'Políticas de privacidad',
            'contenido' => $sitio->valores()['privacidad_contenido'] ?? '',
        ]);
    }

    public function terminos(SitioService $sitio)
    {
        return view('paginas.legal', [
            'titulo'    => 'Términos del servicio',
            'contenido' => $sitio->valores()['terminos_contenido'] ?? '',
        ]);
    }
}
