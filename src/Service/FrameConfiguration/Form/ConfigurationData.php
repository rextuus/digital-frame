<?php

namespace App\Service\FrameConfiguration\Form;

use App\Entity\SearchTag;

class ConfigurationData
{
    private int $mode;

    private ?SearchTag $tag = null;

    private ?string $newTag;

    private ?string $color;

    private bool $blur = false;

    private ?int $height = null;

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

    public function getTag(): ?SearchTag
    {
        return $this->tag;
    }

    public function setTag(?SearchTag $tag): void
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

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): ConfigurationData
    {
        $this->height = $height;
        return $this;
    }
}