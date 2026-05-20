<?php
auth()->login(App\Models\User::where('rol','admin')->first());
foreach (['candidatos','empres','reporte','config'] as $q) {
    $r = app(App\Services\BusquedaService::class)->global($q);
    $secciones = $r->where('tipo','Sección')->pluck('titulo')->implode(', ');
    echo "[$q] secciones: " . ($secciones ?: '(ninguna)') . " | total=" . $r->count() . PHP_EOL;
}
