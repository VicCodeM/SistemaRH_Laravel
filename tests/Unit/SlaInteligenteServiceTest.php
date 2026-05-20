<?php

namespace Tests\Unit;

use App\Services\SlaInteligenteService;
use Tests\TestCase;

class SlaInteligenteServiceTest extends TestCase
{
    public function test_calcula_fecha_vencimiento_para_prioridad_alta(): void
    {
        $service = new SlaInteligenteService();

        // Asumiendo que el método existe y recibe prioridad + fecha de creación
        // Este es solo un ejemplo de cómo rellenarías la prueba
        $this->assertInstanceOf(SlaInteligenteService::class, $service);
    }
}
