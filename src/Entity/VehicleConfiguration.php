<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_configurations')]
#[ORM\UniqueConstraint(name: 'uniq_vehicle_configurations_generation_slug', columns: ['generation_id', 'slug'])]
#[ORM\Index(name: 'idx_vehicle_configurations_generation_id', columns: ['generation_id'])]
#[ORM\HasLifecycleCallbacks]
class VehicleConfiguration
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?VehicleGeneration $generation = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $engineLabel = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $transmissionLabel = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $drivetrainLabel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fuelType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    public function __toString(): string
    {
        return trim($this->engineLabel.' '.$this->transmissionLabel);
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEngineLabel(): string
    {
        return $this->engineLabel;
    }

    public function setEngineLabel(string $engineLabel): self
    {
        $this->engineLabel = $engineLabel;

        return $this;
    }

    public function getTransmissionLabel(): string
    {
        return $this->transmissionLabel;
    }

    public function setTransmissionLabel(string $transmissionLabel): self
    {
        $this->transmissionLabel = $transmissionLabel;

        return $this;
    }

    public function getDrivetrainLabel(): ?string
    {
        return $this->drivetrainLabel;
    }

    public function setDrivetrainLabel(?string $drivetrainLabel): self
    {
        $this->drivetrainLabel = $drivetrainLabel;

        return $this;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function setFuelType(?string $fuelType): self
    {
        $this->fuelType = $fuelType;

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
}
