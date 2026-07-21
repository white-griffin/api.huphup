<?php

namespace App\Jobs;

use App\Enums\AppointmentStatuses;
use App\Enums\PaymentStatuses;
use App\Models\Appointment;
use App\Notifications\User\V1\Appointment\AppointmentExpiredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ExpireAppointmentPaymentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $appointmentId
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {

            $appointment = Appointment::query()
                ->lockForUpdate()
                ->find($this->appointmentId);

            if (! $appointment) {
                return;
            }

            // اگر پرداخت شده یا وضعیت تغییر کرده، کاری نکن
            if ($appointment->status !== AppointmentStatuses::PENDING_PAYMENT->value) {
                return;
            }

            $payment = $appointment->payments()
                ->where('payment_status', PaymentStatuses::UNPAID->value)
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $payment) {
                return;
            }

            $appointment->update([
                'status' => AppointmentStatuses::EXPIRED->value,
            ]);

            $payment->update([
                'payment_status' => PaymentStatuses::FAILED->value,
            ]);

            // TODO: نوتیفیکیشن به کاربر
            $appointment->user->notify(new AppointmentExpiredNotification($appointment));
        });
    }

}
