<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_models')]
#[ORM\UniqueConstraint(name: 'uniq_vehicle_models_brand_slug', columns: ['brand_id', 'slug'])]
#[ORM\UniqueConstraint(name: 'uniq_vehicle_models_brand_name', columns: ['brand_id', 'name'])]
#[ORM\Index(name: 'idx_vehicle_models_brand_id', columns: ['brand_id'])]
#[ORM\HasLifecycleCallbacks]
class VehicleModel
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?VehicleBrand $brand = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $name = '';

    public function __toString(): string
    {
        return $this->name;
    }

    public function getBrand(): ?VehicleBrand
    {
        return $this->brand;
    }

    public function setBrand(?VehicleBrand $brand): self
    {
        $this->brand = $brand;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
