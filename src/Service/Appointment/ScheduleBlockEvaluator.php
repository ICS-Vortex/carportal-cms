<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Entity\ServiceStation;
use App\Entity\ServiceStationScheduleBlock;
use App\Repository\ServiceStationScheduleBlockRepository;

final class ScheduleBlockEvaluator
{
    public function __construct(
        private readonly ServiceStationScheduleBlockRepository $blocks,
    ) {
    }

    public function isWholeDayClosed(ServiceStation $station, \DateTimeImmutable $day): bool
    {
        foreach ($this->blocks->findActiveForDay($station, $day) as $block) {
            if ($block->isWholeDay()) {
                return true;
            }
        }

        return false;
    }

    public function isSlotBlocked(ServiceStation $station, \DateTimeImmutable $day, string $time): bool
    {
        if ($this->isWholeDayClosed($station, $day)) {
            return true;
        }

        $slotMinutes = $this->timeToMinutes($time);

        foreach ($this->blocks->findActiveForDay($station, $day) as $block) {
            if ($block->isWholeDay()) {
                continue;
            }

            $start = $this->timeToMinutes($block->getStartTime()?->format('H:i') ?? '00:00');
            $end = $this->timeToMinutes($block->getEndTime()?->format('H:i') ?? '23:59');

            if ($slotMinutes >= $start && $slotMinutes < $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<ServiceStationScheduleBlock>
     */
    public function blocksBetween(ServiceStation $station, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->blocks->findOverlapping($station, $from, $to);
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map(intval(...), explode(':', substr($time, 0, 5)));

        return $hours * 60 + $minutes;
    }
}
