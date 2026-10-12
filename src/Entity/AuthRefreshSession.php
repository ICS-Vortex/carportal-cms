<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concern\Timestamps;
use App\Entity\Concern\UuidId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'auth_refresh_sessions')]
#[ORM\Index(name: 'idx_auth_refresh_sessions_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_auth_refresh_sessions_family_id', columns: ['family_id'])]
#[ORM\Index(name: 'idx_auth_refresh_sessions_expires_at', columns: ['expires_at'])]
#[ORM\HasLifecycleCallbacks]
class AuthRefreshSession
{
    use UuidId;
    use Timestamps;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: UuidType::NAME)]
    private ?Uuid $familyId = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private string $tokenHash = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    #[ORM\ManyToOne(targetEntity: AuthRefreshSession::class)]
    #[ORM\JoinColumn(name: 'replaced_by_session_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?AuthRefreshSession $replacedBy = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    public function __toString(): string
    {
        return $this->tokenHash;
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

    public function getFamilyId(): ?Uuid
    {
        return $this->familyId;
    }

    public function setFamilyId(?Uuid $familyId): self
    {
        $this->familyId = $familyId;

        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): self
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTimeImmutable $revokedAt): self
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    public function getReplacedBy(): ?AuthRefreshSession
    {
        return $this->replacedBy;
    }

    public function setReplacedBy(?AuthRefreshSession $replacedBy): self
    {
        $this->replacedBy = $replacedBy;

        return $this;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): self
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }
}
