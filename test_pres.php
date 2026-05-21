<?php
$u = App\Models\User::where('rol','candidato')->first();
$u->tocarPresencia();
$u->refresh();
echo 'en linea (ahora): '.($u->estaEnLinea() ? 'si' : 'no').PHP_EOL;
$u->forceFill(['last_seen_at' => now()->subMinutes(8)])->saveQuietly();
$u->refresh();
echo 'en linea (-8min): '.($u->estaEnLinea() ? 'si' : 'no').PHP_EOL;
echo 'ultima vez: '.$u->ultimaVezTexto().PHP_EOL;
