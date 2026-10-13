<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ServiceLog> */
final class ServiceLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceLog::class);
    }

    /**
     * @return array<string, int> time (HH:MM) => booked count
     */
    public function countAppointmentsGroupedByTime(ServiceStation $station, \DateTimeImmutable $day): array
    {
        $rows = $this->createQueryBuilder('log')
            ->select('log.serviceTime AS serviceTime, COUNT(log.id) AS bookedCount')
            ->andWhere('log.serviceStationEntity = :station')
            ->andWhere('log.serviceDate = :day')
            ->andWhere('log.recordKind = :kind')
            ->setParameter('station', $station)
            ->setParameter('day', $day)
            ->setParameter('kind', ServiceLog::RECORD_KIND_APPOINTMENT)
            ->groupBy('log.serviceTime')
            ->getQuery()
            ->getArrayResult();

        $counts = [];

        foreach ($rows as $row) {
            $time = $row['serviceTime'];

            if ($time instanceof \DateTimeInterface) {
                $counts[$time->format('H:i')] = (int) $row['bookedCount'];
            }
        }

        return $counts;
    }

    /**
     * @return list<ServiceLog>
     */
    public function findAppointmentsForStationBetween(
        ServiceStation $station,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('log')
            ->addSelect('vehicle', 'owner', 'generation', 'model', 'brand', 'items', 'procedure')
            ->join('log.vehicle', 'vehicle')
            ->join('vehicle.user', 'owner')
            ->join('vehicle.generation', 'generation')
            ->join('generation.model', 'model')
            ->join('model.brand', 'brand')
            ->leftJoin('log.items', 'items')
            ->leftJoin('items.procedure', 'procedure')
            ->andWhere('log.serviceStationEntity = :station')
            ->andWhere('log.recordKind = :kind')
            ->andWhere('log.serviceDate BETWEEN :from AND :to')
            ->setParameter('station', $station)
            ->setParameter('kind', ServiceLog::RECORD_KIND_APPOINTMENT)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('log.serviceDate', 'ASC')
            ->addOrderBy('log.serviceTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAppointmentsAtSlot(
        ServiceStation $station,
        \DateTimeImmutable $day,
        string $time,
        ?string $excludeLogId = null,
    ): int {
        $timeValue = \DateTimeImmutable::createFromFormat('H:i', $time) ?: null;

        if ($timeValue === null) {
            return 0;
        }

        $query = $this->createQueryBuilder('log')
            ->select('COUNT(log.id)')
            ->andWhere('log.serviceStationEntity = :station')
            ->andWhere('log.serviceDate = :day')
            ->andWhere('log.serviceTime = :time')
            ->andWhere('log.recordKind = :kind')
            ->setParameter('station', $station)
            ->setParameter('day', $day)
            ->setParameter('time', $timeValue)
            ->setParameter('kind', ServiceLog::RECORD_KIND_APPOINTMENT);

        if ($excludeLogId !== null) {
            $query->andWhere('log.id != :excludeId')->setParameter('excludeId', $excludeLogId);
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<ServiceLog>
     */
    public function findAppointmentsNeedingReminder(\DateTimeImmutable $windowStart, \DateTimeImmutable $windowEnd): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $ids = $connection->fetchFirstColumn(
            '
                SELECT id
                FROM service_logs
                WHERE record_kind = :kind
                  AND appointment_reminder_sent_at IS NULL
                  AND service_time IS NOT NULL
                  AND (service_date::timestamp + service_time) >= :window_start
                  AND (service_date::timestamp + service_time) < :window_end
            ',
            [
                'kind' => ServiceLog::RECORD_KIND_APPOINTMENT,
                'window_start' => $windowStart->format('Y-m-d H:i:s'),
                'window_end' => $windowEnd->format('Y-m-d H:i:s'),
            ]
        );

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('log')
            ->addSelect('vehicle', 'owner', 'items', 'procedure')
            ->join('log.vehicle', 'vehicle')
            ->join('vehicle.user', 'owner')
            ->leftJoin('log.items', 'items')
            ->leftJoin('items.procedure', 'procedure')
            ->andWhere('log.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
