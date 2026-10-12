<?php

declare(strict_types=1);

namespace App\Entity;

use App\Catalog\Slugs;
use App\Enum\BodyStyle;
use App\Enum\Drivetrain;
use App\Enum\VehicleStatus;
use App\Repository\VehicleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\Table(name: 'vehicles')]
#[ORM\UniqueConstraint(name: 'uniq_vehicle_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_vehicle_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Brand $brand = null;

    #[ORM\Column(length: 160)]
    private string $name = '';

    #[ORM\Column(length: 180)]
    private string $slug = '';

    #[ORM\Column]
    private int $modelYear = 2026;

    #[ORM\Column(enumType: BodyStyle::class, length: 40)]
    private BodyStyle $bodyStyle = BodyStyle::Coupe;

    #[ORM\Column(enumType: Drivetrain::class, length: 40)]
    private Drivetrain $drivetrain = Drivetrain::RearWheel;

    #[ORM\Column]
    private int $horsepower = 0;

    #[ORM\Column]
    private int $price = 0;

    #[ORM\Column(enumType: VehicleStatus::class, length: 20)]
    private VehicleStatus $status = VehicleStatus::Draft;

    #[ORM\Column(type: 'text')]
    private string $summary = '';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function __toString(): string
    {
        return trim(sprintf('%s %s', $this->brand?->getName(), $this->name));
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function ensureSlug(): void
    {
        if ('' === $this->slug) {
            $this->slug = Slugs::from($this->name);
        }
    }

    #[ORM\PrePersist]
    public function stampPublication(): void
    {
        if (VehicleStatus::Published === $this->status && null === $this->publishedAt) {
            $this->publishedAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getModelYear(): int
    {
        return $this->modelYear;
    }

    public function setModelYear(int $modelYear): self
    {
        $this->modelYear = $modelYear;

        return $this;
    }

    public function getBodyStyle(): BodyStyle
    {
        return $this->bodyStyle;
    }

    public function setBodyStyle(BodyStyle $bodyStyle): self
    {
        $this->bodyStyle = $bodyStyle;

        return $this;
    }

    public function getDrivetrain(): Drivetrain
    {
        return $this->drivetrain;
    }

    public function setDrivetrain(Drivetrain $drivetrain): self
    {
        $this->drivetrain = $drivetrain;

        return $this;
    }

    public function getHorsepower(): int
    {
        return $this->horsepower;
    }

    public function setHorsepower(int $horsepower): self
    {
        $this->horsepower = $horsepower;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStatus(): VehicleStatus
    {
        return $this->status;
    }

    public function setStatus(VehicleStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
