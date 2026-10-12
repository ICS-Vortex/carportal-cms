<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_maintenance_plans')]
#[ORM\Index(name: 'idx_vehicle_maintenance_plans_vehicle_id', columns: ['vehicle_id'])]
#[ORM\HasLifecycleCallbacks]
class VehicleMaintenancePlan
{
    use UuidId;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MaintenanceScheduleTemplate $template = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $templateName = '';

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $assignedAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $sourceTemplateUpdatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->assignedAt = $now;
        $this->updatedAt = $now;
        $this->sourceTemplateUpdatedAt = $now;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function stampUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->templateName;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getTemplate(): ?MaintenanceScheduleTemplate
    {
        return $this->template;
    }

    public function setTemplate(?MaintenanceScheduleTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getAssignedAt(): \DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function setAssignedAt(\DateTimeImmutable $assignedAt): self
    {
        $this->assignedAt = $assignedAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getSourceTemplateUpdatedAt(): \DateTimeImmutable
    {
        return $this->sourceTemplateUpdatedAt;
    }

    public function setSourceTemplateUpdatedAt(\DateTimeImmutable $sourceTemplateUpdatedAt): self
    {
        $this->sourceTemplateUpdatedAt = $sourceTemplateUpdatedAt;

        return $this;
    }
}
