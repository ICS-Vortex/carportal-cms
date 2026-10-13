<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Entity\MaintenanceProcedure;
use App\Entity\MaintenanceStationLaborPrice;
use App\Entity\ServiceStation;
use Doctrine\ORM\EntityManagerInterface;

final class AppointmentLaborEstimateCalculator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<MaintenanceProcedure> $procedures
     */
    public function estimate(ServiceStation $station, array $procedures): ?int
    {
        if ($procedures === []) {
            return null;
        }

        $total = 0;
        $pricedCount = 0;

        foreach ($procedures as $procedure) {
            $price = $this->resolveProcedurePriceUah($station, $procedure);

            if ($price === null) {
                continue;
            }

            $total += $price;
            ++$pricedCount;
        }

        return $pricedCount > 0 ? $total : null;
    }

    private function resolveProcedurePriceUah(ServiceStation $station, MaintenanceProcedure $procedure): ?int
    {
        $stationPrice = $this->entityManager->getRepository(MaintenanceStationLaborPrice::class)->findOneBy([
            'station' => $station,
            'procedure' => $procedure,
            'scheduleItem' => null,
        ]);

        if ($stationPrice instanceof MaintenanceStationLaborPrice) {
            return $stationPrice->getLaborPriceUah();
        }

        return $procedure->getLaborPriceUah();
    }
}
