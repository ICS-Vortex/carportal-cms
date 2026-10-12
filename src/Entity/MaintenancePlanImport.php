<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'maintenance_plan_imports')]
#[ORM\Index(name: 'idx_maintenance_plan_imports_plan_request', columns: ['plan_request_id'])]
#[ORM\Index(name: 'idx_maintenance_plan_imports_vehicle', columns: ['vehicle_id'])]
#[ORM\Index(name: 'idx_maintenance_plan_imports_status_created', columns: ['status', 'created_at'])]
#[ORM\HasLifecycleCallbacks]
class MaintenancePlanImport
{
    use UuidId;
    use Timestamps;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    #[ORM\ManyToOne(inversedBy: 'imports')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MaintenancePlanRequest $planRequest = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $uploadedBy = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $originalFilename = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $storedPath = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::TEXT, options: ['default' => self::STATUS_PENDING])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(nullable: true)]
    private ?int $rowsTotal = null;

    #[ORM\Column(nullable: true)]
    private ?int $rowsImported = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logText = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    public function __toString(): string
    {
        return $this->originalFilename;
    }

    public function getPlanRequest(): ?MaintenancePlanRequest
    {
        return $this->planRequest;
    }

    public function setPlanRequest(?MaintenancePlanRequest $planRequest): self
    {
        $this->planRequest = $planRequest;

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

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStoredPath(): string
    {
        return $this->storedPath;
    }

    public function setStoredPath(string $storedPath): self
    {
        $this->storedPath = $storedPath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

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

    public function getRowsTotal(): ?int
    {
        return $this->rowsTotal;
    }

    public function setRowsTotal(?int $rowsTotal): self
    {
        $this->rowsTotal = $rowsTotal;

        return $this;
    }

    public function getRowsImported(): ?int
    {
        return $this->rowsImported;
    }

    public function setRowsImported(?int $rowsImported): self
    {
        $this->rowsImported = $rowsImported;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getLogText(): ?string
    {
        return $this->logText;
    }

    public function setLogText(?string $logText): self
    {
        $this->logText = $logText;

        return $this;
    }

    public function appendLog(string $line): self
    {
        $this->logText = trim(($this->logText ?? '')."\n".$line);

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Очікує',
            self::STATUS_PROCESSING => 'Обробка',
            self::STATUS_COMPLETED => 'Готово',
            self::STATUS_FAILED => 'Помилка',
            default => $this->status,
        };
    }
}
