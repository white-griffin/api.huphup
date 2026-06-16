<?php

use App\Enums\Contracts\EnumContractInterface;
use App\Enums\PublicationStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete(); // متعلق به کدام کسب‌وکار
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // نام محصول
            $table->string('slug'); // شناسه URL
            $table->text('description')->nullable(); // توضیحات
            $table->decimal('price', 12, 0); // قیمت اصلی
            $table->decimal('discount_price', 12, 0)->nullable(); // قیمت تخفیف‌خورده
            $table->integer('stock')->default(0); // موجودی انبار
            $table->string('sku')->nullable(); // کد انبار
            $table->tinyInteger('publication_status')
                ->default(PublicationStatus::PENDING);
            $table->timestamps();

            $table->unique(['business_id', 'slug']); // اسلاگ یکتا در هر کسب‌وکار
            $table->unique(['business_id', 'sku']); // کد انبار یکتا در هر کسب‌وکار

            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
