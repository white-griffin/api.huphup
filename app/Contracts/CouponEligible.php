<?php

namespace App\Contracts;

interface CouponEligible
{
    public function canUseCoupon(): bool;

    public function couponRestrictionMessage(): string;
}
