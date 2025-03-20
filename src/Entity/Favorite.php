<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use App\Service\FrameConfiguration\DisplayMode;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: DisplayMode::class)]
    private ?DisplayMode $displayMode = null;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 255)]
    private ?string $displayUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $artist = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisplayMode(): ?DisplayMode
    {
        return $this->displayMode;
    }

    public function setDisplayMode(DisplayMode $displayMode): static
    {
        $this->displayMode = $displayMode;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getDisplayUrl(): ?string
    {
        return $this->displayUrl;
    }

    public function setDisplayUrl(string $displayUrl): static
    {
        $this->displayUrl = $displayUrl;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
