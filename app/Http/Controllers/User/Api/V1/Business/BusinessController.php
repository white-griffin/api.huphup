<?php

namespace App\Http\Controllers\User\Api\V1\Business;

use App\Enums\ActivityStatus;
use App\Enums\ReactionType;
use App\Enums\VerificationStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Business\BusinessNearbyResource;
use App\Http\Resources\V1\User\Business\BusinessResource;
use App\Models\Business;
use App\Services\UserDomain\NearbyBusinessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $userId = $user?->id;
            $businesses = BusinessResource::collection(
                Business::query()
                    ->where(
                        'verification_status',
                        VerificationStatuses::ACTIVE->value
                    )
                    ->where(
                        'activity_status',
                        ActivityStatus::ACTIVE->value
                    )
                    ->withCount([
                        'reactions as likes_count' => fn ($q) =>
                        $q->where('type', ReactionType::LIKE->value),
                    ])
                    ->when($userId, function ($q) use ($userId) {
                        $q->with([
                            'reactions' => fn ($q) => $q
                                ->where('user_id', $userId)
                                ->select(
                                    'id',
                                    'user_id',
                                    'reactable_id',
                                    'reactable_type',
                                    'type'
                                ),
                        ]);
                    })
                    ->when($request->type, function ($q) use ($request) {
                        $q->where('business_type', $request->type);
                    })
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $search = $request->search;

                        $q->where(function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('businessServices.service', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->with([
                        'reputation',
                        'businessServices.service',
                        'businessServices.reviewSummary',
                        'businessServices.reviews' => fn ($query) => $query
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
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getMessage()
            );
        }
    }

    public function show(Business $business)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $userId = $user?->id;
            $business->loadCount([
                'reactions as likes_count' => fn ($q) =>
                $q->where('type', ReactionType::LIKE->value),
            ]);

            if ($userId) {
                $business->load([
                    'reactions' => fn ($q) => $q
                        ->where('user_id', $userId)
                        ->select('id', 'user_id', 'reactable_id', 'reactable_type', 'type'),
                ]);
            }

            $business->load([
                'province',
                'city',
                'reputation',

                'businessServices.service',
                'businessServices.reviewSummary',

                'businessServices.reviews' => fn ($query) => $query
                    ->approved()
                    ->latest()
                    ->limit(5)
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
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطای داخلی سرور'
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
