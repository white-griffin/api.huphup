<?php

namespace App\Services\Appointment;

use App\Enums\AppointmentStatuses;
use App\Enums\PaymentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Appointment;
use App\Models\Payment;
use App\Notifications\User\V1\Appointment\AppointmentCancelledNotification;
use App\Services\Payment\SettlementService;
use App\Services\Wallet\WalletService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentHandler
{
    public function succeeded(
        Appointment $appointment,
        Payment $payment
    ): void {

        DB::transaction(function () use (
            $appointment,
            $payment
        ) {

            $appointment->refresh();


            if (
                $appointment->status != AppointmentStatuses::PENDING_PAYMENT->value
            ) {
                return;
            }



            $available = app(AppointmentService::class)
                ->canBook(
                    businessId: $appointment->business_id,
                    startTime: $appointment->date . ' ' . $appointment->start_time,
                    serviceDuration: $appointment->service_duration,
                    ignoreAppointmentId: $appointment->id,
                );


            if (! $available) {

                $appointment->update([
                    'status' => AppointmentStatuses::CANCELLED->value,
                    'notes' => 'به دلیل تکمیل ظرفیت، رزرو لغو شد.',
                ]);


                app(WalletService::class)
                    ->creditAvailable(
                        wallet: $appointment->user->getWallet(),
                        amount: $payment->amount,
                        type: WalletTransactionType::REFUND,
                        payment: $payment,
                        description: "بازگشت وجه رزرو #{$appointment->id}"
                    );


                $appointment->user->notify(
                    new AppointmentCancelledNotification(
                        $appointment
                    )
                );

                return;
            }


            app(WalletService::class)->creditPending(
                wallet: $appointment->business->getWallet(),
                amount: $payment->amount,
                type: WalletTransactionType::PAYMENT,
                payment: $payment,
                description: "ایجاد موجودی معلق رزرو #{$appointment->id}",
            );

            $appointment->update([
                'status' => AppointmentStatuses::PENDING_CONFIRMATION->value,
            ]);

        });
    }


    public function failed(
        Appointment $appointment,
        Payment $payment
    ): void {

        DB::transaction(function () use ($appointment,$payment) {

            $appointment->refresh();


            if (
                $appointment->status !== AppointmentStatuses::PENDING_PAYMENT->value
            ) {
                return;
            }


            $appointment->update([
                'status' => AppointmentStatuses::EXPIRED->value,
                'notes' => 'پرداخت ناموفق',
            ]);

            $payment->update([
                'payment_status' => PaymentStatuses::FAILED->value,
            ]);

        });
    }
}
