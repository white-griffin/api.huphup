<?php

namespace App\Services\Routines\Resolvers;

use App\Helpers\Data\RoutineRecommendationContext;
use App\Models\Product;
use App\Models\RoutineAction;
use Illuminate\Database\Eloquent\Collection;

class CategoryResolver implements RoutineTargetResolver
{

    public function resolve(
        RoutineAction $action,
        RoutineRecommendationContext $context
    ): Collection
    {
        return Product::query()
            ->where('business_id', $context->business?->id)
            ->whereHas('categories', function ($query) use ($action) {
                $query->where('categories.id', $action->target_id);
            })
            ->limit($context->limit)
            ->get();
    }
}
