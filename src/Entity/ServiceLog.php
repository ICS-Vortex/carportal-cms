<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\CreatedAt;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service_logs')]
#[ORM\Index(name: 'idx_service_logs_vehicle_id', columns: ['vehicle_id'])]
#[ORM\HasLifecycleCallbacks]
class ServiceLog
{
    use UuidId;
    use CreatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $serviceDate = null;

    #[ORM\Column]
    private int $mileageKm = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $serviceStation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    public function __toString(): string
    {
        return sprintf('%s @ %s km', $this->serviceDate?->format('Y-m-d') ?? 'service', $this->mileageKm);
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

    public function getServiceDate(): ?\DateTimeImmutable
    {
        return $this->serviceDate;
    }

    public function setServiceDate(?\DateTimeImmutable $serviceDate): self
    {
        $this->serviceDate = $serviceDate;

        return $this;
    }

    public function getMileageKm(): int
    {
        return $this->mileageKm;
    }

    public function setMileageKm(int $mileageKm): self
    {
        $this->mileageKm = $mileageKm;

        return $this;
    }

    public function getServiceStation(): ?string
    {
        return $this->serviceStation;
    }

    public function setServiceStation(?string $serviceStation): self
    {
        $this->serviceStation = $serviceStation;

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
