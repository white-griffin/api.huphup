<?php

namespace App\Services\Appointment;

use App\Enums\AppointmentStatuses;
use App\Enums\WalletTransactionType;
use App\Models\Appointment;
use App\Models\Payment;
use App\Notifications\User\V1\Appointment\AppointmentCancelledNotification;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentHandler
{
    public function succeeded(Appointment $appointment, Payment $payment): void
    {
        DB::transaction(function () use ($payment, $appointment) {

            $appointment->refresh();

            if ($appointment->status === AppointmentStatuses::CONFIRMED->value) {
                return;
            }

            $available = app(AppointmentService::class)->canBook(
                businessId: $appointment->business_id,
                startTime: $appointment->start_time->toDateTimeString(),
                serviceDuration: $appointment->service_duration,
                ignoreAppointmentId: $appointment->id,
            );

            if (! $available) {

                $appointment->update([
                    'status' => AppointmentStatuses::CANCELLED->value,
                ]);

                // ثبت درخواست بازگشت وجه یا انتقال به کیف پول
                app(WalletService::class)->refundPending(
                    from: $appointment->business->getWallet(),
                    to: $appointment->user->getWallet(),
                    amount: $payment->amount,
                    debitType: WalletTransactionType::REFUND,
                    creditType: WalletTransactionType::REFUND,
                    payment: $payment,
                    description: "بازگشت وجه رزرو #{$appointment->id} به دلیل تکمیل ظرفیت"
                );

                // TODO: نوتیفیکیشن / پیامک
                $appointment->user->notify(
                    new AppointmentCancelledNotification(
                        $appointment
                    )
                );

                return;
            }


            $appointment->update([
                'status' => AppointmentStatuses::PENDING_CONFIRMATION->value,
                'notes' => 'بازه زمانی انتخاب‌شده دیگر در دسترس نبود و مبلغ به کیف پول شما بازگشت داده شد.'
            ]);
        });
    }

    public function failed(Appointment $appointment, Payment $payment): void
    {
        DB::transaction(function () use ($appointment) {

            $appointment->refresh();

            if ($appointment->status !== AppointmentStatuses::PENDING_PAYMENT->value) {
                return;
            }

            $appointment->update([
                'status' => AppointmentStatuses::EXPIRED->value,
                'notes' => 'پرداخت ناموفق'
            ]);
        });
    }
}
