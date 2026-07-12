<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
    ) {
    }

    /**
     * لیست سفارشات کاربر
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with([
                'items.product',
                'items.variation',
                'payments',
            ])
            ->latest()
            ->paginate();

        return ApiResponse::Success('عملیات موفق',$orders);
    }

    /**
     * ایجاد سفارش
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_variation_id' => [
                'required',
                'exists:product_variations,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $order = $this->orderService->create(
            userId: $request->user()->id,
            items: $data['items'],
            notes: $data['notes'] ?? null,
        );

        return ApiResponse::Success( 'عملیات موفق',[
            'order'   => $order,
        ]);
    }

    /**
     * نمایش جزئیات سفارش
     */
    public function show(Request $request, int $id)
    {
        $order = $request->user()
            ->orders()
            ->with([
                'items.product',
                'items.variation',
                'payments',
            ])
            ->findOrFail($id);

        return ApiResponse::Success('عملیات موفق',$order);
    }
}
