<?php

namespace App\Http\Controllers\User\Api\V1\Business;

use App\Enums\ActivityStatus;
use App\Enums\VerificationStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Business\BusinessNearbyResource;
use App\Http\Resources\V1\User\Business\BusinessResource;
use App\Models\Business;
use App\Services\UserDomain\NearbyBusinessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        try {
            $businesses = BusinessResource::collection(
                Business::query()
                    ->where('verification_status', VerificationStatuses::ACTIVE->value)
                    ->where('activity_status', ActivityStatus::ACTIVE->value)
                    ->when($request->type, function ($q) use ($request) {
                        $q->where('business_type', $request->type);
                    })
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $search = $request->search;

                        $q->where(function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('services.service', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->with([
                        'reputation',
                        'services.service',
                        'services.reviewSummary',
                        'services.reviews' => fn ($query) => $query
                            ->approved()
                            ->latest()
                            ->take(5)
                            ->with([
                                'user',
                                'messages' => fn ($query) => $query
                                    ->approved()
                                    ->root()
                                    ->with([
                                        'author',
                                        'business',
                                        'replies.author',
                                        'replies.business',
                                    ]),
                            ]),
                    ])
                    ->cursorPaginate(10)
            );
            return ApiResponse::success('عملیات موفق', $businesses);
        } catch (\Exception $exception) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function show(Business $business)
    {
        try {
            $business->load([
                'province',
                'city',
                'reputation',
                'services.service',
                'services.reviewSummary',
                'services.reviews' => fn ($query) => $query
                    ->approved()
                    ->latest()
                    ->take(5)
                    ->with([
                        'user',
                        'messages' => fn ($query) => $query
                            ->approved()
                            ->root()
                            ->with([
                                'author',
                                'business',
                                'replies.author',
                                'replies.business',
                            ]),
                    ]),
            ]);

            return ApiResponse::success(
                'عملیات موفق',
                BusinessResource::make($business)
            );

        } catch (\Exception $exception) {
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getMessage()
            );
        }
    }

    public function getNearbyBusinesses(Request $request)
    {
        try {
            $validated = $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
                'radius' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            ]);

            $businesses = app(NearbyBusinessService::class)->getNearby(
                lat: (float) $validated['lat'],
                lng: (float) $validated['lng'],
                radiusKm: (float) ($validated['radius'] ?? 10)
            );

            return ApiResponse::Success('عملیات موفق',BusinessNearbyResource::collection($businesses));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }


}
