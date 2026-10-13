<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Vehicle> */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    /** @return list<Vehicle> */
    public function recent(int $limit = 8): array
    {
        return $this->createQueryBuilder('vehicle')
            ->addSelect('owner', 'generation', 'configuration')
            ->join('vehicle.user', 'owner')
            ->join('vehicle.generation', 'generation')
            ->join('vehicle.configuration', 'configuration')
            ->andWhere('vehicle.deleted = false')
            ->orderBy('vehicle.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<ServiceStation> $accessibleStations
     */
    public function createAppointmentBookingQueryBuilder(User $actor, bool $unrestricted, array $accessibleStations): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('vehicle')
            ->addSelect('owner', 'generation', 'model', 'brand')
            ->join('vehicle.user', 'owner')
            ->join('vehicle.generation', 'generation')
            ->join('generation.model', 'model')
            ->join('model.brand', 'brand')
            ->andWhere('vehicle.deleted = false')
            ->orderBy('vehicle.updatedAt', 'DESC');

        if ($unrestricted) {
            return $queryBuilder;
        }

        if ($accessibleStations === []) {
            $queryBuilder->andWhere('1 = 0');

            return $queryBuilder;
        }

        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ServiceLog::class, 'service_log')
            ->andWhere('service_log.vehicle = vehicle')
            ->andWhere('service_log.serviceStationEntity IN (:accessibleStations)');

        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                'vehicle.registeredAtStation IN (:accessibleStations)',
                'vehicle.registeredBy = :bookingActor',
                $queryBuilder->expr()->exists($subQuery->getDQL())
            ))
            ->setParameter('accessibleStations', $accessibleStations)
            ->setParameter('bookingActor', $actor);

        return $queryBuilder;
    }
}
