<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\ServiceLog;
use App\Entity\ServiceLogItem;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ServiceLogFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const HRV_OIL = 'service-log.hrv-oil';

    public function getDependencies(): array
    {
        return [
            VehicleFixtures::class,
            HondaHrvRu1MaintenanceFixture::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['garage'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();
        $vehicle = $this->getReference(VehicleFixtures::HRV, Vehicle::class);
        $serviceMileage = $faker->numberBetween(
            max(5_000, $vehicle->getMileageKm() - 15_000),
            max(5_001, $vehicle->getMileageKm() - 1_000),
        );

        $log = new ServiceLog();
        $log
            ->setVehicle($vehicle)
            ->setServiceDate(new \DateTimeImmutable($faker->dateTimeBetween('-8 months', '-2 weeks')->format('Y-m-d')))
            ->setMileageKm($serviceMileage)
            ->setServiceStation(FixtureFaker::serviceStationName())
            ->setNotesUk($faker->optional(0.85)->realText($faker->numberBetween(60, 160)));

        $oil = new ServiceLogItem();
        $oil
            ->setServiceLog($log)
            ->setProcedure($this->getReference(HondaHrvRu1MaintenanceFixture::PROCEDURE_ENGINE_OIL, MaintenanceProcedure::class));

        $filter = new ServiceLogItem();
        $filter
            ->setServiceLog($log)
            ->setProcedure($this->getReference(HondaHrvRu1MaintenanceFixture::PROCEDURE_OIL_FILTER, MaintenanceProcedure::class));

        $manager->persist($log);
        $manager->persist($oil);
        $manager->persist($filter);
        $manager->flush();

        $this->addReference(self::HRV_OIL, $log);
    }
}
