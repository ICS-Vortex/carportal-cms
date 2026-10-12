<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use App\Enum\FuelType;
use App\Enum\TransmissionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleConfigurationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const COROLLA_PETROL = 'configuration.corolla-petrol';
    public const COROLLA_HYBRID = 'configuration.corolla-hybrid';
    public const CX5_FWD = 'configuration.cx5-fwd';
    public const CX5_AWD = 'configuration.cx5-awd';

    public function getDependencies(): array
    {
        return [VehicleGenerationFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $configurations = [
            self::COROLLA_PETROL => $this->configuration(VehicleGenerationFixtures::E210, '16-cvt-fwd', '1.6 Dual VVT-i', TransmissionType::Cvt, 'FWD', FuelType::Benzin),
            self::COROLLA_HYBRID => $this->configuration(VehicleGenerationFixtures::E210, '18-hybrid-ecvt', '1.8 Hybrid', TransmissionType::ECvt, 'FWD', FuelType::Hybrid),
            self::CX5_FWD => $this->configuration(VehicleGenerationFixtures::KF, '20-at-fwd', '2.0 Skyactiv-G', TransmissionType::Auto, 'FWD', FuelType::Benzin),
            self::CX5_AWD => $this->configuration(VehicleGenerationFixtures::KF, '25-at-awd', '2.5 Skyactiv-G', TransmissionType::Auto, 'AWD', FuelType::Benzin),
        ];

        foreach ($configurations as $reference => $configuration) {
            $manager->persist($configuration);
            $this->addReference($reference, $configuration);
        }

        $manager->flush();
    }

    private function configuration(string $generationReference, string $slug, string $engine, TransmissionType $transmission, string $drivetrain, FuelType $fuel): VehicleConfiguration
    {
        $generation = $this->getReference($generationReference, VehicleGeneration::class);
        $configuration = new VehicleConfiguration();
        $configuration
            ->setGeneration($generation)
            ->setSlug($slug)
            ->setEngineLabel($engine)
            ->setTransmissionLabel($transmission)
            ->setDrivetrainLabel($drivetrain)
            ->setFuelType($fuel);

        return $configuration;
    }
}
