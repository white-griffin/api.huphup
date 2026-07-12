<?php

use App\Enums\OrderStatuses;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->tinyInteger('order_status')
                ->default(OrderStatuses::PENDING->value)
                ->comment('1=pending,2=paid,3=cancelled,4=failed,5=completed');
            $table->tinyInteger('payment_status')
                ->default(PaymentStatuses::UNPAID->value)
                ->comment('1=unpaid,2=Processing,3=Failed,4=Cancelled,5=Refunded,6=Expired,7=Paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
