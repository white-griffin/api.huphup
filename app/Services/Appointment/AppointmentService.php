<?php

namespace App\Services\Appointment;

use App\Enums\ActivityStatus;
use App\Enums\AppointmentStatuses;
use App\Jobs\ExpireAppointmentPaymentJob;
use App\Models\Appointment;
use App\Models\BusinessOffDay;
use App\Models\BusinessSchedule;
use App\Models\BusinessService;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentService
{
    private function getIranianDayOfWeek(Carbon $date): int
    {
        // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
        $map = [
            6 => 0, // Saturday
            0 => 1, // Sunday
            1 => 2, // Monday
            2 => 3, // Tuesday
            3 => 4, // Wednesday
            4 => 5, // Thursday
            5 => 6, // Friday
        ];

        return $map[$date->dayOfWeek];
    }

    public function getAvailableSlots(int $businessId, string $date, ?int $serviceDuration = null): Collection
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $this->getIranianDayOfWeek($date);

        if ($this->isOffDay($businessId, $date)) {
            return collect();
        }

        $schedule = BusinessSchedule::query()
            ->where('business_id', $businessId)
            ->where('day_of_week', $dayOfWeek)
            ->where('activity_status', ActivityStatus::ACTIVE->value)
            ->first();

        if (!$schedule) {
            return collect();
        }

        $slots = $this->generateBaseSlots($date, $schedule);
        $slots = $this->removeBreakSlots($slots, $schedule);
        $slots = $this->removeBookedSlots($slots, $businessId, $date, $schedule->capacity);

        if ($serviceDuration) {
            $slots = $this->filterByServiceDuration($slots, $serviceDuration, $businessId, $date, $schedule);
        }

        return $slots
            ->values()
            ->map(fn (Carbon $slot) => [
                'starts_at' => $slot->toDateTimeString(),
                'start_time' => $slot->format('H:i'),
            ]);
    }

    protected function isOffDay(int $businessId, Carbon $date): bool
    {
        return BusinessOffDay::query()
            ->where('business_id', $businessId)
            ->whereDate('date', $date)
            ->exists();
    }

    protected function generateBaseSlots(Carbon $date, BusinessSchedule $schedule): Collection
    {
        $slots = collect();
        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

        $current = $start->copy();
        while ($current->lt($end)) {
            $slots->push($current->copy());
            $current->addMinutes($schedule->slot_duration);
        }

        return $slots;
    }

    protected function removeBreakSlots(Collection $slots, BusinessSchedule $schedule): Collection
    {
        $breaks = $schedule->breaks;

        return $slots->filter(function (Carbon $slot) use ($breaks) {
            foreach ($breaks as $break) {
                $breakStart = Carbon::parse($slot->format('Y-m-d') . ' ' . $break->start_time);
                $breakEnd = Carbon::parse($slot->format('Y-m-d') . ' ' . $break->end_time);

                if ($slot->gte($breakStart) && $slot->lt($breakEnd)) {
                    return false;
                }
            }
            return true;
        });
    }

    protected function removeBookedSlots(Collection $slots, int $businessId, Carbon $date, int $capacity): Collection
    {
        return $slots->filter(function (Carbon $slot) use ($businessId, $date, $capacity) {
            $overlappingCount = Appointment::query()
                ->where('business_id', $businessId)
                ->whereDate('date', $date)
                ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
                ->where(function ($query) use ($slot) {
                    $query->where('start_time', '<=', $slot->format('H:i:s'))
                        ->where('end_time', '>', $slot->format('H:i:s'));
                })
                ->count();

            return $overlappingCount < $capacity;
        });
    }

    protected function filterByServiceDuration(
        Collection       $slots,
        int              $serviceDuration,
        int              $businessId,
        Carbon           $date,
        BusinessSchedule $schedule
    ): Collection
    {
        return $slots->filter(function (Carbon $slot) use ($serviceDuration, $businessId, $date, $schedule) {
            $endTime = $slot->copy()->addMinutes($serviceDuration);
            $scheduleEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

            // اگر از ساعت پایان کاری بگذرد
            if ($endTime->gt($scheduleEnd)) {
                return false;
            }

            foreach ($schedule->breaks as $break) {
                $breakStart = Carbon::parse($date->format('Y-m-d') . ' ' . $break->start_time);
                $breakEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $break->end_time);

                if ($slot->lt($breakEnd) && $endTime->gt($breakStart)) {
                    return false;
                }
            }

            $overlappingCount = Appointment::query()
                ->where('business_id', $businessId)
                ->whereDate('date', $date)
                ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
                ->where(function ($query) use ($slot, $endTime) {
                    $query->where(function ($q) use ($slot, $endTime) {
                        $q->where('start_time', '<', $endTime->format('H:i:s'))
                            ->where('end_time', '>', $slot->format('H:i:s'));
                    });
                })
                ->count();

            return $overlappingCount < $schedule->capacity;
        });
    }

    public function canBook(
        int $businessId,
        string $startTime,
        int $serviceDuration,
        ?int $ignoreAppointmentId = null
    ): bool
    {
        $start = Carbon::parse($startTime);
        $end = $start->copy()->addMinutes($serviceDuration);
        $date = $start->format('Y-m-d');
        $dayOfWeek = $this->getIranianDayOfWeek($start);

        if ($this->isOffDay($businessId, $start)) {
            return false;
        }

        $schedule = BusinessSchedule::query()
            ->where('business_id', $businessId)
            ->where('day_of_week', $dayOfWeek)
            ->where('activity_status', ActivityStatus::ACTIVE->value)
            ->first();

        if (!$schedule) {
            return false;
        }

        $scheduleStart = Carbon::parse($date . ' ' . $schedule->start_time);
        $scheduleEnd = Carbon::parse($date . ' ' . $schedule->end_time);

        if ($start->lt($scheduleStart) || $end->gt($scheduleEnd)) {
            return false;
        }

        foreach ($schedule->breaks as $break) {
            $breakStart = Carbon::parse($date . ' ' . $break->start_time);
            $breakEnd = Carbon::parse($date . ' ' . $break->end_time);

            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                return false;
            }
        }

        $overlappingQuery = Appointment::query()
            ->where('business_id', $businessId)
            ->whereDate('date', $start)
            ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end->format('H:i:s'))
                    ->where('end_time', '>', $start->format('H:i:s'));
            });

        if ($ignoreAppointmentId) {
            $overlappingQuery->where('id', '!=', $ignoreAppointmentId);
        }

        $overlappingCount = $overlappingQuery->count();

        return $overlappingCount < $schedule->capacity;
    }

    public function book(
        BusinessService $businessService,
        int $petId,
        int $userId,
        string $startsAt,
        ?string $note = null,
    ): Appointment
    {
        $startsAt = Carbon::parse($startsAt);
        $serviceDuration = (int) $businessService->duration;
        $servicePrice = (int) $businessService->price;

        abort_if($serviceDuration <= 0, 422, 'مدت زمان سرویس معتبر نیست.');

        $endsAt = $startsAt->copy()->addMinutes($serviceDuration);

        abort_unless(
            $this->canBook($businessService->business_id, $startsAt->toDateTimeString(), $serviceDuration),
            422,
            'زمان مورد نظر در دسترس نیست.'
        );

        $appointment = Appointment::query()
            ->create([
                'business_id' => $businessService->business_id,
                'business_service_id' => $businessService->id,
                'user_id' => $userId,
                'pet_id' => $petId,
                'date' => $startsAt->toDateString(),
                'start_time' => $startsAt,
                'end_time' => $endsAt,
                'service_duration' => $serviceDuration,
                'service_price' => $servicePrice,
                'notes' => $note,
                'status' => AppointmentStatuses::PENDING_PAYMENT->value,
            ]);

//        ExpireAppointmentPaymentJob::dispatch($appointment->id)
//            ->delay(now()->addMinutes(15))
//            ->afterCommit();

        return $appointment;
    }
}
