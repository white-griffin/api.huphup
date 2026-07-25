<?php

namespace App\Http\Controllers\User\Api\V1\Review;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewMessageRequest;
use App\Models\Review;
use App\Services\Review\ReviewMessageService;
use Illuminate\Support\Facades\DB;

class ReviewMessageController extends Controller
{
    public function store(
        StoreReviewMessageRequest $request,
        Review $review,
        ReviewMessageService $service,
    ) {

        return DB::transaction(function () use ($request, $review, $service) {
            $message = $service->create(
                review: $review,
                author: $request->user(),
                body: $request->validated('body'),
                parent: $request->validated('parent_id')
                    ? $review->messages()->find($request->validated('parent_id'))
                    : null,
            );

            return ApiResponse::Success('نظر با موفقیت ثبت شد');
        });
    }
}
