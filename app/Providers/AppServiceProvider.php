<?php

namespace App\Providers;

use App\Models\OpkLaporan;
use App\Models\Observers\OpkLaporanObserver;
use App\Services\AiOpkAnalyzer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiOpkAnalyzer::class, function () {
            return new AiOpkAnalyzer();
        });
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        Model::shouldBeStrict(! $this->app->isProduction());

        OpkLaporan::observe(OpkLaporanObserver::class);

        View::composer('layouts.app', \App\Http\View\Composers\SidebarComposer::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\LaporanCreated::class,
            [\App\Listeners\SideEffectHandler::class, 'handleLaporanCreated']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\LaporanVerified::class,
            [\App\Listeners\SideEffectHandler::class, 'handleLaporanVerified']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AiAnalysisCompleted::class,
            [\App\Listeners\SideEffectHandler::class, 'handleAiAnalysisCompleted']
        );
    }
}
