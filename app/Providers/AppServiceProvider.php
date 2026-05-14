<?php

namespace App\Providers;

use App\Services\CandidatoService;
use App\Services\PostulacionService;
use App\Services\SlaInteligenteService;
use App\Services\WorkflowService;
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
        //
    }
}
