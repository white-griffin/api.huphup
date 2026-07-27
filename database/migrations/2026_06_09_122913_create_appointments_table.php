<?php

use App\Enums\AppointmentStatuses;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            // snapshot
            $table->unsignedInteger('service_price');   // تومان، snapshot
            $table->unsignedSmallInteger('service_duration'); // دقیقه، snapshot
            $table->tinyInteger('status')
                ->default(AppointmentStatuses::PENDING_PAYMENT->value)
                ->comment(
                    '1=pending_payment, 2=pending_confirmation, 3=confirmed, 4=completed,5=cancelled,6=expired'
                );
            $table->text('notes')->nullable();
            $table->index(['business_id', 'date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
