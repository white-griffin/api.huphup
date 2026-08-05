<?php

use App\Enums\PaymentStatuses;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable'); // payable_type, payable_id
            $table->foreignId('user_id')->constrained();

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('original_amount',15,2); // مبلغ قبل از کوپن
            $table->decimal('coupon_discount_amount',15,2)->default(0);// مبلغ تخفیف کوپن
            $table->decimal('amount',15,2); // مبلغ نهایی پرداخت
            $table->tinyInteger('gateway'); // نام درگاه
            $table->string('transaction_id')->nullable()->unique(); // reference بازگشتی از درگاه
            $table->tinyInteger('payment_status')
                ->default(PaymentStatuses::UNPAID->value)
                ->comment('1=unpaid,2=Processing,3=Failed,4=Cancelled,5=Refunded,6=Expired,7=Paid');
            $table->json('gateway_response')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
