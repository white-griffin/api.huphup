<?php

use App\Enums\Referral\ReferralCampaignStatus;
use App\Enums\Referral\ReferralCampaignType;
use App\Enums\Referral\ReferralQualificationEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referral_campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->unsignedTinyInteger('type')
                ->default(ReferralCampaignType::USER->value);

            $table->unsignedTinyInteger('status')
                ->default(ReferralCampaignStatus::DRAFT->value);

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            $table->unsignedSmallInteger('priority')->default(0);

            $table->unsignedTinyInteger('qualification_event')
                ->default(ReferralQualificationEvent::PROFILE_COMPLETED->value);

            $table->unsignedBigInteger('referrer_reward_xp');
            $table->unsignedBigInteger('referred_reward_xp');

            $table->timestamps();

            $table->index(['type', 'status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_campaigns');
    }
};
