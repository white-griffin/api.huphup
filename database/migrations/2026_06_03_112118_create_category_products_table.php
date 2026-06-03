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
        Schema::create('category_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete(); // ارجاع به دسته‌بندی
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // ارجاع به محصول
            $table->timestamps(); // تاریخ تخصیص

            $table->unique(['category_id', 'product_id']); // هر محصول یکبار در یک دسته ثبت می‌شود
            $table->index('product_id'); // ایندکس برای جستجوی دسته‌های یک محصول
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_products');
    }
};
