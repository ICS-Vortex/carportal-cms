<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\MaintenanceScheduleTemplate;
use App\Entity\ServiceLog;
use App\Entity\Vehicle;
use App\Entity\VehicleMaintenancePlan;
use App\Entity\VehicleMaintenancePlanItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleMaintenancePlanFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            VehicleFixtures::class,
            MaintenanceScheduleTemplateFixtures::class,
            MaintenanceProcedureFixtures::class,
            ServiceLogFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['maintenance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();
        $template = $this->getReference(MaintenanceScheduleTemplateFixtures::HRV, MaintenanceScheduleTemplate::class);
        $serviceLog = $this->getReference(ServiceLogFixtures::HRV_OIL, ServiceLog::class);
        $vehicle = $this->getReference(VehicleFixtures::HRV, Vehicle::class);

        $plan = new VehicleMaintenancePlan();
        $plan
            ->setVehicle($vehicle)
            ->setTemplate($template)
            ->setTemplateName($template->getNameUk())
            ->setSourceTemplateUpdatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-90 days', '-3 days')->format('Y-m-d H:i:s')));

        $manager->persist($plan);
        $manager->persist($this->item(
            $plan,
            MaintenanceProcedureFixtures::ENGINE_OIL,
            0,
            'done',
            $serviceLog,
            $serviceLog->getMileageKm(),
            $serviceLog->getServiceDate(),
        ));
        $manager->persist($this->item(
            $plan,
            MaintenanceProcedureFixtures::CABIN_FILTER,
            1,
            'pending',
            null,
            $faker->numberBetween(40_000, max(40_001, $vehicle->getMileageKm() - 20_000)),
            new \DateTimeImmutable($faker->dateTimeBetween('-24 months', '-3 months')->format('Y-m-d')),
        ));
        $manager->persist($this->item(
            $plan,
            MaintenanceProcedureFixtures::BRAKE_FLUID,
            2,
            'pending',
            null,
            $faker->numberBetween(30_000, max(30_001, $vehicle->getMileageKm() - 35_000)),
            new \DateTimeImmutable($faker->dateTimeBetween('-36 months', '-8 months')->format('Y-m-d')),
        ));
        $manager->flush();
    }

    private function item(VehicleMaintenancePlan $plan, string $procedureReference, int $sort, string $status, ?ServiceLog $serviceLog, int $baselineMileage, \DateTimeImmutable $baselineDate): VehicleMaintenancePlanItem
    {
        $procedure = $this->getReference($procedureReference, MaintenanceProcedure::class);
        $item = new VehicleMaintenancePlanItem();
        $item
            ->setPlan($plan)
            ->setProcedure($procedure)
            ->setProcedureTitle($procedure->getTitleUk())
            ->setProcedureCategory($procedure->getCategory())
            ->setIntervalKm($procedure->getDefaultIntervalKm())
            ->setIntervalMonths($procedure->getDefaultIntervalMonths())
            ->setSortOrder($sort)
            ->setStatus($status)
            ->setBaselineMileageKm($baselineMileage)
            ->setBaselineDate($baselineDate)
            ->setLastServiceLog($serviceLog);

        if ('done' === $status && null !== $serviceLog) {
            $item
                ->setLastCompletedAt($serviceLog->getServiceDate())
                ->setLastCompletedMileageKm($serviceLog->getMileageKm());
        }

        return $item;
    }
}
