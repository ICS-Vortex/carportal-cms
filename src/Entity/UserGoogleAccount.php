<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\CreatedAt;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_google_accounts')]
#[ORM\HasLifecycleCallbacks]
class UserGoogleAccount
{
    use CreatedAt;

    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'googleAccount')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $googleSub = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $email = '';

    public function __toString(): string
    {
        return $this->email;
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

    public function getGoogleSub(): string
    {
        return $this->googleSub;
    }

    public function setGoogleSub(string $googleSub): self
    {
        $this->googleSub = $googleSub;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
