<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\User;

class BitacoraService
{
    public function registrar(string $modulo, string $accion, ?string $detalle = null, ?User $usuario = null): Bitacora
    {
        $usuario ??= auth()->user();

        return Bitacora::create([
            'usuario_id' => $usuario?->id,
            'modulo' => $modulo,
            'accion' => $accion,
            'detalle' => $detalle,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
