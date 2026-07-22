<?php

use App\Enums\ActivityStatus;
use App\Enums\CouponTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->tinyInteger('type')
                ->default(CouponTypes::PERCENTAGE->value);

            $table->unsignedBigInteger('value');

            // سقف تخفیف برای کدهای درصدی
            $table->unsignedBigInteger('max_discount')->nullable();

            // حداقل مبلغ پرداخت برای استفاده از کوپن
            $table->unsignedBigInteger('min_amount')->default(0);

            // زمان شروع اعتبار کوپن
            $table->timestamp('starts_at')->nullable();

            // زمان پایان اعتبار کوپن
            $table->timestamp('ends_at')->nullable();

            // حداکثر تعداد استفاده کل
            $table->unsignedInteger('usage_limit')->nullable();

            // حداکثر تعداد استفاده هر کاربر
            $table->unsignedInteger('usage_limit_per_user')->nullable();

            // تعداد استفاده انجام‌شده
            $table->unsignedInteger('used_count')->default(0);

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
