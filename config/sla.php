<?php

return [
    /*
    | Configuración del SLA Inteligente
    | Tiempos base en minutos para cada prioridad
    */
    'base_minutes' => [
        'alta' => env('SLA_ALTA_MINUTOS', 45),
        'media' => env('SLA_MEDIA_MINUTOS', 180),
        'baja' => env('SLA_BAJA_MINUTOS', 480),
    ],
];
