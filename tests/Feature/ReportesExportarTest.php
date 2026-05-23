<?php

namespace Tests\Feature;

use App\Models\ConfiguracionSistema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesExportarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'estado' => 'activo']);
    }

    public function test_reporte_del_sistema_descarga_csv_con_bom(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.reportes.exportar'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // BOM UTF-8 al inicio para que Excel muestre acentos
        $contenido = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenido);
        $this->assertStringContainsString('Empresas totales', $contenido);
    }

    public function test_personal_interno_csv_descarga_con_bom(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.personal-interno.exportar.csv'));

        $response->assertOk();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
    }

    public function test_pdf_de_personal_interno_usa_la_marca_de_configuracion(): void
    {
        ConfiguracionSistema::guardar('sitio_nombre', 'Mi Empresa RH', [
            'grupo' => 'sitio',
            'tipo'  => 'string',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.personal-interno.exportar.pdf'));

        $response->assertOk();
        // La plantilla PDF muestra el nombre configurado, no "SistemaRH" fijo
        $response->assertSee('Mi Empresa', false);
    }

    public function test_un_no_admin_no_puede_exportar_reportes(): void
    {
        $candidato = User::factory()->create(['rol' => 'candidato', 'estado' => 'activo']);

        $this->actingAs($candidato)
            ->get(route('admin.reportes.exportar'))
            ->assertForbidden();
    }
}
