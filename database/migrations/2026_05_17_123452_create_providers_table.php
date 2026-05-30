<?php

use App\Enums\GenderType;
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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('national_code',10)->unique();
            $table->string('father_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->tinyInteger('gender_type')
                ->default(GenderType::UNKNOWN->value)
                ->comment('1 For Male , 2 For Female , 3 For Unknown');

            // تماس و احراز هویت ورود
            $table->string('mobile', 15)->unique();        // شماره موبایل (ورود اصلی)
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // آدرس
            $table->foreignId('province_id')->constrained('provinces');         // استان
            $table->foreignId('city_id')->constrained('cities');             // شهر
            $table->string('postal_code', 10)->nullable();  // کد پستی
            $table->text('address')->nullable();

            // KYC و احراز هویت
            $table->boolean('shahkar_verified')->default(false); // تطبیق موبایل با کد ملی
            $table->tinyInteger('verification_status')
                ->default(VerificationStatuses::PENDING->value)
                ->comment('0 For Pending,1 For UnderReview , 2 For Active , 3 For Suspended , 4 For Rejected');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
