<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Entity\ServiceStation;
use App\Repository\ServiceLogRepository;

final class AppointmentSlotCalculator
{
    public function __construct(
        private readonly ServiceLogRepository $serviceLogs,
        private readonly ScheduleBlockEvaluator $scheduleBlocks,
    ) {
    }

    /** @return list<AppointmentSlot> */
    public function slotsForDay(ServiceStation $station, \DateTimeImmutable $day): array
    {
        $dateString = $day->format('Y-m-d');
        $today = KyivClock::todayDateString();

        if ($dateString < $today) {
            return [];
        }

        if ($this->scheduleBlocks->isWholeDayClosed($station, $day)) {
            return [];
        }

        $booked = $this->serviceLogs->countAppointmentsGroupedByTime($station, $day);
        $capacity = max(1, $station->getAppointmentSlotCapacity());
        $times = $this->buildSlotTimes($station);
        $isToday = $dateString === $today;
        $nowTime = KyivClock::currentTimeString();

        $slots = [];

        foreach ($times as $time) {
            $bookedCount = $booked[$time] ?? 0;
            $pastSlot = $isToday && $time <= $nowTime;

            $blocked = $this->scheduleBlocks->isSlotBlocked($station, $day, $time);

            $slots[] = new AppointmentSlot(
                time: $time,
                available: !$pastSlot && !$blocked && $bookedCount < $capacity,
                bookedCount: $bookedCount,
                capacity: $capacity,
            );
        }

        return $slots;
    }

    /** @return list<string> */
    private function buildSlotTimes(ServiceStation $station): array
    {
        $start = $this->timeToMinutes($station->getAppointmentDayStart()?->format('H:i') ?? '09:00');
        $end = $this->timeToMinutes($station->getAppointmentDayEnd()?->format('H:i') ?? '18:00');
        $step = max(15, $station->getAppointmentSlotMinutes());
        $times = [];

        for ($cursor = $start; $cursor + $step <= $end; $cursor += $step) {
            $times[] = $this->minutesToTime($cursor);
        }

        return $times;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map(intval(...), explode(':', substr($time, 0, 5)));

        return $hours * 60 + $minutes;
    }

    private function minutesToTime(int $totalMinutes): string
    {
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
