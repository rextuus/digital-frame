<?php

namespace App\Entity;

use App\Repository\FrameConfigurationRepository;
use App\Service\FrameConfiguration\DisplayMode;
use Doccheck\Crm\Application\Domain\Order\OrderType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FrameConfigurationRepository::class)]
class FrameConfiguration
{
    public const MODE_UNSPLASH = 1;
    public const MODE_SPOTIFY = 2;

    public const MODE_GREETING = 3;
    public const DEFAULT_DISPLAY_TIME = 30;
    public const DEFAULT_DOWN_TIME = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: false, enumType: DisplayMode::class)]
    private DisplayMode $mode = DisplayMode::UNSPLASH;

    #[ORM\Column]
    private bool $next = false;

    #[ORM\Column(length: 255)]
    private ?string $currentTag = null;

    #[ORM\Column]
    private ?int $greetingDisplayTime = null;

    #[ORM\Column]
    private ?int $shutDownTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $nextImageId = null;

    #[ORM\Column(nullable: true)]
    private ?int $currentlyDisplayedImageId = null;

    #[ORM\Column(type: 'integer', nullable: false, enumType: DisplayMode::class)]
    private DisplayMode $currentlyDisplayedMode = DisplayMode::UNSPLASH;

    // this is flag which is set to true in the configController and should be reset if stage switched the mode
    #[ORM\Column(options: ['default' => false])]
    private bool $waitForModeSwitch = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMode(): DisplayMode
    {
        return $this->mode;
    }

    public function setMode(DisplayMode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function isNext(): bool
    {
        return $this->next;
    }

    public function setNext(bool $next): self
    {
        $this->next = $next;

        return $this;
    }

    public function getCurrentTag(): ?string
    {
        return $this->currentTag;
    }

    public function setCurrentTag(string $currentTag): self
    {
        $this->currentTag = $currentTag;

        return $this;
    }

    public function getGreetingDisplayTime(): ?int
    {
        return $this->greetingDisplayTime;
    }

    public function setGreetingDisplayTime(int $greetingDisplayTime): static
    {
        $this->greetingDisplayTime = $greetingDisplayTime;

        return $this;
    }

    public function getShutDownTime(): ?int
    {
        return $this->shutDownTime;
    }

    public function setShutDownTime(int $shutDownTime): static
    {
        $this->shutDownTime = $shutDownTime;

        return $this;
    }

    public function getNextImageId(): ?int
    {
        return $this->nextImageId;
    }

    public function setNextImageId(?int $nextImageId): static
    {
        $this->nextImageId = $nextImageId;

        return $this;
    }

    public function getCurrentlyDisplayedImageId(): ?int
    {
        return $this->currentlyDisplayedImageId;
    }

    public function setCurrentlyDisplayedImageId(?int $currentlyDisplayedImageId): static
    {
        $this->currentlyDisplayedImageId = $currentlyDisplayedImageId;

        return $this;
    }

    public function getCurrentlyDisplayedMode(): DisplayMode
    {
        return $this->currentlyDisplayedMode;
    }

    public function setCurrentlyDisplayedMode(DisplayMode $currentlyDisplayedMode): FrameConfiguration
    {
        $this->currentlyDisplayedMode = $currentlyDisplayedMode;
        return $this;
    }

    public function isWaitForModeSwitch(): bool
    {
        return $this->waitForModeSwitch;
    }

    public function setWaitForModeSwitch(bool $waitForModeSwitch): static
    {
        $this->waitForModeSwitch = $waitForModeSwitch;

        return $this;
    }
}
