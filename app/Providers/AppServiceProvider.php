<?php

namespace App\Providers;

use App\Contracts\LaporanServiceInterface;
use App\Contracts\OpkStatsServiceInterface;
use App\Contracts\PetaDataServiceInterface;
use App\Contracts\VerifikasiServiceInterface;
use App\Http\View\Composers\KondisiComposer;
use App\Http\View\Composers\SidebarComposer;
use App\Models\Observers\OpkLaporanObserver;
use App\Models\OpkLaporan;
use App\Services\AiOpkAnalyzer;
use App\Services\LaporanService;
use App\Services\OpkStatsService;
use App\Services\PetaDataService;
use App\Services\VerifikasiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiOpkAnalyzer::class, function () {
            return new AiOpkAnalyzer;
        });

        $this->app->bind(OpkStatsServiceInterface::class, OpkStatsService::class);
        $this->app->bind(PetaDataServiceInterface::class, PetaDataService::class);
        $this->app->bind(LaporanServiceInterface::class, LaporanService::class);
        $this->app->bind(VerifikasiServiceInterface::class, VerifikasiService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Model::shouldBeStrict(! $this->app->isProduction());

        OpkLaporan::observe(OpkLaporanObserver::class);

        View::composer('layouts.app', SidebarComposer::class);
        View::composer('*', KondisiComposer::class);
    }
}
