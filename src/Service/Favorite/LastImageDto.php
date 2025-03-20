<?php

declare(strict_types=1);

namespace App\Service\Favorite;

use App\Service\FrameConfiguration\DisplayMode;

class LastImageDto
{
    private string $url;
    private ?string $title;
    private ?string $artist;
    private DisplayMode $displayMode;

    private ?int $entityId;

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
}
