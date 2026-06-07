<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_404_page_uses_guest_layout_without_sidebar(): void
    {
        $response = $this->get('/ruta-que-no-existe-para-pruebas');

        $response
            ->assertNotFound()
            ->assertSee('No encontramos esa página')
            ->assertDontSee('class="sidebar"');
    }
}
