<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_maintenance_plan_items')]
#[ORM\Index(name: 'idx_vehicle_maintenance_plan_items_plan_id', columns: ['plan_id'])]
#[ORM\HasLifecycleCallbacks]
class VehicleMaintenancePlanItem
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?VehicleMaintenancePlan $plan = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MaintenanceProcedure $procedure = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $procedureTitle = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $procedureCategory = '';

    #[ORM\Column(nullable: true)]
    private ?int $intervalKm = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalMonths = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastCompletedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $lastCompletedMileageKm = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ServiceLog $lastServiceLog = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['default' => 'CURRENT_DATE'])]
    private \DateTimeImmutable $baselineDate;

    #[ORM\Column(options: ['default' => 0])]
    private int $baselineMileageKm = 0;

    public function __construct()
    {
        $this->baselineDate = new \DateTimeImmutable('today');
    }

    public function __toString(): string
    {
        return $this->procedureTitle;
    }

    public function getPlan(): ?VehicleMaintenancePlan
    {
        return $this->plan;
    }

    public function setPlan(?VehicleMaintenancePlan $plan): self
    {
        $this->plan = $plan;

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

    public function getProcedureTitle(): string
    {
        return $this->procedureTitle;
    }

    public function setProcedureTitle(string $procedureTitle): self
    {
        $this->procedureTitle = $procedureTitle;

        return $this;
    }

    public function getProcedureCategory(): string
    {
        return $this->procedureCategory;
    }

    public function setProcedureCategory(string $procedureCategory): self
    {
        $this->procedureCategory = $procedureCategory;

        return $this;
    }

    public function getIntervalKm(): ?int
    {
        return $this->intervalKm;
    }

    public function setIntervalKm(?int $intervalKm): self
    {
        $this->intervalKm = $intervalKm;

        return $this;
    }

    public function getIntervalMonths(): ?int
    {
        return $this->intervalMonths;
    }

    public function setIntervalMonths(?int $intervalMonths): self
    {
        $this->intervalMonths = $intervalMonths;

        return $this;
    }

    public function getNotesUk(): ?string
    {
        return $this->notesUk;
    }

    public function setNotesUk(?string $notesUk): self
    {
        $this->notesUk = $notesUk;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastCompletedAt(): ?\DateTimeImmutable
    {
        return $this->lastCompletedAt;
    }

    public function setLastCompletedAt(?\DateTimeImmutable $lastCompletedAt): self
    {
        $this->lastCompletedAt = $lastCompletedAt;

        return $this;
    }

    public function getLastCompletedMileageKm(): ?int
    {
        return $this->lastCompletedMileageKm;
    }

    public function setLastCompletedMileageKm(?int $lastCompletedMileageKm): self
    {
        $this->lastCompletedMileageKm = $lastCompletedMileageKm;

        return $this;
    }

    public function getLastServiceLog(): ?ServiceLog
    {
        return $this->lastServiceLog;
    }

    public function setLastServiceLog(?ServiceLog $lastServiceLog): self
    {
        $this->lastServiceLog = $lastServiceLog;

        return $this;
    }

    public function getBaselineDate(): \DateTimeImmutable
    {
        return $this->baselineDate;
    }

    public function setBaselineDate(\DateTimeImmutable $baselineDate): self
    {
        $this->baselineDate = $baselineDate;

        return $this;
    }

    public function getBaselineMileageKm(): int
    {
        return $this->baselineMileageKm;
    }

    public function setBaselineMileageKm(int $baselineMileageKm): self
    {
        $this->baselineMileageKm = $baselineMileageKm;

        return $this;
    }
}
