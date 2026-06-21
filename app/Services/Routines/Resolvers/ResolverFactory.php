<?php

namespace App\Services\Routines\Resolvers;

class ResolverFactory
{
    public static function make(string $type): ?RoutineTargetResolver
    {
        return match ($type) {

            'service' => new ServiceResolver(),

            'product' => new ProductResolver(),

            'category' => new CategoryResolver(),

            default => null,
        };
    }
}
