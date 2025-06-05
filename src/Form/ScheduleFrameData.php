<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SearchTag;
use App\Service\Scheduling\ScheduleAction;

class ScheduleFrameData
{
    private ?ScheduleAction $action = null;
    private ?int $fromHour = null;
    private ?int $fromMinute = null;
    private ?int $toHour = null;
    private ?int $toMinute = null;

    private ?string $identifier = null;
    private ?int $interval = null;

    public function getAction(): ?ScheduleAction
    {
        return $this->action;
    }

    public function setAction(?ScheduleAction $action): ScheduleFrameData
    {
        $this->action = $action;
        return $this;
    }

    public function getFromHour(): ?int
    {
        return $this->fromHour;
    }

    public function setFromHour(?int $fromHour): ScheduleFrameData
    {
        $this->fromHour = $fromHour;
        return $this;
    }

    public function getFromMinute(): ?int
    {
        return $this->fromMinute;
    }

    public function setFromMinute(?int $fromMinute): ScheduleFrameData
    {
        $this->fromMinute = $fromMinute;
        return $this;
    }

    public function getToHour(): ?int
    {
        return $this->toHour;
    }

    public function setToHour(?int $toHour): ScheduleFrameData
    {
        $this->toHour = $toHour;
        return $this;
    }

    public function getToMinute(): ?int
    {
        return $this->toMinute;
    }

    public function setToMinute(?int $toMinute): ScheduleFrameData
    {
        $this->toMinute = $toMinute;
        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): ScheduleFrameData
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(?int $interval): ScheduleFrameData
    {
        $this->interval = $interval;
        return $this;
    }
}
