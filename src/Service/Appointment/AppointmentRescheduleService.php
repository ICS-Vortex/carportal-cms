<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use App\Repository\ServiceLogRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AppointmentRescheduleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceLogRepository $serviceLogs,
        private readonly AppointmentSlotCalculator $slotCalculator,
        private readonly ScheduleBlockEvaluator $scheduleBlocks,
    ) {
    }

    /**
     * @return array{ok: true}|array{ok: false, message: string}
     */
    public function reschedule(ServiceLog $log, ServiceStation $station, \DateTimeImmutable $day, string $time): array
    {
        if ($log->getRecordKind() !== ServiceLog::RECORD_KIND_APPOINTMENT) {
            return ['ok' => false, 'message' => 'Переносити можна лише записи на СТО.'];
        }

        if ($day->format('Y-m-d') < KyivClock::todayDateString()) {
            return ['ok' => false, 'message' => 'Неможливо перенести в минуле.'];
        }

        if ($this->scheduleBlocks->isWholeDayClosed($station, $day) || $this->scheduleBlocks->isSlotBlocked($station, $day, $time)) {
            return ['ok' => false, 'message' => 'Обраний час закритий для запису.'];
        }

        $slots = $this->slotCalculator->slotsForDay($station, $day);
        $target = null;

        foreach ($slots as $slot) {
            if ($slot->time === $time) {
                $target = $slot;
                break;
            }
        }

        if ($target === null || !$target->available) {
            $currentTime = $log->getServiceTime()?->format('H:i');
            $currentDate = $log->getServiceDate()?->format('Y-m-d');

            if ($currentDate === $day->format('Y-m-d') && $currentTime === $time) {
                return ['ok' => true];
            }

            return ['ok' => false, 'message' => 'Слот недоступний. Оберіть інший час.'];
        }

        $timeValue = \DateTimeImmutable::createFromFormat('H:i', $time);

        if ($timeValue === false) {
            return ['ok' => false, 'message' => 'Некоректний час.'];
        }

        $this->entityManager->beginTransaction();

        try {
            $booked = $this->serviceLogs->countAppointmentsAtSlot($station, $day, $time, (string) $log->getId());
            $currentTime = $log->getServiceTime()?->format('H:i');
            $currentDate = $log->getServiceDate()?->format('Y-m-d');
            $isSameSlot = $currentDate === $day->format('Y-m-d') && $currentTime === $time;

            if (!$isSameSlot && $booked >= max(1, $station->getAppointmentSlotCapacity())) {
                $this->entityManager->rollback();

                return ['ok' => false, 'message' => 'Слот щойно зайняли.'];
            }

            $log
                ->setServiceDate($day)
                ->setServiceTime($timeValue)
                ->setAppointmentReminderSentAt(null);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return ['ok' => true];
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }
    }
}
