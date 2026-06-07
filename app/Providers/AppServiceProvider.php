<?php

namespace App\Providers;

use App\Services\CandidatoService;
use App\Services\PostulacionService;
use App\Services\SitioService;
use App\Services\SlaInteligenteService;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkflowService::class);
        $this->app->singleton(SlaInteligenteService::class);
        $this->app->bind(CandidatoService::class);
        $this->app->bind(PostulacionService::class);
    }

    public function boot(): void
    {
        // Compartir la identidad del sitio (SEO, favicon, textos del landing)
        // solo con las vistas que la usan. Una sola consulta cuando se renderizan.
        View::composer(
            ['layouts.app', 'layouts.guest', 'layouts.landing', 'welcome', 'paginas.legal', 'partials.layout-imprimible'],
            function ($view) {
                try {
                    $sitio = app(SitioService::class)->valores();
                } catch (\Throwable $e) {
                    report($e);
                    $sitio = [];
                }

                $view->with('sitio', $sitio);
            }
        );
    }
}
