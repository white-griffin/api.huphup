<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\PaymentGateways;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Wallet\WalletChargeResource;
use App\Http\Resources\V1\User\Wallet\WalletTransactionResource;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $request->user()->getWallet();

        return ApiResponse::Success('عملیات موفق', [
            'available_balance' => $wallet->available_balance,
            'pending_balance' => $wallet->pending_balance,
            'transactions_count' => $wallet->transactions()->count(),
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->getWallet()
            ->transactions()
            ->latest()
            ->paginate(20);

        return ApiResponse::Success('عملیات موفق', WalletTransactionResource::collection($transactions));
    }

    public function charge(
        Request $request,
        WalletService $walletService,
        PaymentService $paymentService
    ) {
        try {
            $data = $request->validate([
                'amount' => ['required', 'integer', 'min:1'],
                'gateway' => [
                    'required',
                    Rule::enum(PaymentGateways::class),
                ],
            ]);

            $gateway = PaymentGateways::from($data['gateway']);

            if ($gateway == PaymentGateways::WALLET) {
                throw ValidationException::withMessages([
                    'gateway' => 'برای شارژ کیف پول باید از درگاه بانکی استفاده کنید.',
                ]);
            }

            $charge = $walletService->createCharge(
                user: $request->user(),
                amount: $data['amount'],
            );

            $result = $paymentService->pay(
                payable: $charge,
                gateway: $gateway,
            );

            return ApiResponse::Success('عملیات موفق',$result);

        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function charges(Request $request)
    {
        try {
            $charges = $request->user()
                ->walletCharges()
                ->with('payments')
                ->latest('id')
                ->paginate(20);

            return ApiResponse::Success('عملیات موفق',
                WalletChargeResource::collection($charges)
            );
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }
}
