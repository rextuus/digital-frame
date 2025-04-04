<?php

namespace App\Service\FrameConfiguration\Form;

use App\Entity\UnsplashTag;

class ConfigurationData
{
    private int $mode;

    private ?UnsplashTag $tag = null;

    private ?string $newTag;

    private ?string $color;

    private bool $blur = false;

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    public function getTag(): ?UnsplashTag
    {
        return $this->tag;
    }

    public function setTag(?UnsplashTag $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return string|null
     */
    public function getNewTag(): ?string
    {
        return $this->newTag;
    }

    /**
     * @param string|null $newTag
     */
    public function setNewTag(?string $newTag): void
    {
        $this->newTag = $newTag;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): ConfigurationData
    {
        $this->color = $color;
        return $this;
    }

    public function isBlur(): bool
    {
        return $this->blur;
    }

    public function setBlur(bool $blur): ConfigurationData
    {
        $this->blur = $blur;
        return $this;
    }
}