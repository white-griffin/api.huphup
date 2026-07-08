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
            $table->tinyInteger('publication_status')
                ->default(PublicationStatus::PENDING);
            $table->text('reject_reason')->nullable();
            $table->timestamps();


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
