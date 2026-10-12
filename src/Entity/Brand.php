<?php

declare(strict_types=1);

namespace App\Entity;

use App\Catalog\Slugs;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'brands')]
#[ORM\UniqueConstraint(name: 'uniq_brand_slug', columns: ['slug'])]
#[ORM\HasLifecycleCallbacks]
class Brand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 140)]
    private string $slug = '';

    #[ORM\Column(length: 80)]
    private string $country = '';

    #[ORM\Column(nullable: true)]
    private ?int $foundedYear = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $tagline = null;

    /** @var Collection<int, Vehicle> */
    #[ORM\OneToMany(mappedBy: 'brand', targetEntity: Vehicle::class)]
    private Collection $vehicles;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function ensureSlug(): void
    {
        if ('' === $this->slug) {
            $this->slug = Slugs::from($this->name);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getFoundedYear(): ?int
    {
        return $this->foundedYear;
    }

    public function setFoundedYear(?int $foundedYear): self
    {
        $this->foundedYear = $foundedYear;

        return $this;
    }

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): self
    {
        $this->tagline = $tagline;

        return $this;
    }

    /** @return Collection<int, Vehicle> */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }
}
