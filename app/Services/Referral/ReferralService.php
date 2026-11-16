<?php

namespace App\Services\Referral;

use App\Enums\ActivityStatus;
use App\Enums\Referral\ReferralCampaignStatus;
use App\Enums\Referral\ReferralCampaignType;
use App\Enums\Referral\ReferralQualificationEvent;
use App\Enums\Referral\ReferralQualificationStatus;
use App\Enums\Referral\ReferralRewardStatus;
use App\Enums\Referral\ReferralRewardType;
use App\Models\Business;
use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\User;
use App\Services\Xp\XpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class ReferralService
{
    public function __construct(
        private readonly XpService $xpService
    )
    {
    }

    public function register(User $referredUser, string $code): Referral
    {
        return DB::transaction(function () use ($referredUser, $code) {

            $referredUser = User::query()
                ->whereKey($referredUser->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($referredUser->referral()->exists()) {
                throw ValidationException::withMessages([
                    'code' => 'شما قبلاً با کد معرف ثبت‌نام کرده‌اید.',
                ]);
            }

            $referralCode = ReferralCode::query()
                ->where('code', strtoupper(trim($code)))
                ->where('activity_status', ActivityStatus::ACTIVE->value)
                ->first();

            if (!$referralCode) {
                throw ValidationException::withMessages([
                    'code' => 'کد معرف معتبر نیست.',
                ]);
            }

            $referrer = $referralCode->owner;

            if (!$referrer instanceof User && !$referrer instanceof Business) {
                throw ValidationException::withMessages([
                    'code' => 'مالک این کد معرف معتبر نیست.',
                ]);
            }

            if (
                $referrer instanceof User &&
                $referrer->is($referredUser)
            ) {
                throw ValidationException::withMessages([
                    'code' => 'نمی‌توانید از کد معرف خودتان استفاده کنید.',
                ]);
            }

            $campaign = ReferralCampaign::query()
                ->where('type', ReferralCampaignType::USER->value)
                ->where('status', ReferralCampaignStatus::ACTIVE->value)
                ->where('starts_at', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->orderByDesc('priority')
                ->orderBy('id')
                ->first();

            if (!$campaign) {
                throw ValidationException::withMessages([
                    'code' => 'در حال حاضر کمپین فعالی برای معرفی کاربران وجود ندارد.',
                ]);
            }

            $isUserReferrer = $referrer instanceof User;

            return Referral::create([
                'campaign_id' => $campaign->id,

                'referrer_type' => $referrer->getMorphClass(),
                'referrer_id' => $referrer->id,

                'referred_user_id' => $referredUser->id,

                'qualification_event' => $campaign->qualification_event->value,

                'referrer_reward_xp' => $isUserReferrer
                    ? $campaign->referrer_reward_xp
                    : null,

                'referred_reward_xp' => $isUserReferrer
                    ? $campaign->referred_reward_xp
                    : null,

                'qualification_status' => ReferralQualificationStatus::PENDING->value,
            ]);
        }, 3);
    }

    public function qualify(
        User                       $user,
        ReferralQualificationEvent $event
    ): void
    {
        DB::transaction(function () use ($user, $event) {

            $referral = Referral::query()
                ->where('referred_user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$referral) {
                return;
            }

            if ($referral->qualification_status != ReferralQualificationStatus::PENDING->value) {
                return;
            }

            if ($referral->qualification_event != $event) {
                return;
            }

            // فقط زمانی XP می‌دهیم که معرف User باشد.
            if ($referral->referrer_type == (new User())->getMorphClass()) {

                if (
                    $referral->referrer_reward_xp == null ||
                    $referral->referred_reward_xp == null
                ) {
                    throw new LogicException(
                        'Referral reward amounts are missing.'
                    );
                }
                $referrer = User::query()
                    ->findOrFail($referral->referrer_id);

                $this->awardReward(
                    $referral,
                    $referrer,
                    ReferralRewardType::REFERRER,
                    $referral->referrer_reward_xp,
                    $this->xpService
                );

                $this->awardReward(
                    $referral,
                    $user,
                    ReferralRewardType::REFERRED,
                    $referral->referred_reward_xp,
                    $this->xpService
                );
            }

            // برای معرف Business هیچ پاداشی ایجاد نمی‌شود.
            $referral->update([
                'qualification_status' => ReferralQualificationStatus::QUALIFIED->value,
                'qualified_at' => now(),
            ]);

        }, 3);
    }

    private function awardReward(
        Referral           $referral,
        User               $user,
        ReferralRewardType $type,
        int                $amount,
        XpService          $xpService
    ): void
    {

        $role = match ($type) {
            ReferralRewardType::REFERRER => 'referrer',
            ReferralRewardType::REFERRED => 'referred',
        };

        $idempotencyKey = "referral:{$referral->id}:{$role}";

        $reward = ReferralReward::firstOrCreate(
            [
                'referral_id' => $referral->id,
                'type' => $type,
            ],
            [
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => ReferralRewardStatus::PENDING->value,
                'idempotency_key' => $idempotencyKey,
            ]
        );

        $transaction = $xpService->earn(
            user: $user,
            amount: $reward->amount,
            idempotencyKey: $reward->idempotency_key,
            source: 'referral',
            sourceId: $referral->id,
            description: "Referral reward: {$role}",
        );

        $reward->update([
            'status' => ReferralRewardStatus::AWARDED->value,
            'awarded_at' => $transaction->created_at,
        ]);
    }
}
