<?php

namespace App\Services\Discount;

use App\Models\Coupon;

class DiscountResult
{
    public function __construct(
        public bool $valid,

        // مبلغ تخفیف محاسبه‌شده
        public int $discountAmount = 0,

        // پیام خطا یا موفقیت
        public string $message = '',

        // کوپن معتبر پیدا شده
        public ?Coupon $coupon = null,
    ) {}

}
