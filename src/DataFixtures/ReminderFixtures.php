<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\Reminder;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ReminderFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VehicleFixtures::class,
            MaintenanceProcedureFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['garage'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();
        $hrv = $this->getReference(VehicleFixtures::HRV, Vehicle::class);

        $manager->persist($this->reminder(
            UserFixtures::OLENA,
            VehicleFixtures::HRV,
            MaintenanceProcedureFixtures::ENGINE_OIL,
            null,
            $hrv->getMileageKm() + $faker->numberBetween(3_000, 12_000),
            'upcoming',
        ));
        $manager->persist($this->reminder(
            UserFixtures::ANDRII,
            VehicleFixtures::COROLLA,
            MaintenanceProcedureFixtures::CABIN_FILTER,
            new \DateTimeImmutable($faker->dateTimeBetween('+5 days', '+45 days')->format('Y-m-d')),
            $faker->boolean(60) ? $faker->numberBetween(40_000, 90_000) : null,
            'upcoming',
        ));
        $manager->persist($this->reminder(
            UserFixtures::ANDRII,
            VehicleFixtures::CX5,
            MaintenanceProcedureFixtures::BRAKE_FLUID,
            new \DateTimeImmutable($faker->dateTimeBetween('-21 days', '-1 day')->format('Y-m-d')),
            null,
            'due',
        ));
        $manager->flush();
    }

    private function reminder(string $userReference, string $vehicleReference, string $procedureReference, ?\DateTimeImmutable $dueDate, ?int $dueMileage, string $status): Reminder
    {
        $reminder = new Reminder();
        $reminder
            ->setUser($this->getReference($userReference, User::class))
            ->setVehicle($this->getReference($vehicleReference, Vehicle::class))
            ->setProcedure($this->getReference($procedureReference, MaintenanceProcedure::class))
            ->setDueDate($dueDate)
            ->setDueMileageKm($dueMileage)
            ->setStatus($status);

        return $reminder;
    }
}
