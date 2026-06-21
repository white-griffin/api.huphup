<?php

namespace App\Services\Routines;

use App\Helpers\Data\RoutineRecommendationContext;
use App\Models\PetRoutine;
use App\Services\Routines\Resolvers\ResolverFactory;
use Illuminate\Support\Collection;


class RoutineRecommendationService
{
    public function getRecommendations(
        PetRoutine $routine,
        ?int $businessId = null
    ): array {

        $routine->loadMissing([
            'pet',
            'template.actions',
        ]);

        $business = $businessId
            ? \App\Models\Business::query()->find($businessId)
            : null;

        $context = new RoutineRecommendationContext(
            pet: $routine->pet,
            business: $business,
            limit: 10,
        );



        $results = [];

        foreach ($routine->template->actions->sortBy('priority') as $action) {
            $resolver = ResolverFactory::make($action->target_type);

            if (!$resolver) {
                continue;
            }

            $data = $resolver->resolve($action, $context);

            if ($this->isEmptyResult($data)) {
                continue;
            }

            $results[] = [
                'action_id' => $action->id,
                'type' => $action->target_type,
                'priority' => $action->priority,
                'data' => $data,
            ];
        }

        return $results;
    }

    private function isEmptyResult(mixed $data): bool
    {
        if ($data === null) {
            return true;
        }

        if ($data instanceof Collection) {
            return $data->isEmpty();
        }

        return false;
    }
}
