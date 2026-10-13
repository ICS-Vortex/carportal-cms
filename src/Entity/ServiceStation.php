<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service_stations')]
#[ORM\UniqueConstraint(name: 'uniq_service_stations_slug', columns: ['slug'])]
#[ORM\HasLifecycleCallbacks]
class ServiceStation
{
    use UuidId;
    use Timestamps;

    #[ORM\Column(type: Types::TEXT)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $nameUk = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cityUk = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $addressUk = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(options: ['default' => 60])]
    private int $appointmentSlotMinutes = 60;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, options: ['default' => '09:00:00'])]
    private ?\DateTimeImmutable $appointmentDayStart = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, options: ['default' => '18:00:00'])]
    private ?\DateTimeImmutable $appointmentDayEnd = null;

    #[ORM\Column(options: ['default' => 2])]
    private int $appointmentSlotCapacity = 2;

    public function __toString(): string
    {
        return $this->nameUk;
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

    public function getNameUk(): string
    {
        return $this->nameUk;
    }

    public function setNameUk(string $nameUk): self
    {
        $this->nameUk = $nameUk;

        return $this;
    }

    public function getCityUk(): ?string
    {
        return $this->cityUk;
    }

    public function setCityUk(?string $cityUk): self
    {
        $this->cityUk = $cityUk;

        return $this;
    }

    public function getAddressUk(): ?string
    {
        return $this->addressUk;
    }

    public function setAddressUk(?string $addressUk): self
    {
        $this->addressUk = $addressUk;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getAppointmentSlotMinutes(): int
    {
        return $this->appointmentSlotMinutes;
    }

    public function setAppointmentSlotMinutes(int $appointmentSlotMinutes): self
    {
        $this->appointmentSlotMinutes = $appointmentSlotMinutes;

        return $this;
    }

    public function getAppointmentDayStart(): ?\DateTimeImmutable
    {
        return $this->appointmentDayStart;
    }

    public function setAppointmentDayStart(?\DateTimeImmutable $appointmentDayStart): self
    {
        $this->appointmentDayStart = $appointmentDayStart;

        return $this;
    }

    public function getAppointmentDayEnd(): ?\DateTimeImmutable
    {
        return $this->appointmentDayEnd;
    }

    public function setAppointmentDayEnd(?\DateTimeImmutable $appointmentDayEnd): self
    {
        $this->appointmentDayEnd = $appointmentDayEnd;

        return $this;
    }

    public function getAppointmentSlotCapacity(): int
    {
        return $this->appointmentSlotCapacity;
    }

    public function setAppointmentSlotCapacity(int $appointmentSlotCapacity): self
    {
        $this->appointmentSlotCapacity = $appointmentSlotCapacity;

        return $this;
    }
}
