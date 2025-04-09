<?php

namespace App\Entity;

use App\Repository\ArtsyImageRepository;
use App\Service\Favorite\FavoriteConvertable;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use \App\Service\Artsy\Category;

#[ORM\Entity(repositoryClass: ArtsyImageRepository::class)]
class ArtsyImage implements FavoriteConvertable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $bestResolutionUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $mediumResolutionUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $viewed = null;

    #[ORM\Column]
    private ?float $height = null;

    #[ORM\Column]
    private ?float $width = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $artist = null;

    #[ORM\Column(length: 255)]
    private ?string $maxVersion = null;

    #[ORM\Column(nullable: false, enumType: Category::class, options: ['default' => Category::UNKNOWN])]
    private Category $category = Category::UNKNOWN;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $newCategory = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $nextPageUrlStored = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBestResolutionUrl(): ?string
    {
        return $this->bestResolutionUrl;
    }

    public function setBestResolutionUrl(string $bestResolutionUrl): static
    {
        $this->bestResolutionUrl = $bestResolutionUrl;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getMaxVersion(): ?string
    {
        return $this->maxVersion;
    }

    public function setMaxVersion(string $maxVersion): static
    {
        $this->maxVersion = $maxVersion;

        return $this;
    }

    public function getViewed(): ?DateTime
    {
        return $this->viewed;
    }

    public function setViewed(?DateTime $viewed): ArtsyImage
    {
        $this->viewed = $viewed;
        return $this;
    }

    public function getMediumResolutionUrl(): ?string
    {
        return $this->mediumResolutionUrl;
    }

    public function setMediumResolutionUrl(?string $mediumResolutionUrl): ArtsyImage
    {
        $this->mediumResolutionUrl = $mediumResolutionUrl;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getNewCategory(): ?string
    {
        return $this->newCategory;
    }

    public function setNewCategory(?string $newCategory): static
    {
        $this->newCategory = $newCategory;

        return $this;
    }

    public function getNextPageUrlStored(): ?\DateTimeInterface
    {
        return $this->nextPageUrlStored;
    }

    public function setNextPageUrlStored(?\DateTimeInterface $nextPageUrlStored): static
    {
        $this->nextPageUrlStored = $nextPageUrlStored;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }
}
