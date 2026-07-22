<?php

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
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();

            // کوپن استفاده‌شده
            $table->foreignId('coupon_id')
                ->constrained()
                ->cascadeOnDelete();

            // کاربری که از کوپن استفاده کرده
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // موجودیتی که تخفیف روی آن اعمال شده (Order / Appointment / ...)
            $table->morphs('discountable');

            // مبلغ تخفیف اعمال‌شده
            $table->unsignedBigInteger('discount_amount');

            // زمان استفاده از کوپن
            $table->timestamp('used_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
