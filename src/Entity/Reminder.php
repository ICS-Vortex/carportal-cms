<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reminders')]
#[ORM\UniqueConstraint(name: 'idx_reminders_user_vehicle_procedure', columns: ['user_id', 'vehicle_id', 'procedure_id'])]
#[ORM\Index(name: 'idx_reminders_user_due_date', columns: ['user_id', 'due_date'])]
#[ORM\HasLifecycleCallbacks]
class Reminder
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MaintenanceProcedure $procedure = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $dueMileageKm = null;

    #[ORM\Column(options: ['default' => 30])]
    private int $remindDaysBefore = 30;

    #[ORM\Column(options: ['default' => true])]
    private bool $notifyInApp = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $notifyEmail = true;

    #[ORM\Column(type: Types::TEXT, options: ['default' => 'upcoming'])]
    private string $status = 'upcoming';

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastSentAt = null;

    public function __toString(): string
    {
        return sprintf('%s · %s', $this->procedure?->getTitleUk(), $this->status);
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

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getProcedure(): ?MaintenanceProcedure
    {
        return $this->procedure;
    }

    public function setProcedure(?MaintenanceProcedure $procedure): self
    {
        $this->procedure = $procedure;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getDueMileageKm(): ?int
    {
        return $this->dueMileageKm;
    }

    public function setDueMileageKm(?int $dueMileageKm): self
    {
        $this->dueMileageKm = $dueMileageKm;

        return $this;
    }

    public function getRemindDaysBefore(): int
    {
        return $this->remindDaysBefore;
    }

    public function setRemindDaysBefore(int $remindDaysBefore): self
    {
        $this->remindDaysBefore = $remindDaysBefore;

        return $this;
    }

    public function isNotifyInApp(): bool
    {
        return $this->notifyInApp;
    }

    public function setNotifyInApp(bool $notifyInApp): self
    {
        $this->notifyInApp = $notifyInApp;

        return $this;
    }

    public function isNotifyEmail(): bool
    {
        return $this->notifyEmail;
    }

    public function setNotifyEmail(bool $notifyEmail): self
    {
        $this->notifyEmail = $notifyEmail;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastSentAt(): ?\DateTimeImmutable
    {
        return $this->lastSentAt;
    }

    public function setLastSentAt(?\DateTimeImmutable $lastSentAt): self
    {
        $this->lastSentAt = $lastSentAt;

        return $this;
    }
}
