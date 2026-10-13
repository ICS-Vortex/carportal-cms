<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ServiceStationAccess
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function isUnrestricted(User $user): bool
    {
        return !$user->managesAnyServiceStation();
    }

    /** @return list<ServiceStation> */
    public function accessibleActiveStations(User $user): array
    {
        if ($this->isUnrestricted($user)) {
            return $this->entityManager->getRepository(ServiceStation::class)->findBy(
                ['isActive' => true],
                ['sortOrder' => 'ASC', 'nameUk' => 'ASC']
            );
        }

        $stations = [];

        foreach ($user->getManagedServiceStations() as $station) {
            if ($station->isActive()) {
                $stations[] = $station;
            }
        }

        usort(
            $stations,
            static fn (ServiceStation $a, ServiceStation $b): int => [$a->getSortOrder(), $a->getNameUk()] <=> [$b->getSortOrder(), $b->getNameUk()]
        );

        return $stations;
    }

    /** @return list<string> */
    public function accessibleStationIds(User $user): array
    {
        return array_map(
            static fn (ServiceStation $station): string => (string) $station->getId(),
            $this->accessibleActiveStations($user)
        );
    }

    public function canAccessStation(User $user, ?ServiceStation $station): bool
    {
        if ($station === null || !$station->isActive()) {
            return false;
        }

        if ($this->isUnrestricted($user)) {
            return true;
        }

        foreach ($user->getManagedServiceStations() as $managed) {
            if ($managed->getId()?->equals($station->getId())) {
                return true;
            }
        }

        return false;
    }

    public function assertCanAccessStation(User $user, ?ServiceStation $station): void
    {
        if (!$this->canAccessStation($user, $station)) {
            throw new AccessDeniedException('Немає доступу до цього СТО.');
        }
    }

    public function assertCanAccessAppointment(User $user, ServiceLog $log): void
    {
        $this->assertCanAccessStation($user, $log->getServiceStationEntity());
    }

    public function applyStationIdFilter(QueryBuilder $queryBuilder, User $user, string $stationField): void
    {
        if ($this->isUnrestricted($user)) {
            return;
        }

        $ids = $this->accessibleStationIds($user);

        if ($ids === []) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $queryBuilder
            ->andWhere(sprintf('%s IN (:accessibleStationIds)', $stationField))
            ->setParameter('accessibleStationIds', $ids);
    }
}
