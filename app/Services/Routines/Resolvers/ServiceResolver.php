<?php

namespace App\Services\Routines\Resolvers;

use App\Helpers\Data\RoutineRecommendationContext;
use App\Models\BusinessService;
use App\Models\RoutineAction;
use Illuminate\Database\Eloquent\Collection;

class ServiceResolver implements RoutineTargetResolver
{

    public function resolve(
        RoutineAction $action,
        RoutineRecommendationContext $context
    ): Collection
    {
        return BusinessService::query()
            ->with(['service', 'business'])
            ->where('service_id', $action->target_id)
            ->when($context->business, function ($query) use ($context) {
                $query->where('business_id', $context->business->id);
            })
            ->limit($context->limit)
            ->get();
    }
}
