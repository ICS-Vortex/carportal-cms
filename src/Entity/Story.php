<?php

declare(strict_types=1);

namespace App\Entity;

use App\Catalog\Slugs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stories')]
#[ORM\UniqueConstraint(name: 'uniq_story_slug', columns: ['slug'])]
#[ORM\HasLifecycleCallbacks]
class Story
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 200)]
    private string $slug = '';

    #[ORM\Column(length: 280)]
    private string $excerpt = '';

    #[ORM\Column(type: 'text')]
    private string $body = '';

    #[ORM\Column]
    private bool $published = false;

    public function __toString(): string
    {
        return $this->title;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function ensureSlug(): void
    {
        if ('' === $this->slug) {
            $this->slug = Slugs::from($this->title);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }
}
