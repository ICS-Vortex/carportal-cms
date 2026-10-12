<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Vehicle;
use App\Enum\VehicleStatus;
use App\Message\PublishVehicle;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Vehicle::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Vehicle::class)]
#[AsDoctrineListener(event: Events::postFlush)]
final class VehiclePublishListener
{
    /** @var list<int> */
    private array $pendingIds = [];

    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function postPersist(Vehicle $vehicle, PostPersistEventArgs $event): void
    {
        if (VehicleStatus::Published === $vehicle->getStatus() && null !== $vehicle->getId()) {
            $this->pendingIds[] = $vehicle->getId();
        }
    }

    public function preUpdate(Vehicle $vehicle, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('status') || !$this->isPublished($event->getNewValue('status'))) {
            return;
        }

        if (null === $vehicle->getPublishedAt()) {
            $vehicle->setPublishedAt(new \DateTimeImmutable());
            $event->getObjectManager()->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $event->getObjectManager()->getClassMetadata(Vehicle::class),
                $vehicle,
            );
        }

        if (null !== $vehicle->getId()) {
            $this->pendingIds[] = $vehicle->getId();
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if ([] === $this->pendingIds) {
            return;
        }

        $ids = $this->pendingIds;
        $this->pendingIds = [];

        foreach ($ids as $id) {
            $this->messageBus->dispatch(new PublishVehicle($id));
        }
    }

    private function isPublished(mixed $value): bool
    {
        return $value instanceof VehicleStatus
            ? VehicleStatus::Published === $value
            : VehicleStatus::Published->value === $value;
    }
}
