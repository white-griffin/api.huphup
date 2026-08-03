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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_vendor_id')
                ->constrained('order_vendors')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variation_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);      // snapshot از variation.price لحظه خرید
            $table->decimal('discount_price', 15, 2)->nullable(); // snapshot از variation.discount_price
            $table->decimal('total_price', 15, 2);    // quantity * (discount_price ?? unit_price)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
