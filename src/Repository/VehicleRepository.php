<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vehicle;
use App\Enum\VehicleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function countByStatus(VehicleStatus $status): int
    {
        return (int) $this->createQueryBuilder('vehicle')
            ->select('COUNT(vehicle.id)')
            ->andWhere('vehicle.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<Vehicle> */
    public function recent(int $limit = 5): array
    {
        return $this->createQueryBuilder('vehicle')
            ->addSelect('brand')
            ->join('vehicle.brand', 'brand')
            ->orderBy('vehicle.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
