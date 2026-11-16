<?php

use App\Enums\Referral\ReferralQualificationStatus;
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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('referral_campaigns')
                ->restrictOnDelete();

            // Referrer: User or Business
            $table->morphs('referrer');

            $table->foreignId('referred_user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            // Snapshot of campaign rules
            $table->unsignedTinyInteger('qualification_event');

            $table->unsignedBigInteger('referrer_reward_xp')->nullable();
            $table->unsignedBigInteger('referred_reward_xp')->nullable();

            // Qualification status
            $table->tinyInteger('qualification_status')
                ->default(ReferralQualificationStatus::PENDING->value);

            $table->timestamp('qualified_at')->nullable();

            $table->timestamps();

            $table->index([
                'campaign_id',
                'qualification_status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
