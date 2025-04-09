<?php

namespace App\Entity;

use App\Repository\DisplateImageRepository;
use App\Service\Favorite\FavoriteConvertable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisplateImageRepository::class)]
class DisplateImage implements FavoriteConvertable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $viewed = null;

    #[ORM\ManyToOne(inversedBy: 'displateImages')]
    private ?SearchTag $searchTag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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

    public function getViewed(): ?DateTimeInterface
    {
        return $this->viewed;
    }

    public function setViewed(?DateTimeInterface $viewed): static
    {
        $this->viewed = $viewed;

        return $this;
    }

    public function getSearchTag(): ?SearchTag
    {
        return $this->searchTag;
    }

    public function setSearchTag(?SearchTag $searchTag): static
    {
        $this->searchTag = $searchTag;

        return $this;
    }
}
