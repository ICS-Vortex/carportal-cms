<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VehicleBrand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class VehicleBrandFixtures extends Fixture implements FixtureGroupInterface
{
    public const HONDA = 'brand.honda';
    public const TOYOTA = 'brand.toyota';
    public const MAZDA = 'brand.mazda';

    public static function getGroups(): array
    {
        return ['catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $honda = $this->brand('Honda', 'honda');
        $toyota = $this->brand('Toyota', 'toyota');
        $mazda = $this->brand('Mazda', 'mazda');

        $manager->persist($honda);
        $manager->persist($toyota);
        $manager->persist($mazda);
        $manager->flush();

        $this->addReference(self::HONDA, $honda);
        $this->addReference(self::TOYOTA, $toyota);
        $this->addReference(self::MAZDA, $mazda);
    }

    private function brand(string $name, string $slug): VehicleBrand
    {
        $brand = new VehicleBrand();
        $brand->setName($name)->setSlug($slug);

        return $brand;
    }
}
