<?php

namespace App\Services\Banner;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;

class BannerService
{

    public function getForPlacement(
        BannerPlacement $placement,
    ): Collection {
        return Banner::query()
            ->available()
            ->forPlacement($placement)
            ->ordered()
            ->get();
    }

}
