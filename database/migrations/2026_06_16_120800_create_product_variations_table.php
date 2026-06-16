<?php

use App\Enums\ActivityStatus;
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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->decimal('discount_price', 12, 0)->nullable(); // قیمت تخفیف‌خورده
            $table->unsignedInteger('stock')->default(0);
            $table->string('sku')->nullable()->unique();
            $table->json('attributes');    // {"رنگ":"قرمز","سایز":"XL"}
            $table->boolean('is_default')->default(true);
            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 2 For InActive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
