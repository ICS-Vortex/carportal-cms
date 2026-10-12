<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
