<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\CreatedAt;
use App\Entity\Concern\UuidId;
use App\Repository\ServiceLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceLogRepository::class)]
#[ORM\Table(name: 'service_logs')]
#[ORM\Index(name: 'idx_service_logs_vehicle_id', columns: ['vehicle_id'])]
#[ORM\HasLifecycleCallbacks]
class ServiceLog
{
    public const RECORD_KIND_VISIT = 'visit';
    public const RECORD_KIND_APPOINTMENT = 'appointment';

    use UuidId;
    use CreatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $serviceDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $serviceTime = null;

    #[ORM\Column(length: 20, options: ['default' => self::RECORD_KIND_VISIT])]
    private string $recordKind = self::RECORD_KIND_VISIT;

    #[ORM\Column]
    private int $mileageKm = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $serviceStation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'service_station_id', nullable: true, onDelete: 'SET NULL')]
    private ?ServiceStation $serviceStationEntity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $appointmentReminderSentAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimatedCostUah = null;

    /** @var Collection<int, ServiceLogItem> */
    #[ORM\OneToMany(targetEntity: ServiceLogItem::class, mappedBy: 'serviceLog', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

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

    public function getServiceTime(): ?\DateTimeImmutable
    {
        return $this->serviceTime;
    }

    public function setServiceTime(?\DateTimeImmutable $serviceTime): self
    {
        $this->serviceTime = $serviceTime;

        return $this;
    }

    public function getRecordKind(): string
    {
        return $this->recordKind;
    }

    public function setRecordKind(string $recordKind): self
    {
        $this->recordKind = $recordKind;

        return $this;
    }

    public function getServiceStationEntity(): ?ServiceStation
    {
        return $this->serviceStationEntity;
    }

    public function setServiceStationEntity(?ServiceStation $serviceStationEntity): self
    {
        $this->serviceStationEntity = $serviceStationEntity;

        return $this;
    }

    /** @return Collection<int, ServiceLogItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ServiceLogItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setServiceLog($this);
        }

        return $this;
    }

    public function getAppointmentReminderSentAt(): ?\DateTimeImmutable
    {
        return $this->appointmentReminderSentAt;
    }

    public function setAppointmentReminderSentAt(?\DateTimeImmutable $appointmentReminderSentAt): self
    {
        $this->appointmentReminderSentAt = $appointmentReminderSentAt;

        return $this;
    }

    public function getEstimatedCostUah(): ?int
    {
        return $this->estimatedCostUah;
    }

    public function setEstimatedCostUah(?int $estimatedCostUah): self
    {
        $this->estimatedCostUah = $estimatedCostUah;

        return $this;
    }
}
