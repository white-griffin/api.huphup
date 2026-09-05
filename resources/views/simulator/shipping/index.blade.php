<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sandbox - Shipping Simulator</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fa;
            color: #1f2937;
            font-family: Tahoma, Arial, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        .shipment-list {
            display: grid;
            gap: 20px;
        }

        .shipment-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .04);
        }

        .shipment-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .shipment-title {
            margin: 0;
            font-size: 19px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 13px;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .info-item {
            background: #f9fafb;
            border-radius: 10px;
            padding: 13px;
        }

        .info-label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
        }

        .actions-title {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .status-button {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            padding: 9px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: .15s;
        }

        .status-button:hover {
            background: #f3f4f6;
        }

        .status-button.current {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }

        .status-button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .empty {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 50px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 700px) {
            .shipment-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .status-button {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>🚚 Shipping Simulator</h1>
        <p>
            شبیه‌ساز پنل راننده برای تست فرآیند ارسال
        </p>
    </div>

    @if($shipments->isEmpty())

        <div class="empty">
            هیچ Shipmentای برای نمایش وجود ندارد.
        </div>

    @else

        <div class="shipment-list">

            @foreach ($shipments as $shipment)

                <div class="shipment-card">

                    <div class="shipment-header">

                        <h2 class="shipment-title">
                            Shipment #{{ $shipment->id }}
                        </h2>

                        <span class="status">
                            {{ $shipment->status->name }}
                        </span>

                    </div>

                    <div class="info-grid">

                        <div class="info-item">
                            <span class="info-label">
                                Tracking Code
                            </span>

                            <span class="info-value">
                                {{ $shipment->tracking_code ?? '-' }}
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">
                                Provider Order ID
                            </span>

                            <span class="info-value">
                                {{ $shipment->provider_order_id ?? '-' }}
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">
                                Order
                            </span>

                            <span class="info-value">
                                #{{ $shipment->order_id }}
                            </span>
                        </div>

                    </div>

                    <div class="actions-title">
                        تغییر وضعیت Shipment
                    </div>

                    <div class="actions">

                        @foreach (\App\Enums\ShipmentStatuses::cases() as $status)

                            <form
                                method="POST"
                                action="{{ route('simulator.shipping.status', $shipment) }}"
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="status"
                                    value="{{ $status->value }}"
                                >

                                <button
                                    type="submit"
                                    class="status-button {{ $shipment->status === $status ? 'current' : '' }}"
                                    {{ $shipment->status === $status ? 'disabled' : '' }}
                                >
                                    {{ $status->name }}
                                </button>

                            </form>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

</body>

</html>
