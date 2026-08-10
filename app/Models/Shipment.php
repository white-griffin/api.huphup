<?php

namespace App\Models;

use App\Enums\PaymentStatuses;
use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Services\Payment\SettlementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Shipment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'provider' => ShipmentProvider::class,
            'status' => ShipmentStatuses::class,
            'provider_data' => 'array',
        ];
    }

    public function orderVendor(): BelongsTo
    {
        return $this->belongsTo(OrderVendor::class);
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function updateStatus(
        ShipmentStatuses $status,
        array $providerData = [],
    ): void {
        DB::transaction(function () use ($status, $providerData) {

            $this->refresh();

            if (
                $this->status === $status
                && $this->provider_data === $providerData
            ) {
                return;
            }

            $this->update([
                'status' => $status,
                'provider_data' => $providerData,
            ]);

            $this->events()->create([
                'status' => $status,
                'payload' => $providerData,
            ]);

            if ($status === ShipmentStatuses::DELIVERED) {
                $this->loadMissing([
                    'orderVendor.payments',
                ]);

                $payment = $this->orderVendor
                    ->payments()
                    ->where(
                        'payment_status',
                        PaymentStatuses::PAID->value
                    )
                    ->latest('id')
                    ->first();

                if ($payment) {
                    app(SettlementService::class)
                        ->settle($payment);
                }
            }
        });
    }
}
