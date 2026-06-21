<?php

namespace App\Helpers\Data;

use App\Models\Business;
use App\Models\Pet;

class RoutineRecommendationContext
{
    public function __construct(
        public Pet $pet,
        public ?Business $business = null,
        public ?int $limit = 10,
    ) {}

    public function speciesId(): ?int
    {
        return $this->pet->species_id;
    }

    public function breedId(): ?int
    {
        return $this->pet->breed_id;
    }
}
