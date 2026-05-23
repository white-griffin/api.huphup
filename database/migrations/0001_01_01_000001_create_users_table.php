<?php

use App\Enums\ActivityStatus;
use App\Enums\GenderType;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');

            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('mobile'); // E.164

            $table->string('otp_code')->nullable();
            $table->string('password')->nullable();

            $table->string('avatar')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('national_code')->nullable();
            $table->text('bio')->nullable();

            $table->tinyInteger('gender_type')
                ->default(GenderType::MALE->value)
                ->comment('1 For Male , 2 For FeMale');

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 2 For InActive');

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
