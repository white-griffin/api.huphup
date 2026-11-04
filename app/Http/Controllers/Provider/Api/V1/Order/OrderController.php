<?php
namespace App\Http\Controllers\Provider\Api\V1\Order;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Orders\OrderVendorResource;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\User;
use App\Services\Order\OrderVendorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderVendorService $orderVendorService,
    ) {}

    public function index(Request $request)
    {

        $data = $request->validate([
            'order_number' => ['nullable', 'integer', 'exists:orders,order_number'],
            'user' => ['nullable'],
            'order_status'        => ['nullable', Rule::enum(OrderStatuses::class)],
            'payment_status'        => ['nullable', Rule::enum(PaymentStatuses::class)],
            'date'                => ['nullable', 'date'],
            'from_date'           => ['nullable', 'date'],
            'to_date'             => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page'            => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $orders = OrderVendor::query()
            ->with([
                'order.user',
                'items.product',
                'items.variation',
                'shipments',
                'shipments.events',
            ])
            ->when(
                $data['order_number'] ?? null,
                fn ($query, $orderNumber) => $query->where(
                    'order_number',
                    $orderNumber
                )
            )
            ->when(
                $data['user'] ?? null,
                fn ($query, $user) => $query->whereIn(
                    'user_id',
                    User::where('mobile', 'like', "%{$user}%")
                        ->orWhere('first_name', 'like', "%{$user}%")
                        ->orWhere('last_name', 'like', "%{$user}%")
                        ->select('id')
                )
            )
            ->when(
                $data['order_status'] ?? null,
                fn ($query, $orderStatus) => $query->where('order_status', $orderStatus)
            )
            ->when(
                $data['payment_status'] ?? null,
                fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus)
            )
            ->when(
                $data['date'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', $date)
            )
            ->when(
                $data['from_date'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $data['to_date'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->latest()
            ->paginate($data['per_page'] ?? 20);

        return ApiResponse::Success('عملیات موفق', [
            'orders' => OrderVendorResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'statistics' => $this->orderVendorService->getStats()
        ]);
    }

    public function show(
        int $orderVendorId,
    ) {

        $orderVendor = OrderVendorResource::make(
            OrderVendor::query()
                ->with([
                    'order.user',
                    'items.product',
                    'items.variation',
                    'shipments',
                    'shipments.events',
                    'payments',
                ])
                ->findOrFail($orderVendorId)
        );

        return ApiResponse::success(
            'عملیات موفق',
            $orderVendor
        );
    }

    public function accept(
        int $orderVendorId,
    ) {

        $orderVendor = OrderVendor::query()
            ->findOrFail($orderVendorId);

        try {

            $this->orderVendorService->accept($orderVendor);

            return ApiResponse::success(
                'سفارش با موفقیت تأیید شد.'
            );

        } catch (\DomainException $e) {
            return ApiResponse::fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getMessage()
            );
        }
    }

    public function reject(
        int $orderVendorId,
    ) {

        $orderVendor = OrderVendor::query()
            ->findOrFail($orderVendorId);

        try {
            $orderVendor = $this->orderVendorService
                ->reject($orderVendor);

            return ApiResponse::success(
                'سفارش با موفقیت رد شد.',
                [
                    'order_vendor' => OrderVendorResource::make($orderVendor),
                ]
            );

        } catch (\DomainException $e) {
            return ApiResponse::fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getMessage()
            );
        }
    }

    public function cancelItem(
        Request $request,
        int $orderItemId,
    ) {
        $business = $request->user()->business;

        $orderItem = OrderItem::query()
            ->whereKey($orderItemId)
            ->whereHas(
                'orderVendor',
                fn ($query) => $query->where(
                    'business_id',
                    $business->id
                )
            )
            ->firstOrFail();

        try {

            $orderItem = app(OrderVendorService::class)
                ->cancelItem($orderItem);

            return ApiResponse::success(
                'آیتم سفارش با موفقیت لغو شد.',
                [
                    'order_item' => $orderItem,
                ]
            );

        } catch (\DomainException $e) {

            return ApiResponse::fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getMessage()
            );
        }
    }
}
