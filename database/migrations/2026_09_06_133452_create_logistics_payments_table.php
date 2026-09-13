<?php

use App\Enums\LogisticsPaymentStatuses;
use App\Enums\ShipmentProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logistics_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_vendor_id')
                ->constrained('order_vendors')
                ->cascadeOnDelete();

            $table->foreignId('shipment_id')
                ->unique()
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->tinyInteger('provider')
                ->default(ShipmentProvider::SANDBOX->value);

            $table->unsignedInteger('amount');

            $table->tinyInteger('status')
                ->default(LogisticsPaymentStatuses::PENDING->value);

            $table->string('invoice_number')->nullable();

            $table->string('payment_reference')->nullable();

            $table->string('payment_method')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('paid_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->json('metadata')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics_payments');
    }
};
