<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VehicleBrand;
use App\Entity\VehicleModel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleModelFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const HRV = 'model.hr-v';
    public const COROLLA = 'model.corolla';
    public const CX5 = 'model.cx-5';

    public function getDependencies(): array
    {
        return [VehicleBrandFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $hrv = $this->model(VehicleBrandFixtures::HONDA, 'HR-V', 'hr-v');
        $corolla = $this->model(VehicleBrandFixtures::TOYOTA, 'Corolla', 'corolla');
        $cx5 = $this->model(VehicleBrandFixtures::MAZDA, 'CX-5', 'cx-5');

        $manager->persist($hrv);
        $manager->persist($corolla);
        $manager->persist($cx5);
        $manager->flush();

        $this->addReference(self::HRV, $hrv);
        $this->addReference(self::COROLLA, $corolla);
        $this->addReference(self::CX5, $cx5);
    }

    private function model(string $brandReference, string $name, string $slug): VehicleModel
    {
        $brand = $this->getReference($brandReference, VehicleBrand::class);
        $model = new VehicleModel();
        $model->setBrand($brand)->setName($name)->setSlug($slug);

        return $model;
    }
}
