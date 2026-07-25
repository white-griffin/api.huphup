<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewMessageRequest;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\BusinessService;
use App\Models\Review;
use App\Services\Review\ReviewMessageService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $business = $request->user()->business;

        $reviews = Review::query()
            ->whereHasMorph(
                'reviewable',
                [BusinessService::class],
                fn ($query) => $query->where('business_id', $business->id)
            )
            ->with([
                'user',
                'messages.author',
                'messages.replies.author',
            ])
            ->latest()
            ->paginate();

        return ApiResponse::success('عملیات موفق', ReviewResource::collection($reviews));
    }

    public function reply(
        StoreReviewMessageRequest $request,
        Review $review,
        ReviewMessageService $reviewMessageService,
    ) {
       return DB::transaction(function () use ($request, $review, $reviewMessageService) {
           $this->authorize('reply', $review);

           $business = $request->user()->business;

           $message = $reviewMessageService->create(
               review: $review,
               author: $business,
               body: $request->validated('body'),
               parent: $request->filled('parent_id')
                   ? $review->messages()->findOrFail($request->validated('parent_id'))
                   : null,
           );

           return ApiResponse::Success('عملیات موفق');
       });
    }
}
