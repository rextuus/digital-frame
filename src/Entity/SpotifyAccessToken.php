<?php

namespace App\Entity;

use App\Repository\SpotifyAccessTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpotifyAccessTokenRepository::class)]
class SpotifyAccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000)]
    private ?string $accessToken = null;

    #[ORM\Column(length: 1000)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $expirationDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpirationDate(): int
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(int $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
