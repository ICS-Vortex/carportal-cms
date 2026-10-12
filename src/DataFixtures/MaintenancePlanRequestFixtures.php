<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenancePlanRequest;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class MaintenancePlanRequestFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VehicleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['maintenance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();

        $request = new MaintenancePlanRequest();
        $request
            ->setUser($this->getReference(UserFixtures::ANDRII, User::class))
            ->setVehicle($this->getReference(VehicleFixtures::CX5, Vehicle::class))
            ->setStatus('open')
            ->setRequestNote($faker->realText($faker->numberBetween(80, 220)));

        $manager->persist($request);
        $manager->flush();
    }
}
