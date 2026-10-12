<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_brands')]
#[ORM\HasLifecycleCallbacks]
class VehicleBrand
{
    use UuidId;
    use Timestamps;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $name = '';

    public function __toString(): string
    {
        return $this->name;
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
