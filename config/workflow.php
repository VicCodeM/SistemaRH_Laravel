<?php

return [
    /*
    | Modos de Workflow
    | 'auto'   = aprueba automáticamente si cumple requisitos
    | 'manual' = requiere revisión del admin
    */
    'empresas' => env('WORKFLOW_EMPRESAS', 'manual'),
    'candidatos' => env('WORKFLOW_CANDIDATOS', 'auto'),
    'vacantes' => env('WORKFLOW_VACANTES', 'manual'),
];
