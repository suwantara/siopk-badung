<?php

namespace App\Http\View\Composers;

use App\Helpers\CacheKeys;
use App\Models\KondisiOpk;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class KondisiComposer
{
    public function compose(View $view): void
    {
        $kondisiList = Cache::remember(CacheKeys::KONDISI_LIST, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->get();
        });

        $view->with('kondisiList', $kondisiList);
    }
}
