<?php

namespace App\Services\Logistics;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Jobs\TrackShipmentJob;
use App\Models\OrderVendor;
use App\Models\Shipment;
use App\Services\Logistics\DTO\AddressData;
use App\Services\Logistics\DTO\CreateShipmentData;
use App\Services\Logistics\DTO\CustomerData;
use Illuminate\Support\Facades\DB;

class ShippingService
{
    public function __construct(
        private readonly LogisticsManager $manager,
    ) {}

    public function create(OrderVendor $orderVendor): Shipment
    {
        return DB::transaction(function () use ($orderVendor) {

            $orderVendor = OrderVendor::query()
                ->lockForUpdate()
                ->with([
                    'order.user',
                    'business',
                ])
                ->findOrFail($orderVendor->id);

            $existingShipment = $orderVendor->shipments()
                ->latest('id')
                ->first();

            if ($existingShipment) {
                return $existingShipment;
            }

            $user = $orderVendor->order->user;
            $business = $orderVendor->business;

            $shippingAddress = $user->addresses()
                ->latest('id')
                ->first();

            if (! $shippingAddress) {
                throw new \DomainException(
                    'آدرس ارسال برای کاربر ثبت نشده است.'
                );
            }

            if (
                $business->latitude === null ||
                $business->longitude === null
            ) {
                throw new \DomainException(
                    'مختصات آدرس فروشگاه ثبت نشده است.'
                );
            }

            if (
                $shippingAddress->latitude === null ||
                $shippingAddress->longitude === null
            ) {
                throw new \DomainException(
                    'مختصات آدرس ارسال ثبت نشده است.'
                );
            }

            $provider = ShipmentProvider::SANDBOX;

            $data = new CreateShipmentData(
                reference: $orderVendor->order->order_number,

                origin: new AddressData(
                    address: $business->address,
                    latitude: (float) $business->latitude,
                    longitude: (float) $business->longitude,
                ),

                destination: new AddressData(
                    address: $shippingAddress->address,
                    latitude: (float) $shippingAddress->latitude,
                    longitude: (float) $shippingAddress->longitude,
                ),

                customer: new CustomerData(
                    name: $user->name,
                    phone: $user->phone,
                ),

                price: (int) $orderVendor->total_amount,
            );

            $driver = $this->manager->driver($provider);

            $result = $driver->createShipment($data);

            $shipment = $orderVendor->shipments()->create([
                'provider' => $provider->value,
                'provider_order_id' => $result->providerOrderId,
                'tracking_code' => $result->trackingCode,
                'status' => $result->status->value,
                'provider_data' => $result->providerData,
            ]);

            $shipment->events()->create([
                'status' => $result->status->value,
                'payload' => $result->providerData,
            ]);

            if (
                ! in_array($result->status, [
                    ShipmentStatuses::DELIVERED,
                    ShipmentStatuses::CANCELLED,
                ], true)
            ) {
                TrackShipmentJob::dispatch($shipment)
                    ->delay(now()->addMinute())
                    ->afterCommit();
            }

            return $shipment->fresh([
                'orderVendor',
                'events',
            ]);
        });
    }

    public function cancel(Shipment $shipment): void
    {
        $shipment->refresh();

        if (in_array($shipment->status, [
            ShipmentStatuses::DELIVERED,
            ShipmentStatuses::CANCELLED,
        ], true)) {
            return;
        }

        $driver = $this->manager->driver($shipment->provider);

        $success = $driver->cancelShipment($shipment);

        if (! $success) {
            throw new \DomainException(
                'لغو درخواست ارسال توسط سرویس لجستیک انجام نشد.'
            );
        }

        $shipment->updateStatus(
            ShipmentStatuses::CANCELLED
        );
    }
}

