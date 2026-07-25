<?php

namespace App\Http\Controllers\User\Api\V1\Review;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\UpdateReviewRequest;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function update(
        UpdateReviewRequest $request,
        Review $review,
    ) {

        return DB::transaction(function () use ($request, $review) {
            $this->authorize('update', $review);

            $review->update(
                $request->validated()
            );

            return ApiResponse::Success('عملیات موفق');
        });
    }

    public function destroy(
        Review $review,
    ) {

        return DB::transaction(function () use ($review) {
            $this->authorize('delete', $review);

            $review->delete();

            return ApiResponse::success(
                'عملیات موفق'
            );
        });
    }
}
