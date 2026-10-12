<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vehicle_generations')]
#[ORM\UniqueConstraint(name: 'uniq_vehicle_generations_model_slug', columns: ['model_id', 'slug'])]
#[ORM\Index(name: 'idx_vehicle_generations_model_id', columns: ['model_id'])]
#[ORM\HasLifecycleCallbacks]
class VehicleGeneration
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?VehicleModel $model = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $name = '';

    #[ORM\Column]
    private int $yearFrom = 2000;

    #[ORM\Column]
    private int $yearTo = 2000;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getModel(): ?VehicleModel
    {
        return $this->model;
    }

    public function setModel(?VehicleModel $model): self
    {
        $this->model = $model;

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

    public function getYearFrom(): int
    {
        return $this->yearFrom;
    }

    public function setYearFrom(int $yearFrom): self
    {
        $this->yearFrom = $yearFrom;

        return $this;
    }

    public function getYearTo(): int
    {
        return $this->yearTo;
    }

    public function setYearTo(int $yearTo): self
    {
        $this->yearTo = $yearTo;

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
