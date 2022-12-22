<?php

namespace App\Service\FrameConfiguration\Form;

class ConfigurationData
{
    private int $mode;

    private string $tag;

    private ?string $newTag;

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
}