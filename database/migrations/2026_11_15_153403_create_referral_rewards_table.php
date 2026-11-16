<?php

use App\Enums\Referral\ReferralRewardStatus;
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
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('referral_id')
                ->constrained('referrals')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('type');

            $table->unsignedBigInteger('amount');

            $table->tinyInteger('status')
                ->default(ReferralRewardStatus::PENDING->value);

            $table->string('idempotency_key')->unique();

            $table->timestamp('awarded_at')->nullable();

            $table->timestamps();

            $table->unique(['referral_id', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
