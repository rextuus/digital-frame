<?php

namespace App\Service\Image\Unsplash;

use App\Entity\UnsplashImage;
use DateTime;

class UnsplashImageData
{
    private string $name;
    private string $url;
    private string $tag;
    private string $color;
    private ?DateTime $viewed;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return DateTime|null
     */
    public function getViewed(): ?DateTime
    {
        return $this->viewed;
    }

    /**
     * @param DateTime|null $viewed
     */
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