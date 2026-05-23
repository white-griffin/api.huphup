<?php

use App\Enums\MemberActivityStatuses;
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
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->constrained(); // نوع کسب‌وکار

            //Main Business Data
            $table->string('business_name');
            $table->string('License_code');
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

            // تنظیمات اختصاصی هر نوع کسب‌وکار - JSON
            // مثال vendor: {"commission_rate": 15, "min_order": 50000}
            // مثال vet: {"consultation_fee": 200000, "emergency_available": true}
            $table->json('settings')->nullable();

            $table->tinyInteger('activity_status')
                ->default(MemberActivityStatuses::PENDING->value)
                ->comment('0 For Pending,1 For Active , 2 For Suspended , 3 For Rejected');
            $table->timestamp('verified_at')->nullable(); // زمان تایید
            $table->text('rejection_reason')->nullable(); // دلیل رد درخواست

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
        Schema::dropIfExists('business_profiles');
    }
};
