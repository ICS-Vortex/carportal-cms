<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'maintenance_schedule_templates')]
#[ORM\Index(name: 'idx_maintenance_schedule_templates_deleted', columns: ['deleted'])]
#[ORM\Index(name: 'idx_maintenance_schedule_templates_configuration_id', columns: ['configuration_id'])]
#[ORM\HasLifecycleCallbacks]
class MaintenanceScheduleTemplate
{
    use UuidId;
    use Timestamps;

    #[ORM\Column(type: Types::TEXT)]
    private string $nameUk = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?VehicleGeneration $generation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    private ?VehicleConfiguration $configuration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __toString(): string
    {
        return $this->nameUk;
    }

    public function getNameUk(): string
    {
        return $this->nameUk;
    }

    public function setNameUk(string $nameUk): self
    {
        $this->nameUk = $nameUk;

        return $this;
    }

    public function getGeneration(): ?VehicleGeneration
    {
        return $this->generation;
    }

    public function setGeneration(?VehicleGeneration $generation): self
    {
        $this->generation = $generation;

        return $this;
    }

    public function getConfiguration(): ?VehicleConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?VehicleConfiguration $configuration): self
    {
        $this->configuration = $configuration;

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
