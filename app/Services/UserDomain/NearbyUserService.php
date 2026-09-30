<?php

namespace App\Services\UserDomain;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class NearbyUserService
{

    public function getNearby(
        float $lat,
        float $lng,
        float $radiusKm = 10,
        ?int $excludeUserId = null,
    ): Collection {
        if ($lat < -90 || $lat > 90) {
            throw new InvalidArgumentException('Invalid latitude.');
        }

        if ($lng < -180 || $lng > 180) {
            throw new InvalidArgumentException('Invalid longitude.');
        }

        if ($radiusKm <= 0) {
            throw new InvalidArgumentException('Radius must be greater than zero.');
        }

        /*
         * Approximate bounding box.
         * 1 degree latitude ~= 111.32 km
         */
        $latDelta = $radiusKm / 111.32;

        $lngDelta = $radiusKm / (
                111.32 * max(cos(deg2rad($lat)), 0.00001)
            );

        $minLat = max(-90, $lat - $latDelta);
        $maxLat = min(90, $lat + $latDelta);

        $minLng = $lng - $lngDelta;
        $maxLng = $lng + $lngDelta;

        $query = User::query()
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.avatar',
                'users.latitude',
                'users.longitude',
            ])
            ->selectRaw(
                'ST_Distance_Sphere(
                    POINT(users.longitude, users.latitude),
                    POINT(?, ?)
                ) / 1000 AS distance',
                [$lng, $lat]
            )
            ->where('users.nearby_enabled', true)
            ->whereNotNull('users.latitude')
            ->whereNotNull('users.longitude')
            ->whereBetween('users.latitude', [$minLat, $maxLat])
            ->when(
                $minLng >= -180 && $maxLng <= 180,
                fn ($query) => $query->whereBetween(
                    'users.longitude',
                    [$minLng, $maxLng]
                ),
                function ($query) use ($minLng, $maxLng) {
                    $query->where(function ($query) use ($minLng, $maxLng) {
                        $query
                            ->where('users.longitude', '>=', $minLng)
                            ->orWhere('users.longitude', '<=', $maxLng);
                    });
                }
            )
            ->when(
                $excludeUserId,
                fn ($query) => $query->where(
                    'users.id',
                    '!=',
                    $excludeUserId
                )
            )
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        return $query
            ->with('pets')
            ->get();
    }

    public function updateLocation(
        User $user,
        float $lat,
        float $lng,
    ): User {
        if ($lat < -90 || $lat > 90) {
            throw new InvalidArgumentException('Invalid latitude.');
        }

        if ($lng < -180 || $lng > 180) {
            throw new InvalidArgumentException('Invalid longitude.');
        }

        $user->update([
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return $user->refresh();
    }

    public function setEnabled(
        User $user,
        bool $enabled,
    ): User {
        $user->update([
            'nearby_enabled' => $enabled,
        ]);

        return $user->refresh();
    }

}
