<?php

namespace App\Services\UserDomain;

use App\Enums\ActivityStatus;
use App\Enums\VerificationStatuses;
use App\Models\Business;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class NearbyBusinessService
{
    public function getNearby(
        float $lat,
        float $lng,
        float $radiusKm = 10,
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

        $query = Business::query()
            ->select([
                'businesses.id',
                'businesses.business_type',
                'businesses.name',
                'businesses.logo',
                'businesses.latitude',
                'businesses.longitude',
                'businesses.activity_status',
                'businesses.verification_status',
            ])
            ->selectRaw(
                'ST_Distance_Sphere(
                    POINT(businesses.longitude, businesses.latitude),
                    POINT(?, ?)
                ) / 1000 AS distance',
                [$lng, $lat]
            )
            ->where('businesses.activity_status',ActivityStatus::ACTIVE->value)
            ->where('businesses.verification_status',VerificationStatuses::ACTIVE->value)
            ->where('businesses.nearby_enabled', true)
            ->whereNotNull('businesses.latitude')
            ->whereNotNull('businesses.longitude')
            ->whereBetween('businesses.latitude', [$minLat, $maxLat])
            ->when(
                $minLng >= -180 && $maxLng <= 180,
                fn ($query) => $query->whereBetween(
                    'businesses.longitude',
                    [$minLng, $maxLng]
                ),
                function ($query) use ($minLng, $maxLng) {
                    $query->where(function ($query) use ($minLng, $maxLng) {
                        $query
                            ->where('businesses.longitude', '>=', $minLng)
                            ->orWhere('businesses.longitude', '<=', $maxLng);
                    });
                }
            )
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        return $query->get();
    }

    public function setEnabled(
        Business $business,
        bool $enabled,
    ): Business {
        $business->update([
            'nearby_enabled' => $enabled,
        ]);

        return $business->refresh();
    }
}
