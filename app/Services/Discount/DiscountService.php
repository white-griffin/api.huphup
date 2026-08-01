<?php

namespace App\Services\Discount;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscountService
{

    public function validate(string $code, User $user, int $amount): DiscountResult
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف نامعتبر است.'
            );
        }

        // شروع اعتبار
        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف هنوز فعال نشده است.'
            );
        }

        // پایان اعتبار
        if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
            return new DiscountResult(
                valid: false,
                message: 'کد تخفیف منقضی شده است.'
            );
        }

        // حداقل مبلغ
        if ($amount < $coupon->min_amount) {
            return new DiscountResult(
                valid: false,
                message: 'حداقل مبلغ برای استفاده از این کد رعایت نشده است.'
            );
        }

        // محدودیت کل
        if ($coupon->usage_limit &&
            $coupon->used_count >= $coupon->usage_limit) {

            return new DiscountResult(
                valid: false,
                message: 'ظرفیت استفاده از این کد تکمیل شده است.'
            );
        }

        // محدودیت هر کاربر
        $userUsageCount = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        if ($coupon->usage_limit_per_user &&
            $userUsageCount >= $coupon->usage_limit_per_user) {

            return new DiscountResult(
                valid: false,
                message: 'شما قبلاً از این کد استفاده کرده‌اید.'
            );
        }

        // محاسبه تخفیف
        if ($coupon->type === 'percentage') {

            $discount = (int) floor($amount * $coupon->value / 100);

            if ($coupon->max_discount) {
                $discount = min($discount, $coupon->max_discount);
            }

        } else {

            $discount = min($coupon->value, $amount);
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
                ->where('discountable_type', $payment->payable_type)
                ->where('discountable_id', $payment->payable_id)
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
