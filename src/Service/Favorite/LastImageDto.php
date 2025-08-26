<?php

declare(strict_types=1);

namespace App\Service\Favorite;

class LastImageDto
{
    private string $url = '';
    private ?string $title;
    private ?string $artist;
    private bool $found = false;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): LastImageDto
    {
        $this->url = $url;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): LastImageDto
    {
        $this->title = $title;
        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): LastImageDto
    {
        $this->artist = $artist;
        return $this;
    }

    public function isFound(): bool
    {
        return $this->found;
    }

    public function setFound(bool $found): LastImageDto
    {
        $this->found = $found;
        return $this;
    }

    public static function fromFavoriteConvertable(FavoriteConvertable $convertable): self
    {
        $dto = new self();

        $dto->setUrl($convertable->getUrl());
        $dto->setTitle($convertable->getTitle());
        $dto->setArtist($convertable->getArtist());

        return $dto;
    }
}
