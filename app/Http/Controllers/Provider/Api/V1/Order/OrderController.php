<?php
namespace App\Http\Controllers\Provider\Api\V1\Order;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OrderVendor;
use App\Services\Order\OrderVendorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderVendorService $orderVendorService,
    ) {}

    public function index(Request $request)
    {
        $business = $request->user()->business;

        $orders = OrderVendor::query()
            ->where('business_id', $business->id)
            ->with([
                'order.user',
                'items.product',
                'items.variation',
                'shipments',
            ])
            ->latest()
            ->paginate();

        return ApiResponse::success(
            'عملیات موفق',
            $orders
        );
    }

    public function show(
        Request $request,
        int $orderVendorId,
    ) {
        $business = $request->user()->business;

        $orderVendor = OrderVendor::query()
            ->where('business_id', $business->id)
            ->with([
                'order.user',
                'items.product',
                'items.variation',
                'shipments',
                'payments',
            ])
            ->findOrFail($orderVendorId);

        return ApiResponse::success(
            'عملیات موفق',
            $orderVendor
        );
    }

    public function accept(
        Request $request,
        int $orderVendorId,
    ) {
        $business = $request->user()->business;

        $orderVendor = OrderVendor::query()
            ->where('business_id', $business->id)
            ->findOrFail($orderVendorId);

        try {
            $orderVendor = $this->orderVendorService
                ->accept($orderVendor);

            return ApiResponse::success(
                'سفارش با موفقیت تأیید شد.',
                [
                    'order_vendor' => $orderVendor,
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
        Request $request,
        int $orderVendorId,
    ) {
        $business = $request->user()->business;

        $orderVendor = OrderVendor::query()
            ->where('business_id', $business->id)
            ->findOrFail($orderVendorId);

        try {
            $orderVendor = $this->orderVendorService
                ->reject($orderVendor);

            return ApiResponse::success(
                'سفارش با موفقیت رد شد.',
                [
                    'order_vendor' => $orderVendor,
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
