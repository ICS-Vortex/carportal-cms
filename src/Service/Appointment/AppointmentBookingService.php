<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Admin\Appointment\ServiceAppointmentDraft;
use App\Entity\ServiceLog;
use App\Entity\ServiceLogItem;
use App\Entity\User;
use App\Repository\ServiceLogRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AppointmentBookingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceLogRepository $serviceLogs,
        private readonly AppointmentSlotCalculator $slotCalculator,
        private readonly AppointmentVehicleRegistrar $vehicleRegistrar,
        private readonly AppointmentLaborEstimateCalculator $estimateCalculator,
    ) {
    }

    /**
     * @return array{ok: true, log: ServiceLog}|array{ok: false, message: string}
     */
    public function book(ServiceAppointmentDraft $draft, User $bookedBy): array
    {
        $station = $draft->station;

        if ($station === null || $draft->serviceDate === null || $draft->serviceTime === null) {
            return ['ok' => false, 'message' => 'Заповніть СТО, дату та час.'];
        }

        if ($draft->registerNewVehicle) {
            try {
                $draft->vehicle = $this->vehicleRegistrar->registerFromDraft($draft, $station, $bookedBy);
            } catch (\Throwable $exception) {
                return ['ok' => false, 'message' => $exception->getMessage()];
            }
        }

        if ($draft->vehicle === null) {
            return ['ok' => false, 'message' => 'Заповніть автомобіль.'];
        }

        if ($draft->serviceDate->format('Y-m-d') < KyivClock::todayDateString()) {
            return ['ok' => false, 'message' => 'Неможливо записати на дату в минулому.'];
        }

        $slots = $this->slotCalculator->slotsForDay($station, $draft->serviceDate);
        $slot = null;

        foreach ($slots as $candidate) {
            if ($candidate->time === $draft->serviceTime) {
                $slot = $candidate;
                break;
            }
        }

        if ($slot === null || !$slot->available) {
            return ['ok' => false, 'message' => 'Обраний час недоступний. Оновіть сторінку та оберіть інший слот.'];
        }

        $timeValue = \DateTimeImmutable::createFromFormat('H:i', $draft->serviceTime);

        if ($timeValue === false) {
            return ['ok' => false, 'message' => 'Некоректний час візиту.'];
        }

        $this->entityManager->beginTransaction();

        try {
            $booked = $this->serviceLogs->countAppointmentsAtSlot($station, $draft->serviceDate, $draft->serviceTime);

            if ($booked >= max(1, $station->getAppointmentSlotCapacity())) {
                $this->entityManager->rollback();

                return ['ok' => false, 'message' => 'Слот щойно зайняли. Оберіть інший час.'];
            }

            $log = new ServiceLog();
            $log
                ->setVehicle($draft->vehicle)
                ->setServiceDate($draft->serviceDate)
                ->setServiceTime($timeValue)
                ->setRecordKind(ServiceLog::RECORD_KIND_APPOINTMENT)
                ->setMileageKm($draft->vehicle->getMileageKm())
                ->setServiceStation($station->getNameUk())
                ->setServiceStationEntity($station)
                ->setNotesUk($draft->notesUk)
                ->setEstimatedCostUah(
                    $draft->estimatedCostUah ?? $this->estimateCalculator->estimate($station, $draft->procedures)
                );

            foreach ($draft->procedures as $procedure) {
                $item = new ServiceLogItem();
                $item->setProcedure($procedure);
                $log->addItem($item);
            }

            $this->entityManager->persist($log);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return ['ok' => true, 'log' => $log];
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }
    }
}
