<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\AppointmentStatuses;
use App\Models\BusinessOffDay;
use App\Models\BusinessSchedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentService
{


    /**
     * تولید روزهای هفته ایرانی
     *
     * @param Carbon $date (Y-m-d)
     * @return int
     */
    private function getIranianDayOfWeek(Carbon $date): int
    {
        // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
        $map = [
            0 => 0, // Saturday   -> شنبه
            1 => 1, // Sunday   -> یکشنبه
            2 => 2, // Monday  -> دوشنبه
            3 => 3, // Tuesday-> سه شنبه
            4 => 4, // Wednesday -> چهارشنبه
            5 => 5, // Thursday   -> پنجشنبه
            6 => 6, // Friday -> جمعه
        ];

        return $map[$date->dayOfWeek];
    }

    /**
     * تولید اسلات‌های آزاد برای یک بیزنس در یک تاریخ مشخص
     *
     * @param int $businessId
     * @param string $date (Y-m-d)
     * @param int|null $serviceDuration (minutes) - برای فیلتر کردن اسلات‌هایی که سرویس جا می‌شود
     * @return Collection
     */
    public function getAvailableSlots(int $businessId, string $date, ?int $serviceDuration = null): Collection
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $this->getIranianDayOfWeek($date);

        // 1. چک کردن روز تعطیل
        if ($this->isOffDay($businessId, $date)) {
            return collect();
        }

        // 2. گرفتن برنامه کاری
        $schedule = BusinessSchedule::query()
            ->where('business_id', $businessId)
            ->where('day_of_week', $dayOfWeek)
            ->where('activity_status', ActivityStatus::ACTIVE->value)
            ->first();

        if (!$schedule) {
            return collect();
        }

        // 3. تولید اسلات‌های پایه
        $slots = $this->generateBaseSlots($date, $schedule);

        // 4. حذف اسلات‌های داخل استراحت
        $slots = $this->removeBreakSlots($slots, $schedule);

        // 5. حذف اسلات‌های پر
        $slots = $this->removeBookedSlots($slots, $businessId, $date, $schedule->capacity);

        // 6. اگر مدت سرویس داده شده، فقط اسلات‌هایی که سرویس جا می‌شود
        if ($serviceDuration) {
            $slots = $this->filterByServiceDuration($slots, $serviceDuration, $businessId, $date, $schedule);
        }

        return $slots;
    }

    /**
     * چک کردن روز تعطیل
     */
    protected function isOffDay(int $businessId, Carbon $date): bool
    {
        return BusinessOffDay::query()
            ->where('business_id', $businessId)
            ->whereDate('date', $date)
            ->exists();
    }

    /**
     * تولید اسلات‌های پایه بر اساس slot_duration
     */
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

    /**
     * حذف اسلات‌های داخل استراحت
     */
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

    /**
     * حذف اسلات‌های پر بر اساس capacity
     */
    protected function removeBookedSlots(Collection $slots, int $businessId, Carbon $date, int $capacity): Collection
    {
        return $slots->filter(function (Carbon $slot) use ($businessId, $date, $capacity) {
            $overlappingCount = Appointment::query()
                ->where('business_id', $businessId)
                ->whereDate('start_time', $date)
                ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
                ->where(function ($query) use ($slot) {
                    $query->where('start_time', '<=', $slot)
                        ->where('end_time', '>', $slot);
                })
                ->count();

            return $overlappingCount < $capacity;
        });
    }

    /**
     * فیلتر کردن اسلات‌هایی که سرویس با مدت مشخص در آن جا می‌شود
     */
    protected function filterByServiceDuration(
        Collection $slots,
        int $serviceDuration,
        int $businessId,
        Carbon $date,
        BusinessSchedule $schedule
    ): Collection {
        return $slots->filter(function (Carbon $slot) use ($serviceDuration, $businessId, $date, $schedule) {
            $endTime = $slot->copy()->addMinutes($serviceDuration);
            $scheduleEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

            // اگر از ساعت پایان کاری بگذرد
            if ($endTime->gt($scheduleEnd)) {
                return false;
            }

            // بررسی تداخل با استراحت‌ها
            foreach ($schedule->breaks as $break) {
                $breakStart = Carbon::parse($date->format('Y-m-d') . ' ' . $break->start_time);
                $breakEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $break->end_time);

                if ($slot->lt($breakEnd) && $endTime->gt($breakStart)) {
                    return false;
                }
            }

            // بررسی تداخل با رزروهای موجود
            $overlappingCount = Appointment::query()
                ->where('business_id', $businessId)
                ->whereDate('start_time', $date)
                ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
                ->where(function ($query) use ($slot, $endTime) {
                    $query->where(function ($q) use ($slot, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $slot);
                    });
                })
                ->count();

            return $overlappingCount < $schedule->capacity;
        });
    }

    /**
     * بررسی امکان رزرو یک اسلات
     */
    public function canBook(int $businessId, string $startTime, int $serviceDuration): bool
    {
        $start = Carbon::parse($startTime);
        $end = $start->copy()->addMinutes($serviceDuration);
        $date = $start->format('Y-m-d');
        $dayOfWeek = $this->getIranianDayOfWeek($date);

        // چک روز تعطیل
        if ($this->isOffDay($businessId, $start)) {
            return false;
        }

        // چک برنامه کاری
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

        // چک محدوده ساعت کاری
        if ($start->lt($scheduleStart) || $end->gt($scheduleEnd)) {
            return false;
        }

        // چک استراحت‌ها
        foreach ($schedule->breaks as $break) {
            $breakStart = Carbon::parse($date . ' ' . $break->start_time);
            $breakEnd = Carbon::parse($date . ' ' . $break->end_time);

            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                return false;
            }
        }

        // چک ظرفیت
        $overlappingCount = Appointment::query()
            ->where('business_id', $businessId)
            ->where('status', '!=', AppointmentStatuses::CANCELLED->value)
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->count();

        return $overlappingCount < $schedule->capacity;
    }
}
