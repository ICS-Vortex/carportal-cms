<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\CreatedAt;
use App\Entity\Concern\UuidId;
use App\Repository\ServiceStationScheduleBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceStationScheduleBlockRepository::class)]
#[ORM\Table(name: 'service_station_schedule_blocks')]
#[ORM\Index(name: 'idx_schedule_blocks_station_dates', columns: ['station_id', 'start_date', 'end_date'])]
#[ORM\HasLifecycleCallbacks]
class ServiceStationScheduleBlock
{
    use UuidId;
    use CreatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?ServiceStation $station = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $labelUk = '';

    public function __toString(): string
    {
        return $this->labelUk !== '' ? $this->labelUk : 'Блокування';
    }

    public function getStation(): ?ServiceStation
    {
        return $this->station;
    }

    public function setStation(?ServiceStation $station): self
    {
        $this->station = $station;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getLabelUk(): string
    {
        return $this->labelUk;
    }

    public function setLabelUk(string $labelUk): self
    {
        $this->labelUk = $labelUk;

        return $this;
    }

    public function isWholeDay(): bool
    {
        return $this->startTime === null && $this->endTime === null;
    }
}
