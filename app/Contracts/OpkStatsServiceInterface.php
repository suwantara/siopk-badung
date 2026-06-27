<?php

namespace App\Contracts;

interface OpkStatsServiceInterface
{
    public function dashboardAdmin(): array;
    public function dashboardPublik(): array;
    public function laporanAdmin(): array;
    public function ringkasanEksekutif(): array;
    public function kategoriWithOpkCount(): \Illuminate\Database\Eloquent\Collection;
    public function kecamatanWithOpkCount(): \Illuminate\Database\Eloquent\Collection;
}
