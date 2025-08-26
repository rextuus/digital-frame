<?php

declare(strict_types=1);

namespace App\Service\FrameConfiguration\Form;

use App\Entity\FavoriteList;
use App\Entity\FrameConfiguration;
use App\Entity\SearchTag;
use App\Service\FrameConfiguration\DisplayMode;

class ConfigurationUpdateData
{
    private DisplayMode $mode;
    private bool $next;
    private SearchTag $currentTag;
    private int $greetingDisplayTime;
    private int $shutDownTime;

    private ?FavoriteList $currentFavoriteList;
    private ?int $currentFavoriteListIndex;

    public function getMode(): DisplayMode
    {
        return $this->mode;
    }

    public function setMode(DisplayMode $mode): ConfigurationUpdateData
    {
        $this->mode = $mode;
        return $this;
    }

    public function isNext(): bool
    {
        return $this->next;
    }

    public function setNext(bool $next): ConfigurationUpdateData
    {
        $this->next = $next;
        return $this;
    }

    public function getCurrentTag(): SearchTag
    {
        return $this->currentTag;
    }

    public function setCurrentTag(SearchTag $currentTag): ConfigurationUpdateData
    {
        $this->currentTag = $currentTag;

        return $this;
    }

    public function getGreetingDisplayTime(): int
    {
        return $this->greetingDisplayTime;
    }

    public function setGreetingDisplayTime(int $greetingDisplayTime): ConfigurationUpdateData
    {
        $this->greetingDisplayTime = $greetingDisplayTime;
        return $this;
    }

    public function getShutDownTime(): int
    {
        return $this->shutDownTime;
    }

    public function setShutDownTime(int $shutDownTime): ConfigurationUpdateData
    {
        $this->shutDownTime = $shutDownTime;
        return $this;
    }

    public function getCurrentFavoriteList(): ?FavoriteList
    {
        return $this->currentFavoriteList;
    }

    public function setCurrentFavoriteList(?FavoriteList $currentFavoriteList): self
    {
        $this->currentFavoriteList = $currentFavoriteList;
        return $this;
    }

    public function getCurrentFavoriteListIndex(): ?int
    {
        return $this->currentFavoriteListIndex;
    }

    public function setCurrentFavoriteListIndex(?int $currentFavoriteListIndex): self
    {
        $this->currentFavoriteListIndex = $currentFavoriteListIndex;
        return $this;
    }

    public function initFrom(FrameConfiguration $configuration): ConfigurationUpdateData
    {
        $this->setMode($configuration->getMode());
        $this->setGreetingDisplayTime($configuration->getGreetingDisplayTime());
        $this->setCurrentTag($configuration->getCurrentTag());
        $this->setNext($configuration->isNext());
        $this->setShutDownTime($configuration->getShutDownTime());
        $this->setCurrentFavoriteList($configuration->getCurrentFavoriteList());
        $this->setCurrentFavoriteListIndex($configuration->getCurrentFavoriteListIndex());

        return $this;
    }
}
