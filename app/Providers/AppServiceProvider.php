<?php

namespace App\Providers;

use App\Contracts\{
    OpkStatsServiceInterface,
    PetaDataServiceInterface,
    LaporanServiceInterface,
    VerifikasiServiceInterface
};
use App\Models\OpkLaporan;
use App\Models\Observers\OpkLaporanObserver;
use App\Services\{
    AiOpkAnalyzer,
    OpkStatsService,
    PetaDataService,
    LaporanService,
    VerifikasiService
};
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

        $this->app->bind(OpkStatsServiceInterface::class, OpkStatsService::class);
        $this->app->bind(PetaDataServiceInterface::class, PetaDataService::class);
        $this->app->bind(LaporanServiceInterface::class, LaporanService::class);
        $this->app->bind(VerifikasiServiceInterface::class, VerifikasiService::class);
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
