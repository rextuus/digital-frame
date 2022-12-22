<?php

namespace App\Entity;

use App\Repository\FrameConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FrameConfigurationRepository::class)]
class FrameConfiguration
{
    public const MODE_UNSPLASH = 1;
    public const MODE_SPOTIFY = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $mode = null;

    #[ORM\Column]
    private bool $next = false;

    #[ORM\Column(length: 255)]
    private ?string $currentTag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function isNext(): bool
    {
        return $this->next;
    }

    public function setNext(bool $next): self
    {
        $this->next = $next;

        return $this;
    }

    public function getCurrentTag(): ?string
    {
        return $this->currentTag;
    }

    public function setCurrentTag(string $currentTag): self
    {
        $this->currentTag = $currentTag;

        return $this;
    }
}
