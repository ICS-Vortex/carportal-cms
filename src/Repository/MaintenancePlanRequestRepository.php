<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaintenancePlanRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MaintenancePlanRequest>
 */
final class MaintenancePlanRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenancePlanRequest::class);
    }

    public function countOpen(): int
    {
        return (int) $this->createQueryBuilder('request')
            ->select('COUNT(request.id)')
            ->andWhere('request.status = :status')
            ->setParameter('status', 'open')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<MaintenancePlanRequest>
     */
    public function findOpenRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('request')
            ->addSelect('user', 'vehicle')
            ->innerJoin('request.user', 'user')
            ->innerJoin('request.vehicle', 'vehicle')
            ->andWhere('request.status = :status')
            ->setParameter('status', 'open')
            ->orderBy('request.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
