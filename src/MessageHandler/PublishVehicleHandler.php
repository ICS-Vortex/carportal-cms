<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Publication;
use App\Entity\Vehicle;
use App\Message\PublishVehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PublishVehicleHandler
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(PublishVehicle $message): void
    {
        $vehicle = $this->entityManager->find(Vehicle::class, $message->vehicleId);
        if (!$vehicle instanceof Vehicle || null === $vehicle->getBrand()) {
            return;
        }

        $publication = new Publication();
        $publication
            ->setVehicle($vehicle)
            ->setSummary(sprintf('%s %s is on the floor.', $vehicle->getBrand()->getName(), $vehicle->getName()));

        $this->entityManager->persist($publication);
        $this->entityManager->flush();
    }
}
