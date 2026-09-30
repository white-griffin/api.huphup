<?php

namespace App\Http\Controllers\User\Api\V1\User;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\User\UserNearbyResource;
use App\Services\NearbyUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserNearbyController extends Controller
{
    public function __construct(
        private readonly NearbyUserService $nearbyUserService,
    ) {
    }


    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
                'radius' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            ]);

            $users = $this->nearbyUserService->getNearby(
                lat: (float) $validated['lat'],
                lng: (float) $validated['lng'],
                radiusKm: (float) ($validated['radius'] ?? 10),
                excludeUserId: $request->user()->id,
            );

            return ApiResponse::Success('عملیات موفق',UserNearbyResource::collection($users));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }

    public function toggle(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enabled' => ['required', 'boolean'],
            ]);

            $user = $this->nearbyUserService->setEnabled(
                user: $request->user(),
                enabled: (bool) $validated['enabled'],
            );

            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }
}
