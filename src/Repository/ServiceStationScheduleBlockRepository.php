<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ServiceStation;
use App\Entity\ServiceStationScheduleBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ServiceStationScheduleBlock> */
final class ServiceStationScheduleBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceStationScheduleBlock::class);
    }

    /**
     * @return list<ServiceStationScheduleBlock>
     */
    public function findOverlapping(ServiceStation $station, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('block')
            ->andWhere('block.startDate <= :to')
            ->andWhere('block.endDate >= :from')
            ->andWhere('block.station IS NULL OR block.station = :station')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('station', $station)
            ->orderBy('block.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<ServiceStationScheduleBlock>
     */
    public function findActiveForDay(ServiceStation $station, \DateTimeImmutable $day): array
    {
        return $this->findOverlapping($station, $day, $day);
    }
}
