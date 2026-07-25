<?php

namespace App\Services\Review;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewApprovalService
{
    public function approve(
        Review|ReviewMessage $reviewable,
        User $admin,
    ): Review|ReviewMessage {
        return DB::transaction(function () use ($reviewable, $admin) {

            $reviewable->update([
                'status' => ReviewStatus::APPROVED->value,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            return $reviewable->fresh();
        });
    }

    public function reject(
        Review|ReviewMessage $reviewable,
        User $admin,
    ): Review|ReviewMessage {
        return DB::transaction(function () use ($reviewable, $admin) {

            $reviewable->update([
                'status' => ReviewStatus::REJECTED->value,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            return $reviewable->fresh();
        });
    }
}
