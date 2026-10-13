<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\MaintenanceProcedureRepository::class)]
#[ORM\Table(name: 'maintenance_procedures')]
#[ORM\Index(name: 'idx_maintenance_procedures_deleted', columns: ['deleted'])]
#[ORM\HasLifecycleCallbacks]
class MaintenanceProcedure
{
    use UuidId;
    use Timestamps;

    #[ORM\Column(type: Types::TEXT)]
    private string $code = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $titleUk = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionUk = null;

    #[ORM\Column(nullable: true)]
    private ?int $defaultIntervalKm = null;

    #[ORM\Column(nullable: true)]
    private ?int $defaultIntervalMonths = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $category = '';

    /** Estimated labor price in whole UAH (орієнтовна вартість робіт). */
    #[ORM\Column(nullable: true)]
    private ?int $laborPriceUah = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by_user_id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'service_station_id', nullable: true, onDelete: 'CASCADE')]
    private ?ServiceStation $serviceStation = null;

    /** @var Collection<int, MaintenanceScheduleItem> */
    #[ORM\OneToMany(mappedBy: 'procedure', targetEntity: MaintenanceScheduleItem::class)]
    private Collection $scheduleItems;

    public function __construct()
    {
        $this->scheduleItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->titleUk;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitleUk(): string
    {
        return $this->titleUk;
    }

    public function setTitleUk(string $titleUk): self
    {
        $this->titleUk = $titleUk;

        return $this;
    }

    public function getDescriptionUk(): ?string
    {
        return $this->descriptionUk;
    }

    public function setDescriptionUk(?string $descriptionUk): self
    {
        $this->descriptionUk = $descriptionUk;

        return $this;
    }

    public function getDefaultIntervalKm(): ?int
    {
        return $this->defaultIntervalKm;
    }

    public function setDefaultIntervalKm(?int $defaultIntervalKm): self
    {
        $this->defaultIntervalKm = $defaultIntervalKm;

        return $this;
    }

    public function getDefaultIntervalMonths(): ?int
    {
        return $this->defaultIntervalMonths;
    }

    public function setDefaultIntervalMonths(?int $defaultIntervalMonths): self
    {
        $this->defaultIntervalMonths = $defaultIntervalMonths;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

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

    /** @return Collection<int, MaintenanceScheduleItem> */
    public function getScheduleItems(): Collection
    {
        return $this->scheduleItems;
    }

    /** Human-readable list of schedule templates (car variants) that use this procedure. */
    public function getScheduleTemplatesLabel(): string
    {
        $labels = [];
        foreach ($this->scheduleItems as $item) {
            if ($item->isDeleted()) {
                continue;
            }
            $template = $item->getTemplate();
            if ($template === null || $template->isDeleted()) {
                continue;
            }
            $labels[$template->getNameUk()] = $template->getNameUk();
        }

        if ($labels === []) {
            return '—';
        }

        return implode(', ', array_values($labels));
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getServiceStation(): ?ServiceStation
    {
        return $this->serviceStation;
    }

    public function setServiceStation(?ServiceStation $serviceStation): self
    {
        $this->serviceStation = $serviceStation;

        return $this;
    }

    public function isGlobalCatalog(): bool
    {
        return $this->serviceStation === null;
    }
}
