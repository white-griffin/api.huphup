<!doctype html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <title>Test Shipment</title>
</head>

    <body>

        <div class="card">
            <h1>Shipping Simulator</h1>

            @foreach ($shipments as $shipment)

                <div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ccc">

                    <h3>
                        Shipment #{{ $shipment->id }}
                    </h3>

                    <div>
                        Tracking:
                        {{ $shipment->tracking_code }}
                    </div>

                    <div>
                        Status:
                        {{ $shipment->status->name }}
                    </div>

                    <div style="margin-top: 10px">

                        @foreach (\App\Enums\ShipmentStatuses::englishLabels() as $status)

                            <form
                                method="POST"
                                action="{{ route('simulator.shipping.status',[$shipment, $status]) }}"
                                style="display: inline-block"
                            >
                                @csrf

                                <button type="submit">
                                    {{ $status->name }}
                                </button>
                            </form>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </div>
    </body>
</html>
