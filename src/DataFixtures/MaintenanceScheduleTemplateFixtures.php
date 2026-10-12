<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\MaintenanceScheduleItem;
use App\Entity\MaintenanceScheduleTemplate;
use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class MaintenanceScheduleTemplateFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const HYBRID = 'template.hybrid';
    public const CX5 = 'template.cx5';

    public function getDependencies(): array
    {
        return [
            VehicleConfigurationFixtures::class,
            MaintenanceProcedureFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['maintenance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();

        $hybrid = $this->template(VehicleGenerationFixtures::E210, VehicleConfigurationFixtures::COROLLA_HYBRID, 'Corolla 1.8 Hybrid', $faker->sentence(10));
        $cx5 = $this->template(VehicleGenerationFixtures::KF, VehicleConfigurationFixtures::CX5_AWD, 'CX-5 2.5 AWD', $faker->sentence(9));

        $this->items($manager, $hybrid, [
            MaintenanceProcedureFixtures::ENGINE_OIL => [15_000, 12],
            MaintenanceProcedureFixtures::CABIN_FILTER => [15_000, 12],
            MaintenanceProcedureFixtures::BRAKE_FLUID => [null, 24],
            MaintenanceProcedureFixtures::SPARK_PLUGS => [60_000, 72],
        ]);
        $this->items($manager, $cx5, [
            MaintenanceProcedureFixtures::ENGINE_OIL => [10_000, 12],
            MaintenanceProcedureFixtures::AIR_FILTER => [20_000, 24],
            MaintenanceProcedureFixtures::CABIN_FILTER => [15_000, 12],
            MaintenanceProcedureFixtures::BRAKE_FLUID => [null, 24],
        ]);

        $manager->persist($hybrid);
        $manager->persist($cx5);
        $manager->flush();

        $this->addReference(self::HYBRID, $hybrid);
        $this->addReference(self::CX5, $cx5);
    }

    private function template(string $generationReference, string $configurationReference, string $name, string $notes): MaintenanceScheduleTemplate
    {
        $template = new MaintenanceScheduleTemplate();
        $template
            ->setGeneration($this->getReference($generationReference, VehicleGeneration::class))
            ->setConfiguration($this->getReference($configurationReference, VehicleConfiguration::class))
            ->setNameUk($name)
            ->setNotesUk($notes);

        return $template;
    }

    /** @param array<string, array{0: ?int, 1: ?int}> $intervals */
    private function items(ObjectManager $manager, MaintenanceScheduleTemplate $template, array $intervals): void
    {
        foreach ($intervals as $procedureReference => [$km, $months]) {
            $item = new MaintenanceScheduleItem();
            $item
                ->setTemplate($template)
                ->setProcedure($this->getReference($procedureReference, MaintenanceProcedure::class))
                ->setIntervalKm($km)
                ->setIntervalMonths($months);
            $manager->persist($item);
        }
    }
}
