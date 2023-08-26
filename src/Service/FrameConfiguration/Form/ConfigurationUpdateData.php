<?php

declare(strict_types=1);

namespace App\Service\FrameConfiguration\Form;

use App\Entity\FrameConfiguration;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ConfigurationUpdateData
{
    private ?int $mode;
    private bool $next;
    private string $currentTag;
    private int $greetingDisplayTime;
    private int $shutDownTime;

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode): ConfigurationUpdateData
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

    public function getCurrentTag(): string
    {
        return $this->currentTag;
    }

    public function setCurrentTag(string $currentTag): ConfigurationUpdateData
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
