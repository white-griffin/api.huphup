<?php

namespace App\Http\Controllers\User\Api\V1\Order;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\User\Orders\OrderListResource;
use App\Http\Resources\V1\User\Orders\OrderResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\OrderItem;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use App\Services\Review\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService   $orderService,
        protected PaymentService $paymentService,
    )
    {
    }

    /**
     * لیست سفارشات کاربر
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with([
                'payments',
                'vendors',
                'vendors.items'
            ])
            ->latest()
            ->paginate(5);

        return ApiResponse::Success('عملیات موفق', [
            'orders' => OrderListResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * ایجاد سفارش
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],

            'shipping_address_id' => [
                'required',
                'integer',
                'exists:user_addresses,id',
            ],

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
            shippingAddressId: $data['shipping_address_id'],
            notes: $data['notes'] ?? null,
        );

        return ApiResponse::Success('عملیات موفق', [
            'order' => OrderResource::make($order),
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
                'vendors',
                'vendors.items',
                'payments',
            ])
            ->findOrFail($id);

        return ApiResponse::Success('عملیات موفق', OrderResource::make($order));
    }


    public function cancel(Request $request, int $orderId)
    {
        $order = $request->user()
            ->orders()
            ->findOrFail($orderId);

        try {
            $order = $this->orderService->cancel($order);

            return ApiResponse::Success(
                'سفارش با موفقیت لغو شد.',
                [
                    'order' => $order,
                ]
            );

        } catch (\DomainException $e) {
            return ApiResponse::Fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getMessage()
            );
        }
    }


    public function review(
        StoreReviewRequest $request,
        OrderItem          $orderItem,
        ReviewService      $reviewService,
    )
    {
        try {
            abort_unless(
                $orderItem->order->user_id === $request->user()->id,
                Response::HTTP_FORBIDDEN
            );

            $review = $reviewService->create(
                source: $orderItem,
                attributes: $request->validated(),
            );

            return ApiResponse::success(
                'نظر با موفقیت ثبت شد.',
                ReviewResource::make(
                    $review->load([
                        'user',
                        'messages.author',
                        'messages.business',
                    ])
                )
            );
        } catch (\Throwable $exception) {
            return ApiResponse::fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getMessage()
            );
        }
    }
}
