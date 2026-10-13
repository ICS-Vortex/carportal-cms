<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ServiceStation $station = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    /** @var Collection<int, MaintenanceScheduleItem> */
    #[ORM\OneToMany(mappedBy: 'template', targetEntity: MaintenanceScheduleItem::class)]
    private Collection $scheduleItems;

    public function __construct()
    {
        $this->scheduleItems = new ArrayCollection();
    }

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

    public function getStation(): ?ServiceStation
    {
        return $this->station;
    }

    public function setStation(?ServiceStation $station): self
    {
        $this->station = $station;

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

    /** @return Collection<int, MaintenanceScheduleItem> */
    public function getScheduleItems(): Collection
    {
        return $this->scheduleItems;
    }

    public function getActiveScheduleItemsCount(): int
    {
        $count = 0;
        foreach ($this->scheduleItems as $item) {
            if (!$item->isDeleted()) {
                ++$count;
            }
        }

        return $count;
    }

    /** Placeholder for EasyAdmin index column; links are built in the CRUD field formatter. */
    public function getScheduleWorksIndexLink(): string
    {
        return (string) $this->getActiveScheduleItemsCount();
    }

    /** Марка · модель · покоління · комплектація (для списку в CMS). */
    public function getVehicleContextLabel(): string
    {
        $parts = [];
        $generation = $this->generation;
        if ($generation !== null) {
            $model = $generation->getModel();
            if ($model !== null) {
                $brand = $model->getBrand();
                if ($brand !== null) {
                    $parts[] = $brand->getName();
                }
                $parts[] = $model->getName();
            }
            $parts[] = $generation->getName();
        }
        $configuration = $this->configuration;
        if ($configuration !== null) {
            $parts[] = (string) $configuration;
        }

        if ($parts === []) {
            return '—';
        }

        return implode(' · ', $parts);
    }

    /**
     * HTML-таблиця робіт для сторінки перегляду шаблону в CMS.
     */
    public function getScheduleWorksHtml(): string
    {
        $items = [];
        foreach ($this->scheduleItems as $item) {
            if ($item->isDeleted()) {
                continue;
            }
            $procedure = $item->getProcedure();
            if ($procedure === null || $procedure->isDeleted()) {
                continue;
            }
            $items[] = $item;
        }

        usort($items, static function (MaintenanceScheduleItem $a, MaintenanceScheduleItem $b): int {
            return strcasecmp($a->getProcedure()?->getTitleUk() ?? '', $b->getProcedure()?->getTitleUk() ?? '');
        });

        if ($items === []) {
            return '<p class="cars-template-works-empty">—</p>';
        }

        $rows = '';
        foreach ($items as $item) {
            $procedure = $item->getProcedure();
            assert($procedure !== null);
            $title = self::escapeHtml($procedure->getTitleUk());
            $category = self::escapeHtml($procedure->getCategory());
            $km = $item->getIntervalKm() ?? $procedure->getDefaultIntervalKm();
            $months = $item->getIntervalMonths() ?? $procedure->getDefaultIntervalMonths();
            $price = $item->getLaborPriceUah() ?? $procedure->getLaborPriceUah();
            $notes = $item->getNotesUk() ?? $procedure->getDescriptionUk();

            $categoryCell = $category !== ''
                ? sprintf('<span class="cars-chip">%s</span>', $category)
                : '—';

            $rows .= sprintf(
                '<tr><td><b>%s</b></td><td>%s</td><td>%s</td><td>%s</td><td class="cars-template-works-price">%s</td><td class="cars-template-works-note">%s</td></tr>',
                $title,
                $categoryCell,
                self::formatIntervalCell($km, 'км'),
                self::formatIntervalCell($months, 'міс'),
                $price !== null ? self::escapeHtml((string) $price) : '—',
                $notes !== null && $notes !== '' ? self::escapeHtml($notes) : '—',
            );
        }

        return <<<HTML
<table class="cars-template-works-table">
<thead>
<tr>
<th>Робота</th>
<th>Категорія</th>
<th>Кожні, км</th>
<th>Кожні, міс</th>
<th>Ціна, грн</th>
<th>Примітка</th>
</tr>
</thead>
<tbody>
{$rows}
</tbody>
</table>
HTML;
    }

    private static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function formatIntervalCell(?int $value, string $unit): string
    {
        if ($value === null) {
            return '—';
        }

        return self::escapeHtml((string) $value).' '.self::escapeHtml($unit);
    }
}
