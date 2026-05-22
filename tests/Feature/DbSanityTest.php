<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DbSanityTest extends TestCase
{
    public function test_los_tests_usan_la_base_de_datos_de_prueba(): void
    {
        $nombre = DB::connection()->getDatabaseName();

        $this->assertStringEndsWith('_test', $nombre, "Los tests apuntan a '{$nombre}', NO a una BD _test.");
    }
}
