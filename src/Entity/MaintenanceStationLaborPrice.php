<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'maintenance_station_labor_prices')]
#[ORM\HasLifecycleCallbacks]
class MaintenanceStationLaborPrice
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ServiceStation $station = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?MaintenanceProcedure $procedure = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?MaintenanceScheduleItem $scheduleItem = null;

    #[ORM\Column]
    private int $laborPriceUah = 0;

    public function __toString(): string
    {
        $target = $this->procedure?->getTitleUk() ?? $this->scheduleItem?->__toString() ?? 'ціна';

        return sprintf('%s · %d грн', $this->station?->getNameUk() ?? 'СТО', $this->laborPriceUah);
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

    public function getProcedure(): ?MaintenanceProcedure
    {
        return $this->procedure;
    }

    public function setProcedure(?MaintenanceProcedure $procedure): self
    {
        $this->procedure = $procedure;

        return $this;
    }

    public function getScheduleItem(): ?MaintenanceScheduleItem
    {
        return $this->scheduleItem;
    }

    public function setScheduleItem(?MaintenanceScheduleItem $scheduleItem): self
    {
        $this->scheduleItem = $scheduleItem;

        return $this;
    }

    public function getLaborPriceUah(): int
    {
        return $this->laborPriceUah;
    }

    public function setLaborPriceUah(int $laborPriceUah): self
    {
        $this->laborPriceUah = $laborPriceUah;

        return $this;
    }
}
