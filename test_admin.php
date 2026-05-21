<?php
$admins = App\Models\User::where('rol','admin')->get(['id','name','email','estado']);
foreach ($admins as $a) echo "#{$a->id} {$a->name} | estado=".var_export($a->estado,true).PHP_EOL;
$activo = App\Models\User::where('rol','admin')->where('estado','activo')->first();
echo 'Admin activo encontrado: '.($activo ? 'SI' : 'NO').PHP_EOL;
