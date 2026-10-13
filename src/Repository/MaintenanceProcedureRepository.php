<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaintenanceProcedure;
use App\Entity\ServiceStation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<MaintenanceProcedure> */
class MaintenanceProcedureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceProcedure::class);
    }

    /**
     * @param list<ServiceStation> $accessibleStations
     */
    public function createAdminListingQueryBuilder(User $actor, bool $unrestricted, array $accessibleStations): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('procedure')
            ->andWhere('procedure.deleted = false')
            ->orderBy('procedure.titleUk', 'ASC');

        if ($unrestricted) {
            return $queryBuilder;
        }

        if ($accessibleStations === []) {
            $queryBuilder->andWhere('procedure.createdBy = :procedureActor')
                ->setParameter('procedureActor', $actor);

            return $queryBuilder;
        }

        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'procedure.serviceStation IS NULL',
                    'procedure.serviceStation IN (:accessibleStations)',
                    'procedure.createdBy = :procedureActor'
                )
            )
            ->setParameter('accessibleStations', $accessibleStations)
            ->setParameter('procedureActor', $actor);

        return $queryBuilder;
    }

    public function createBookingChoicesQueryBuilder(?ServiceStation $station, User $actor, bool $unrestricted, array $accessibleStations): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('procedure')
            ->andWhere('procedure.deleted = false')
            ->orderBy('procedure.titleUk', 'ASC');

        if ($station !== null) {
            $queryBuilder->andWhere('procedure.serviceStation IS NULL OR procedure.serviceStation = :bookingStation')
                ->setParameter('bookingStation', $station);
        }

        if ($unrestricted) {
            return $queryBuilder;
        }

        if ($accessibleStations === []) {
            return $queryBuilder->andWhere('procedure.createdBy = :bookingActor')
                ->setParameter('bookingActor', $actor);
        }

        return $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'procedure.serviceStation IS NULL',
                'procedure.serviceStation IN (:bookingAccessibleStations)',
                'procedure.createdBy = :bookingActor'
            )
        )
            ->setParameter('bookingAccessibleStations', $accessibleStations)
            ->setParameter('bookingActor', $actor);
    }
}
