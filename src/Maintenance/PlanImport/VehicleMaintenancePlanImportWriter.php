<?php

declare(strict_types=1);

namespace App\Maintenance\PlanImport;

use App\Entity\MaintenanceProcedure;
use App\Entity\Vehicle;
use App\Entity\VehicleMaintenancePlan;
use App\Entity\VehicleMaintenancePlanItem;
use Doctrine\ORM\EntityManagerInterface;

final class VehicleMaintenancePlanImportWriter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<ParsedPlanRow> $rows
     */
    public function replacePlanForVehicle(Vehicle $vehicle, array $rows, string $templateName): int
    {
        $existing = $this->entityManager->getRepository(VehicleMaintenancePlan::class)
            ->findOneBy(['vehicle' => $vehicle]);
        if ($existing !== null) {
            $this->entityManager->remove($existing);
            $this->entityManager->flush();
        }

        $plan = new VehicleMaintenancePlan();
        $plan->setVehicle($vehicle);
        $plan->setTemplate(null);
        $plan->setTemplateName($templateName);
        $plan->setSourceTemplateUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($plan);

        $baselineDate = new \DateTimeImmutable('today');
        $baselineMileage = $vehicle->getMileageKm();
        $imported = 0;

        foreach ($rows as $index => $row) {
            $procedure = null;
            if ($row->procedureCode !== null) {
                $procedure = $this->entityManager->getRepository(MaintenanceProcedure::class)->findOneBy([
                    'code' => $row->procedureCode,
                    'deleted' => false,
                ]);
            }

            $item = new VehicleMaintenancePlanItem();
            $item->setPlan($plan);
            $item->setProcedure($procedure);
            $item->setProcedureTitle($procedure?->getTitleUk() ?? $row->titleUk);
            $item->setProcedureCategory($procedure?->getCategory() ?? $row->category);
            $item->setIntervalKm($row->intervalKm ?? $procedure?->getDefaultIntervalKm());
            $item->setIntervalMonths($row->intervalMonths ?? $procedure?->getDefaultIntervalMonths());
            $item->setNotesUk($row->notesUk ?? $procedure?->getDescriptionUk());
            $item->setLaborPriceUah($row->laborPriceUah ?? $procedure?->getLaborPriceUah());
            $item->setSortOrder($index + 1);
            $item->setBaselineDate($baselineDate);
            $item->setBaselineMileageKm($baselineMileage);
            $item->setStatus('pending');
            $this->entityManager->persist($item);
            ++$imported;
        }

        $this->entityManager->flush();

        return $imported;
    }
}
