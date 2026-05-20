<?php

namespace Tests\Unit;

use App\Models\Candidato;
use Tests\TestCase;

class CandidatoNombreCompletoTest extends TestCase
{
    public function test_construye_nombre_completo_con_todos_los_campos(): void
    {
        $candidato = new Candidato([
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'García',
        ]);

        $this->assertEquals('Juan Pérez García', $candidato->nombreCompleto());
    }

    public function test_construye_nombre_sin_apellido_materno(): void
    {
        $candidato = new Candidato([
            'nombre' => 'María',
            'apellido_paterno' => 'López',
            'apellido_materno' => null,
        ]);

        $this->assertEquals('María López', $candidato->nombreCompleto());
    }

    public function test_construye_nombre_con_campos_vacios(): void
    {
        $candidato = new Candidato([
            'nombre' => 'Pedro',
            'apellido_paterno' => '',
            'apellido_materno' => 'Sánchez',
        ]);

        $this->assertEquals('Pedro Sánchez', $candidato->nombreCompleto());
    }
}
