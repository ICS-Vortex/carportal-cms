<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\CreatedAt;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service_log_items')]
#[ORM\HasLifecycleCallbacks]
class ServiceLogItem
{
    use UuidId;
    use CreatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ServiceLog $serviceLog = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?MaintenanceProcedure $procedure = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesUk = null;

    public function __toString(): string
    {
        return (string) $this->procedure;
    }

    public function getServiceLog(): ?ServiceLog
    {
        return $this->serviceLog;
    }

    public function setServiceLog(?ServiceLog $serviceLog): self
    {
        $this->serviceLog = $serviceLog;

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
