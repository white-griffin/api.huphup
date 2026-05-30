<?php

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
use App\Enums\VerificationStatuses;
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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('business_type')
                ->default(BusinessTypes::SHOPPING); // نوع کسب‌وکار

            //Main Business Data
            $table->string('name');
            $table->string('license_code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            //Call Data
            $table->string('phone', 11);
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            //Address
            $table->foreignId('city_id')->constrained('cities');
            $table->text('address');
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // اطلاعات بانکی (در سطح کسب‌وکار)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_holder')->nullable(); // صاحب حساب
            $table->string('bank_card', 16)->nullable();        // شماره کارت
            $table->string('bank_iban', 26)->nullable();        // شبا (بدون IR)

            // تنظیمات اختصاصی هر نوع کسب‌وکار - JSON
            // مثال vendor: {"commission_rate": 15, "min_order": 50000}
            // مثال vet: {"consultation_fee": 200000, "emergency_available": true}
            $table->json('settings')->nullable();

            $table->tinyInteger('verification_status')
                ->default(VerificationStatuses::PENDING->value)
                ->comment('0 Pending, 1 Verified, 2 Rejected');
            $table->timestamp('verified_at')->nullable(); // زمان تایید
            $table->text('rejection_reason')->nullable(); // دلیل رد درخواست

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 2 For InActive');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']); // برای جستجوی جغرافیایی
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
