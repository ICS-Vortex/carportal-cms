<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const HRV = 'vehicle.hrv';
    public const COROLLA = 'vehicle.corolla';
    public const CX5 = 'vehicle.cx5';

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            HondaHrvRu1MaintenanceFixture::class,
            VehicleConfigurationFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['garage'];
    }

    public function load(ObjectManager $manager): void
    {
        $hrv = $this->vehicle(
            UserFixtures::OLENA,
            HondaHrvRu1MaintenanceFixture::GENERATION,
            HondaHrvRu1MaintenanceFixture::CONFIG_MKPP,
        );
        $hrv->setYear(2021);
        $corolla = $this->vehicle(UserFixtures::ANDRII, VehicleGenerationFixtures::E210, VehicleConfigurationFixtures::COROLLA_HYBRID);
        $cx5 = $this->vehicle(UserFixtures::ANDRII, VehicleGenerationFixtures::KF, VehicleConfigurationFixtures::CX5_AWD);

        $manager->persist($hrv);
        $manager->persist($corolla);
        $manager->persist($cx5);
        $manager->flush();

        $this->addReference(self::HRV, $hrv);
        $this->addReference(self::COROLLA, $corolla);
        $this->addReference(self::CX5, $cx5);
    }

    private function vehicle(string $ownerReference, string $generationReference, string $configurationReference): Vehicle
    {
        $generation = $this->getReference($generationReference, VehicleGeneration::class);
        $yearFrom = $generation->getYearFrom() ?? 2015;
        $yearTo = $generation->getYearTo() ?? (int) date('Y');

        $vehicle = new Vehicle();
        $vehicle
            ->setUser($this->getReference($ownerReference, User::class))
            ->setGeneration($generation)
            ->setConfiguration($this->getReference($configurationReference, VehicleConfiguration::class))
            ->setYear(FixtureFaker::uk()->numberBetween($yearFrom, min($yearTo, (int) date('Y'))))
            ->setVin(FixtureFaker::vin())
            ->setLicensePlate(FixtureFaker::licensePlateUa())
            ->setMileageKm(FixtureFaker::mileageKm());

        return $vehicle;
    }
}
