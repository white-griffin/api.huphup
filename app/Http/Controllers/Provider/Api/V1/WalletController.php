<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\WalletTransactionResource;
use App\Http\Resources\V1\Provider\WalletWithdrawalResource;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * ثبت درخواست برداشت
     */
    public function requestWithdrawal(
        Request $request
    ) {
        try {
            $data = $request->validate([
                'amount' => ['required', 'integer', 'min:1'],
            ]);

            $withdrawal = $this->walletService->requestWithdrawal(
                amount: $data['amount'],
            );

            return ApiResponse::Success('درخواست برداشت با موفقیت ثبت شد.',
                WalletWithdrawalResource::make($withdrawal)
            );
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }

    /**
     * موجودی کیف پول
     */
    public function show()
    {

        try {
            $wallet = app('business')->getWallet();

            return ApiResponse::Success('عملیات موفق',
                [
                    'available_balance' => $wallet->available_balance,
                    'pending_balance' => $wallet->pending_balance,
                ]
            );
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }

    /**
     * تاریخچه تراکنش‌ها
     */
    public function transactions()
    {
        try {
            $transactions = app('business')->getWallet()
                ->transactions()
                ->latest()
                ->paginate(20);

            return ApiResponse::Success('',
                WalletTransactionResource::collection($transactions)
            );
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }

    /**
     * تاریخچه درخواست‌های برداشت
     */
    public function withdrawals()
    {
        try {
            $withdrawals = app('business')
                ->walletWithdrawals()
                ->latest()
                ->paginate(20);

            return ApiResponse::Success('عملیات موفق',WalletWithdrawalResource::collection($withdrawals));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }
}
