<?php

namespace App\Http\Controllers\User\Api\V1\User;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Referral\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function store(Request $request, ReferralService $referralService)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $referral = $referralService->register(
            $request->user(),
            $validated['code']
        );

        return ApiResponse::Success('کد معرف با موفقیت ثبت شد', [
            'referral_id' => $referral->id,
        ]);
    }
}
