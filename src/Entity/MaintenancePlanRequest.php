<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'maintenance_plan_requests')]
#[ORM\Index(name: 'idx_maintenance_plan_requests_status_created_at', columns: ['status', 'created_at'])]
#[ORM\HasLifecycleCallbacks]
class MaintenancePlanRequest
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(type: Types::TEXT, options: ['default' => 'open'])]
    private string $status = 'open';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requestNote = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $handledAt = null;

    public function __toString(): string
    {
        return sprintf('%s · %s', $this->vehicle, $this->status);
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRequestNote(): ?string
    {
        return $this->requestNote;
    }

    public function setRequestNote(?string $requestNote): self
    {
        $this->requestNote = $requestNote;

        return $this;
    }

    public function getHandledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function setHandledAt(?\DateTimeImmutable $handledAt): self
    {
        $this->handledAt = $handledAt;

        return $this;
    }
}
