<?php

namespace App\Entity;

use App\Repository\FrameConfigurationRepository;
use App\Service\FrameConfiguration\DisplayMode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use \App\Service\FrameConfiguration\DisplayState;

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

    #[ORM\Column(options: ['default' => false])]
    private bool $shouldSpotifyInterrupt = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $shouldGreetingInterrupt = false;

    #[ORM\Column(
        type: 'integer',
        nullable: false,
        enumType: DisplayMode::class,
        options: ['default' => DisplayMode::UNSPLASH]
    )]
    private DisplayMode $modeBeforeInterruption = DisplayMode::UNSPLASH;

    #[ORM\Column(
        type: 'string',
        nullable: false,
        enumType: DisplayState::class,
        options: ['default' => DisplayState::OFF]
    )]
    private DisplayState $displayState = DisplayState::OFF;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $forcedSpotifyInterruption = null;

    /**
     * @var Collection<int, BackgroundConfiguration>
     */
    #[ORM\OneToMany(mappedBy: 'configuration', targetEntity: BackgroundConfiguration::class, orphanRemoval: true)]
    private Collection $backgroundConfigurations;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?SearchTag $currentTag = null;

    public function __construct()
    {
        $this->backgroundConfigurations = new ArrayCollection();
    }

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

    public function isShouldSpotifyInterrupt(): ?bool
    {
        return $this->shouldSpotifyInterrupt;
    }

    public function setShouldSpotifyInterrupt(bool $shouldSpotifyInterrupt): static
    {
        $this->shouldSpotifyInterrupt = $shouldSpotifyInterrupt;

        return $this;
    }

    public function isShouldGreetingInterrupt(): ?bool
    {
        return $this->shouldGreetingInterrupt;
    }

    public function setShouldGreetingInterrupt(bool $shouldGreetingInterrupt): static
    {
        $this->shouldGreetingInterrupt = $shouldGreetingInterrupt;

        return $this;
    }

    public function getModeBeforeInterruption(): DisplayMode
    {
        return $this->modeBeforeInterruption;
    }

    public function setModeBeforeInterruption(DisplayMode $modeBeforeInterruption): FrameConfiguration
    {
        $this->modeBeforeInterruption = $modeBeforeInterruption;
        return $this;
    }

    public function getDisplayState(): DisplayState
    {
        return $this->displayState;
    }

    public function setDisplayState(DisplayState $displayState): static
    {
        $this->displayState = $displayState;

        return $this;
    }

    public function getForcedSpotifyInterruption(): ?\DateTimeInterface
    {
        return $this->forcedSpotifyInterruption;
    }

    public function setForcedSpotifyInterruption(?\DateTimeInterface $forcedSpotifyInterruption): static
    {
        $this->forcedSpotifyInterruption = $forcedSpotifyInterruption;

        return $this;
    }

    /**
     * @return Collection<int, BackgroundConfiguration>
     */
    public function getBackgroundConfigurations(): Collection
    {
        return $this->backgroundConfigurations;
    }

    public function addBackgroundConfiguration(BackgroundConfiguration $backgroundConfiguration): static
    {
        if (!$this->backgroundConfigurations->contains($backgroundConfiguration)) {
            $this->backgroundConfigurations->add($backgroundConfiguration);
            $backgroundConfiguration->setConfiguration($this);
        }

        return $this;
    }

    public function removeBackgroundConfiguration(BackgroundConfiguration $backgroundConfiguration): static
    {
        if ($this->backgroundConfigurations->removeElement($backgroundConfiguration)) {
            // set the owning side to null (unless already changed)
            if ($backgroundConfiguration->getConfiguration() === $this) {
                $backgroundConfiguration->setConfiguration(null);
            }
        }

        return $this;
    }

    public function getCurrentTag(): ?SearchTag
    {
        return $this->currentTag;
    }

    public function setCurrentTag(?SearchTag $currentTag): static
    {
        $this->currentTag = $currentTag;

        return $this;
    }
}
