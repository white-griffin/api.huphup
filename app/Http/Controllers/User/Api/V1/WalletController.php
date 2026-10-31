<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Wallet\WalletTransactionResource;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $request->user()->getWallet();

        return ApiResponse::Success('عملیات موفق', [
            'available_balance' => $wallet->available_balance,
            'pending_balance' => $wallet->pending_balance,
            'transactions_count' => $wallet->transactions_count,
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
}
