<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use App\Repository\VehicleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\Table(name: 'vehicles')]
#[ORM\Index(name: 'idx_vehicles_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_vehicles_generation_id', columns: ['generation_id'])]
#[ORM\Index(name: 'idx_vehicles_configuration_id', columns: ['configuration_id'])]
#[ORM\Index(name: 'idx_vehicles_deleted', columns: ['user_id', 'deleted'])]
#[ORM\HasLifecycleCallbacks]
class Vehicle
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?VehicleGeneration $generation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?VehicleConfiguration $configuration = null;

    #[ORM\Column]
    private int $year = 2000;

    #[ORM\Column(type: Types::TEXT, unique: true, nullable: true)]
    private ?string $vin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $licensePlate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $mileageKm = 0;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'registered_by_user_id', nullable: true, onDelete: 'SET NULL')]
    private ?User $registeredBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'registered_at_station_id', nullable: true, onDelete: 'SET NULL')]
    private ?ServiceStation $registeredAtStation = null;

    public function __toString(): string
    {
        return $this->vin ?? trim(sprintf('%s %d', $this->generation?->getName(), $this->year));
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(?string $licensePlate): self
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

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

    public function getRegisteredBy(): ?User
    {
        return $this->registeredBy;
    }

    public function setRegisteredBy(?User $registeredBy): self
    {
        $this->registeredBy = $registeredBy;

        return $this;
    }

    public function getRegisteredAtStation(): ?ServiceStation
    {
        return $this->registeredAtStation;
    }

    public function setRegisteredAtStation(?ServiceStation $registeredAtStation): self
    {
        $this->registeredAtStation = $registeredAtStation;

        return $this;
    }
}
