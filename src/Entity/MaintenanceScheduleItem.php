<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'maintenance_schedule_items')]
#[ORM\UniqueConstraint(name: 'uniq_maintenance_schedule_items_template_procedure', columns: ['template_id', 'procedure_id'])]
#[ORM\Index(name: 'idx_maintenance_schedule_items_deleted', columns: ['deleted'])]
#[ORM\HasLifecycleCallbacks]
class MaintenanceScheduleItem
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MaintenanceScheduleTemplate $template = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MaintenanceProcedure $procedure = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalKm = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalMonths = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    #[ORM\Column(nullable: true)]
    private ?int $laborPriceUah = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __toString(): string
    {
        return (string) $this->procedure;
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

    public function getProcedure(): ?MaintenanceProcedure
    {
        return $this->procedure;
    }

    public function setProcedure(?MaintenanceProcedure $procedure): self
    {
        $this->procedure = $procedure;

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

    public function getLaborPriceUah(): ?int
    {
        return $this->laborPriceUah;
    }

    public function setLaborPriceUah(?int $laborPriceUah): self
    {
        $this->laborPriceUah = $laborPriceUah;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
