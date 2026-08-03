<?php

namespace App\Services\Discount;

use App\Enums\ActivityStatus;
use App\Enums\CouponTypes;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    public function validate(
        string $code,
        User $user,
        int $amount
    ): DiscountResult {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->where('activity_status', ActivityStatus::ACTIVE->value)
            ->first();

        if (! $coupon) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف نامعتبر است.'
            );
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف هنوز فعال نشده است.'
            );
        }

        if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف منقضی شده است.'
            );
        }

        if ($amount < $coupon->min_amount) {
            return new DiscountResult(
                valid: false,
                message: 'حداقل مبلغ برای استفاده از این کد رعایت نشده است.'
            );
        }

        if (
            $coupon->usage_limit !== null &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return new DiscountResult(
                valid: false,
                message: 'ظرفیت استفاده از این کد تکمیل شده است.'
            );
        }

        $userUsageCount = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        if (
            $coupon->usage_limit_per_user !== null &&
            $userUsageCount >= $coupon->usage_limit_per_user
        ) {
            return new DiscountResult(
                valid: false,
                message: 'شما قبلاً از این کد استفاده کرده‌اید.'
            );
        }

        if ($coupon->type === CouponTypes::PERCENTAGE->value) {
            $discount = (int) floor(
                $amount * $coupon->value / 100
            );

            if ($coupon->max_discount !== null) {
                $discount = min(
                    $discount,
                    $coupon->max_discount
                );
            }
        } else {
            $discount = min(
                $coupon->value,
                $amount
            );
        }

        return new DiscountResult(
            valid: true,
            discountAmount: $discount,
            coupon: $coupon,
        );
    }

    public function releaseUsage(Payment $payment): void
    {
        if (! $payment->coupon_id) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $usage = CouponUsage::query()
                ->where('coupon_id', $payment->coupon_id)
                ->where(
                    'discountable_type',
                    $payment->payable_type
                )
                ->where(
                    'discountable_id',
                    $payment->payable_id
                )
                ->lockForUpdate()
                ->first();

            if (! $usage) {
                return;
            }

            $coupon = Coupon::query()
                ->lockForUpdate()
                ->find($usage->coupon_id);

            if ($coupon?->used_count > 0) {
                $coupon->decrement('used_count');
            }

            $usage->delete();
        });
    }
}
