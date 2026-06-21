<?php

namespace App\Services\Routines\Resolvers;

use App\Helpers\Data\RoutineRecommendationContext;
use App\Models\Product;
use App\Models\RoutineAction;
use Illuminate\Database\Eloquent\Collection;

class ProductResolver implements RoutineTargetResolver
{

    public function resolve(
        RoutineAction $action,
        RoutineRecommendationContext $context
    ): Collection
    {
        return Product::query()
            ->when($context->business, function ($query) use ($context) {
                $query->where('business_id', $context->business->id);
            })
            ->where('id', $action->target_id)
            ->limit($context->limit)
            ->get();
    }
}
