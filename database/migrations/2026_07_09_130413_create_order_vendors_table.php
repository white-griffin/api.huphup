<?php

use App\Enums\OrderStatuses;
use App\Enums\OrderVendorStatuses;
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
        Schema::create('order_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('business_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            // مبلغ واقعی که از Payment به این Vendor تخصیص داده شده
            $table->decimal('paid_amount', 15, 2)
                ->default(0);

            $table->tinyInteger('status')
                ->default(OrderVendorStatuses::PENDING->value);

            $table->timestamps();

            $table->unique([
                'order_id',
                'business_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_vendors');
    }
};
