<?php

namespace App\Services\Routines;


use App\Enums\RoutineStatuses;
use App\Helpers\Data\RoutineProgressData;
use App\Models\PetRoutine;
use Carbon\Carbon;

class RoutineProgressService
{
    public function calculate(PetRoutine $routine): RoutineProgressData
    {
        if ($routine->routine_status === RoutineStatuses::PAUSED->value) {
            return $this->paused($routine);
        }

        if ($routine->routine_status === RoutineStatuses::ARCHIVED->value) {
            return $this->archived($routine);
        }

        $now = now()->startOfDay();

        $lastDoneAt = $routine->last_done_at
            ? Carbon::parse($routine->last_done_at)->startOfDay()
            : Carbon::parse($routine->start_date)->startOfDay();

        $nextDueAt = Carbon::parse($routine->next_due_at)->startOfDay();

        $intervalDays = max((int) $routine->interval_days, 1);

        $elapsedDays = $lastDoneAt->diffInDays($now, false);

        $progressPercent = (int) min(
            max(($elapsedDays / $intervalDays) * 100, 0),
            100
        );

        $daysLeft = $now->diffInDays($nextDueAt, false);

        $reminderDaysBefore = $routine->template?->reminder_days_before ?? 3;

        $status = $this->resolveStatus(
            daysLeft: $daysLeft,
            reminderDaysBefore: $reminderDaysBefore
        );

        return new RoutineProgressData(
            status: $status,
            progressPercent: $progressPercent,
            daysLeft: $daysLeft,
            isDueSoon: $status == RoutineStatuses::DUE_SOON->value,
            isDueToday: $status == RoutineStatuses::DUE_TODAY->value,
            isOverdue: $status == RoutineStatuses::OVERDUE->value,
            nextDueAt: $nextDueAt->toDateString(),
        );
    }

    private function resolveStatus(int $daysLeft, int $reminderDaysBefore): string
    {
        if ($daysLeft < 0) {
            return RoutineStatuses::OVERDUE->value;
        }

        if ($daysLeft === 0) {
            return RoutineStatuses::DUE_TODAY->value;
        }

        if ($daysLeft <= $reminderDaysBefore) {
            return RoutineStatuses::DUE_SOON->value;
        }

        return RoutineStatuses::UPCOMING->value;
    }

    private function paused(PetRoutine $routine): RoutineProgressData
    {
        return new RoutineProgressData(
            status: RoutineStatuses::PAUSED->value,
            progressPercent: 0,
            daysLeft: 0,
            isDueSoon: false,
            isDueToday: false,
            isOverdue: false,
            nextDueAt: $routine->next_due_at?->toDateString()
        );
    }

    private function archived(PetRoutine $routine): RoutineProgressData
    {
        return new RoutineProgressData(
            status: RoutineStatuses::ARCHIVED->value,
            progressPercent: 0,
            daysLeft: 0,
            isDueSoon: false,
            isDueToday: false,
            isOverdue: false,
            nextDueAt: $routine->next_due_at?->toDateString()
        );
    }
}
