<?php

namespace App\Service\Unsplash;

use App\Entity\UnsplashImage;
use DateTime;

class UnsplashImageData
{
    private string $name;
    private string $url;
    private string $tag;
    private string $color;
    private ?DateTime $viewed;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getViewed(): ?DateTime
    {
        return $this->viewed;
    }

    public function setViewed(?DateTime $viewed): void
    {
        $this->viewed = $viewed;
    }

    public function initFrom(UnsplashImage $image): UnsplashImageData
    {
        $this->setName($image->getName());
        $this->setColor($image->getColor());
        $this->setTag($image->getTag());
        $this->setUrl($image->getUrl());
        $this->setViewed($image->getViewed());

        return $this;
    }
}