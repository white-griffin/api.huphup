<?php

use App\Enums\WalletWithdrawalStatuses;
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
        Schema::create('wallet_withdrawals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->tinyInteger('status')
                ->default(WalletWithdrawalStatuses::PENDING->value);

            $table->text('rejection_reason')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->string('payout_reference')->nullable();
            $table->json('payout_metadata')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawals');
    }
};
