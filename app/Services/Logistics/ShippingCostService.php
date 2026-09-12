<?php

namespace App\Services\Logistics;

use App\Models\Business;
use App\Models\Order;

class ShippingCostService
{

    public function calculate(
        Business $business,
        array $items,
        Order $order,
    ): int {
        // فعلاً منطق محاسبه هزینه ارسال
        // بعداً می‌توانیم بر اساس provider، مسافت، وزن و ... پیاده کنیم.
        return 0;
    }

}
