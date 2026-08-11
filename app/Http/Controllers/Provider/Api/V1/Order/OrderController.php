<?php
namespace App\Http\Controllers\Provider\Api\V1\Order;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Orders\OrderVendorResource;
use App\Models\OrderVendor;
use App\Services\Order\OrderVendorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderVendorService $orderVendorService,
    ) {}

    public function index()
    {

        $orders = OrderVendor::query()
            ->with([
                'order.user',
                'items.product',
                'items.variation',
                'shipments',
                'shipments.events',
            ])
            ->latest()
            ->paginate();

        return ApiResponse::Success('عملیات موفق', [
            'orders' => OrderVendorResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
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
            $orderVendor = $this->orderVendorService
                ->accept($orderVendor);

            return ApiResponse::success(
                'سفارش با موفقیت تأیید شد.',
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
}
