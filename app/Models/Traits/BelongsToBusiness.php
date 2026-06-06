<?php

namespace App\Models\Traits;

use App\Models\Scopes\BusinessScope;

trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BusinessScope);

        static::creating(function ($model) {
            if (app()->bound('business')) {
                $model->business_id = business()->id;
            }
        });
    }
}
