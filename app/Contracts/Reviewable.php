<?php

namespace App\Contracts;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Reviewable
{
    /**
     * کسب‌وکار مالک این موجودیت
     */
    public function getBusiness(): Business;

    /**
     * آیا این موجودیت قابلیت امتیازدهی دارد؟
     */
    public function canBeRated(): bool;

    /**
     * آیا این کاربر اجازه ثبت Review دارد؟
     */
    public function canUserReview(User $user): bool;

    /**
     * آیا خرید/رزرو این کاربر تأیید شده است؟
     */
    public function isVerifiedPurchase(User $user): bool;
}
