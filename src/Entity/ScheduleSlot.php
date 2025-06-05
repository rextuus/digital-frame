<?php

namespace App\Entity;

use App\Repository\ScheduleSlotRepository;
use App\Service\Scheduling\ScheduleAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleSlotRepository::class)]
class ScheduleSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: ScheduleAction::class)]
    private ?ScheduleAction $scheduleAction = null;

    #[ORM\ManyToOne(inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScheduleConfiguration $scheduleConfiguration = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $shouldTrigger = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $finish = null;

    #[ORM\Column(length: 255)]
    private ?string $frameIdentifier = null;

    #[ORM\Column]
    private ?int $interval = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheduleAction(): ?ScheduleAction
    {
        return $this->scheduleAction;
    }

    public function setScheduleAction(ScheduleAction $scheduleAction): static
    {
        $this->scheduleAction = $scheduleAction;

        return $this;
    }

    public function getScheduleConfiguration(): ?ScheduleConfiguration
    {
        return $this->scheduleConfiguration;
    }

    public function setScheduleConfiguration(?ScheduleConfiguration $scheduleConfiguration): static
    {
        $this->scheduleConfiguration = $scheduleConfiguration;

        return $this;
    }

    public function isShouldTrigger(): ?bool
    {
        return $this->shouldTrigger;
    }

    public function setShouldTrigger(bool $shouldTrigger): static
    {
        $this->shouldTrigger = $shouldTrigger;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getFinish(): ?\DateTimeInterface
    {
        return $this->finish;
    }

    public function setFinish(\DateTimeInterface $finish): static
    {
        $this->finish = $finish;

        return $this;
    }

    public function getFrameIdentifier(): ?string
    {
        return $this->frameIdentifier;
    }

    public function setFrameIdentifier(string $frameIdentifier): static
    {
        $this->frameIdentifier = $frameIdentifier;

        return $this;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): static
    {
        $this->interval = $interval;

        return $this;
    }
}
