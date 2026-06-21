<?php

namespace App\Helpers\Data;

class RoutineProgressData
{
    public function __construct(
        public string $status,
        public int $progressPercent,
        public int $daysLeft,
        public bool $isDueSoon,
        public bool $isDueToday,
        public bool $isOverdue,
        public ?string $nextDueAt = null,
    ) {}
}
