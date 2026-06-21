<?php

namespace App\Services\Routines\Resolvers;

use App\Helpers\Data\RoutineRecommendationContext;
use App\Models\RoutineAction;

interface RoutineTargetResolver
{
    public function resolve(
        RoutineAction $action,
        RoutineRecommendationContext $context
    );
}
