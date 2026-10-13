<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Admin\Appointment\ServiceAppointmentDraft;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use Doctrine\ORM\EntityManagerInterface;

final class AppointmentVehicleRegistrar
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function registerFromDraft(ServiceAppointmentDraft $draft, ServiceStation $station, User $registeredBy): Vehicle
    {
        $owner = $this->resolveOwner($draft);
        $generation = $draft->newGeneration;

        if (!$generation instanceof VehicleGeneration) {
            throw new \InvalidArgumentException('Покоління авто не вказано.');
        }

        $configuration = $this->resolveConfiguration($generation);

        $vehicle = new Vehicle();
        $vehicle
            ->setUser($owner)
            ->setGeneration($generation)
            ->setConfiguration($configuration)
            ->setYear($draft->newYear ?? (int) date('Y'))
            ->setLicensePlate($this->normalizePlate($draft->newLicensePlate))
            ->setMileageKm(max(0, $draft->newMileageKm))
            ->setRegisteredBy($registeredBy)
            ->setRegisteredAtStation($station);

        $this->entityManager->persist($vehicle);

        return $vehicle;
    }

    private function resolveOwner(ServiceAppointmentDraft $draft): User
    {
        $email = mb_strtolower(trim((string) $draft->newOwnerEmail));
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing instanceof User) {
            return $existing;
        }

        $owner = new User();
        $owner
            ->setEmail($email)
            ->setFullName(trim((string) $draft->newOwnerName))
            ->setRole('user')
            ->setLocale('uk');

        $this->entityManager->persist($owner);

        return $owner;
    }

    private function resolveConfiguration(VehicleGeneration $generation): VehicleConfiguration
    {
        $configuration = $this->entityManager->getRepository(VehicleConfiguration::class)->findOneBy(
            ['generation' => $generation],
            ['createdAt' => 'ASC']
        );

        if (!$configuration instanceof VehicleConfiguration) {
            throw new \RuntimeException('Для обраного покоління немає комплектації в довіднику. Додайте її в CMS.');
        }

        return $configuration;
    }

    private function normalizePlate(?string $plate): string
    {
        return mb_strtoupper(trim((string) $plate));
    }
}
