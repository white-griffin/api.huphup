<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ActivityStatus;
use App\Enums\VerificationStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\User\BusinessResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Business;
use App\Models\BusinessService;
use App\Services\Review\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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


}
