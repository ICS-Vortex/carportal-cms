<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VehicleGeneration;
use App\Entity\VehicleModel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleGenerationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const E210 = 'generation.e210';
    public const KF = 'generation.kf';

    public function getDependencies(): array
    {
        return [VehicleModelFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();

        $e210 = $this->generation(VehicleModelFixtures::COROLLA, 'E210', 'e210', 2018, 2026, $faker->optional(0.5)->sentence(10));
        $kf = $this->generation(VehicleModelFixtures::CX5, 'KF', 'kf', 2017, 2025, $faker->optional(0.5)->sentence(10));

        $manager->persist($e210);
        $manager->persist($kf);
        $manager->flush();

        $this->addReference(self::E210, $e210);
        $this->addReference(self::KF, $kf);
    }

    private function generation(string $modelReference, string $name, string $slug, int $from, int $to, ?string $notes): VehicleGeneration
    {
        $model = $this->getReference($modelReference, VehicleModel::class);
        $generation = new VehicleGeneration();
        $generation
            ->setModel($model)
            ->setName($name)
            ->setSlug($slug)
            ->setYearFrom($from)
            ->setYearTo($to)
            ->setNotesUk($notes);

        return $generation;
    }
}
