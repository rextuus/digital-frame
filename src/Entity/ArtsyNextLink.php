<?php

namespace App\Entity;

use App\Repository\ArtsyNextLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtsyNextLinkRepository::class)]
class ArtsyNextLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $currentLink = null;

    #[ORM\Column(length: 255)]
    private ?string $nextLink = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stored = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrentLink(): ?string
    {
        return $this->currentLink;
    }

    public function setCurrentLink(string $currentLink): static
    {
        $this->currentLink = $currentLink;

        return $this;
    }

    public function getNextLink(): ?string
    {
        return $this->nextLink;
    }

    public function setNextLink(string $nextLink): static
    {
        $this->nextLink = $nextLink;

        return $this;
    }

    public function getStored(): ?\DateTimeInterface
    {
        return $this->stored;
    }

    public function setStored(?\DateTimeInterface $stored): static
    {
        $this->stored = $stored;

        return $this;
    }
}
