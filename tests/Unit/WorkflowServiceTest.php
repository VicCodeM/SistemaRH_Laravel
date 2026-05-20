<?php

namespace Tests\Unit;

use App\Services\WorkflowService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WorkflowServiceTest extends TestCase
{
    public function test_devuelve_manual_cuando_no_hay_config(): void
    {
        $service = new WorkflowService();

        $resultado = $service->modeFor('empresas');

        $this->assertEquals('manual', $resultado);
    }

    public function test_devuelve_auto_cuando_esta_configurado(): void
    {
        Config::set('workflow.candidatos', 'auto');

        $service = new WorkflowService();
        $resultado = $service->modeFor('candidatos');

        $this->assertEquals('auto', $resultado);
    }
}
