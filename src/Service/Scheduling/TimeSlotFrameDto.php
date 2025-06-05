<?php

declare(strict_types=1);

namespace App\Service\Scheduling;

use DateTimeImmutable;

class TimeSlotFrameDto
{
    public function __construct(
        public int $id,
        public string $identifier,
        public DateTimeImmutable $firstSlot,
        public DateTimeImmutable $lastSlot,
        public int $interval,
        public ?int $fromHour = null,
        public ?int $toHour = null,
        public ?int $fromMinute = null,
        public ?int $toMinute = null,
    ) {
        $this->fromHour = $this->getFromHour();
        $this->toHour = $this->getToHour();
        $this->fromMinute = $this->getFromMinute();
        $this->toMinute = $this->getToMinute();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): TimeSlotFrameDto
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getFirstSlot(): DateTimeImmutable
    {
        return $this->firstSlot;
    }

    public function setFirstSlot(DateTimeImmutable $firstSlot): TimeSlotFrameDto
    {
        $this->firstSlot = $firstSlot;
        return $this;
    }

    public function getLastSlot(): DateTimeImmutable
    {
        return $this->lastSlot;
    }

    public function setLastSlot(DateTimeImmutable $lastSlot): TimeSlotFrameDto
    {
        $this->lastSlot = $lastSlot;
        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): TimeSlotFrameDto
    {
        $this->interval = $interval;
        return $this;
    }

    public function getFromHour(): int
    {
        return (int) $this->firstSlot->format('H');
    }

    public function getToHour(): int
    {
        return (int) $this->lastSlot->format('H');
    }

    public function getFromMinute(): int
    {
        return (int) $this->firstSlot->format('i');
    }

    public function getToMinute(): int
    {
        return (int) $this->lastSlot->format('i');
    }
}
