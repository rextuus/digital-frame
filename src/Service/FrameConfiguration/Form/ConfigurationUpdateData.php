<?php

declare(strict_types=1);

namespace App\Service\FrameConfiguration\Form;

use App\Entity\FrameConfiguration;
use App\Entity\UnsplashTag;
use App\Service\FrameConfiguration\DisplayMode;

class ConfigurationUpdateData
{
    private DisplayMode $mode;
    private bool $next;
    private UnsplashTag $currentTag;
    private int $greetingDisplayTime;
    private int $shutDownTime;

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

    public function getCurrentTag(): UnsplashTag
    {
        return $this->currentTag;
    }

    public function setCurrentTag(UnsplashTag $currentTag): ConfigurationUpdateData
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

    public function initFrom(FrameConfiguration $configuration): ConfigurationUpdateData
    {
        $this->setMode($configuration->getMode());
        $this->setGreetingDisplayTime($configuration->getGreetingDisplayTime());
        $this->setCurrentTag($configuration->getCurrentTag());
        $this->setNext($configuration->isNext());
        $this->setShutDownTime($configuration->getShutDownTime());

        return $this;
    }
}
