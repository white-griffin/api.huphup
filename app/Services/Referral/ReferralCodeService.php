<?php

namespace App\Services\Referral;

use App\Models\ReferralCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class ReferralCodeService
{
    public function generate(Model $owner): ReferralCode
    {
        $existingCode = $owner->referralCode()->first();

        if ($existingCode) {
            return $existingCode;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (
            ReferralCode::query()
                ->where('code', $code)
                ->exists()
        );

        try {
            return $owner->referralCode()->create([
                'code' => $code,
            ]);
        } catch (QueryException $exception) {

            // احتمال دارد درخواست هم‌زمان دیگری کد را ساخته باشد.
            $existingCode = $owner->referralCode()->first();

            if ($existingCode) {
                return $existingCode;
            }

            throw $exception;
        }
    }
}
