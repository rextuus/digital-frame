<?php

namespace App\Service\FrameConfiguration;

use App\Entity\FrameConfiguration;
use App\Repository\FrameConfigurationRepository;
use App\Service\FrameConfiguration\Form\ConfigurationUpdateData;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

readonly class FrameConfigurationService
{
    public const COLOR_BLUR = 'blur';

    public function __construct(
        private FrameConfigurationRepository $repository,
        private FrameConfigurationFactory $factory,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createConfiguration(DisplayMode $mode): FrameConfiguration
    {
        $configuration = $this->factory->createConfiguration($mode);
        $configuration->setNext(false);
        $configuration->setCurrentTag('random');
        $configuration->setGreetingDisplayTime(FrameConfiguration::DEFAULT_DISPLAY_TIME);
        $configuration->setShutDownTime(FrameConfiguration::DEFAULT_DOWN_TIME);
        $this->repository->save($configuration, true);
        return $configuration;
    }

    public function getDefaultUpdateData(): ConfigurationUpdateData
    {
        $configuration = $this->repository->find(1);

        return (new ConfigurationUpdateData())->initFrom($configuration);
    }

    public function updateConfiguration(
        ?int $mode,
        bool $isNext = false,
        string $defaultGreetingTime = FrameConfiguration::DEFAULT_DISPLAY_TIME
    ) {
        $configuration = $this->getConfiguration();

        if (is_null($mode)) {
            $mode = $configuration->getMode();
        }
        $configuration->setMode($mode);
        $configuration->setNext($isNext);

        $this->repository->save($configuration, true);
    }

    public function update(ConfigurationUpdateData $data, FrameConfiguration $configuration = null): FrameConfiguration
    {
        if (is_null($configuration)) {
            $configuration = $this->getConfiguration();
        }

        $this->factory->mapData($configuration, $data);
        $this->repository->save($configuration, true);

        return $configuration;
    }

    public function getMode(): DisplayMode
    {
        $configuration = $this->getConfiguration();

        return $configuration->getMode();
    }

    public function setMode(DisplayMode $mode): void
    {
        $configuration = $this->getConfiguration();

        $configuration->setMode($mode);
        $this->repository->save($configuration, true);
    }

    public function isNext(): bool
    {
        $configuration = $this->getConfiguration();

        return $configuration->isNext();
    }

    public function setCurrentTag(string $tag): void
    {
        $configuration = $this->getConfiguration();

        $configuration->setCurrentTag($tag);
        $this->repository->save($configuration, true);
    }

    public function getCurrentTag(): string
    {
        $configuration = $this->getConfiguration();
        return $configuration->getCurrentTag();
    }

    public function getConfiguration(bool $forceRefresh = false): FrameConfiguration
    {
        $configuration = $this->repository->find(1);

        if (is_null($configuration)) {
            $configuration = $this->createConfiguration(DisplayMode::UNSPLASH);
        }

        if ($forceRefresh) {
            $this->entityManager->refresh($configuration); // Refresh from database
        }

        return $configuration;
    }

    public function getNextImageId(): ?int
    {
        $configuration = $this->getConfiguration();

        return $configuration->getNextImageId();
    }

    public function setNextImageId(?int $nextImageId): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setNextImageId($nextImageId);
        $this->repository->save($configuration, true);
    }

    public function setNext(bool $next): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setNext($next);
        $this->repository->save($configuration, true);
    }

    public function setCurrentDisplayedImage(?int $artworkId, DisplayMode $mode): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setCurrentlyDisplayedImageId($artworkId);
        $configuration->setCurrentlyDisplayedMode($mode);
        $this->repository->save($configuration, true);
    }

    public function getCurrentlyDisplayedImageId(): ?int
    {
        $configuration = $this->getConfiguration();

        return $configuration->getCurrentlyDisplayedImageId();
    }

    public function getCurrentlyDisplayedImageMode(): DisplayMode
    {
        $configuration = $this->getConfiguration();

        return $configuration->getCurrentlyDisplayedMode();
    }

    public function setWaitForModeSwitch(bool $shouldWait): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setWaitForModeSwitch($shouldWait);
        $this->repository->save($configuration, true);
    }

    public function isWaitingForModeSwitch(): bool
    {
        $configuration = $this->getConfiguration(true);

        return $configuration->isWaitForModeSwitch();
    }

    public function shouldSpotifyInterrupt(): bool
    {
        $configuration = $this->getConfiguration();

        return $configuration->isShouldSpotifyInterrupt();
    }

    public function toggleShouldSpotifyInterrupt(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setShouldSpotifyInterrupt(!$configuration->isShouldSpotifyInterrupt());
        $this->repository->save($configuration, true);
    }

    public function shouldGreetingInterrupt(): bool
    {
        $configuration = $this->getConfiguration();

        return $configuration->isShouldGreetingInterrupt();
    }

    public function toggleShouldGreetingInterrupt(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setShouldGreetingInterrupt(!$configuration->isShouldGreetingInterrupt());
        $this->repository->save($configuration, true);
    }

    public function getDisplayState(): DisplayState
    {
        $configuration = $this->getConfiguration();

        return $configuration->getDisplayState();
    }

    public function setDisplayState(DisplayState $state): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setDisplayState($state);
        $this->repository->save($configuration, true);
    }

    public function getForcedSpotifyInterruption(): ?DateTimeInterface
    {
        $configuration = $this->getConfiguration();

        return $configuration->getForcedSpotifyInterruption();
    }

    public function forceSpotifyInterruption(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setForcedSpotifyInterruption(new DateTime());
        $configuration->setModeBeforeInterruption($configuration->getMode());
        $configuration->setMode(DisplayMode::SPOTIFY);
        $this->repository->save($configuration, true);
    }

    public function releaseSpotifyInterruption(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setForcedSpotifyInterruption(null);
        $configuration->setMode($configuration->getModeBeforeInterruption());
        $this->repository->save($configuration, true);
    }

    /**
     * @return array<string, string>
     */
    public function getBackGroundColors(): array
    {
        $configuration = $this->getConfiguration();
        if ($configuration->getBackgroundColors() === []) {
            $defaults = [];
            foreach (DisplayMode::cases() as $displayMode){
                $defaults[$displayMode->value] = self::COLOR_BLUR;
            }

            $configuration->setBackgroundColors($defaults);
            $this->repository->save($configuration, true);

            return $defaults;
        }

        return $configuration->getBackgroundColors();
    }

    public function getBackgroundColorForCurrentMode(): string
    {
        $configuration = $this->getConfiguration();
        $backgroundColor = $configuration->getBackgroundColors();

        return $backgroundColor[$configuration->getMode()->value] ?? self::COLOR_BLUR;
    }

    public function setBackgroundColorForCurrentMode(string $string): void
    {
        $configuration = $this->getConfiguration();
        $backgroundColor = $configuration->getBackgroundColors();
        $backgroundColor[$configuration->getMode()->value] = $string;
        $configuration->setBackgroundColors($backgroundColor);
        $this->repository->save($configuration, true);
    }

    /**
     * @return ButtonStateCollection
     */
    public function getActiveButtonMap(): ButtonStateCollection
    {
        $configuration = $this->getConfiguration();
        $disabledClass = ButtonState::DISABLED_CLASS;
        $enabledClass = ButtonState::ENABLED_CLASS;

        $currentMode = $configuration->getMode();

        $collection = new ButtonStateCollection();

        $blur = $disabledClass;
        $changeColor = $enabledClass;
        if ($configuration->getBackgroundColors()[$currentMode->value] === self::COLOR_BLUR) {
            $blur = $enabledClass;
            $changeColor = $disabledClass;
        }
        $collection->addButton('blur', new ButtonState($blur));
        $collection->addButton('changeColor', new ButtonState($changeColor));

        $collection->addButton(
            'spotifyInterruption',
            new ButtonState($configuration->isShouldSpotifyInterrupt() ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'greetingInterruption',
            new ButtonState($configuration->isShouldGreetingInterrupt() ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'spotify',
            new ButtonState($currentMode === DisplayMode::SPOTIFY ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'unsplash',
            new ButtonState($currentMode === DisplayMode::UNSPLASH ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'greeting',
            new ButtonState($currentMode === DisplayMode::GREETING ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'artsy',
            new ButtonState($currentMode === DisplayMode::ARTSY ? $enabledClass : $disabledClass)
        );
        $collection->addButton(
            'nasa',
            new ButtonState($currentMode === DisplayMode::NASA ? $enabledClass : $disabledClass)
        );

        return $collection;

//        return [
//            'spotifyInterruption' => $configuration->isShouldSpotifyInterrupt() ? $enabledClass : $disabledClass,
//            'greetingInterruption' => $configuration->isShouldGreetingInterrupt() ? $enabledClass : $disabledClass,
//            'spotify' => $currentMode === DisplayMode::SPOTIFY ? $enabledClass : $disabledClass,
//            'unsplash' => $currentMode === DisplayMode::UNSPLASH ? $enabledClass : $disabledClass,
//            'greeting' => $currentMode === DisplayMode::GREETING ? $enabledClass : $disabledClass,
//            'artsy' => $currentMode === DisplayMode::ARTSY ? $enabledClass : $disabledClass,
//            'nasa' => $currentMode === DisplayMode::NASA ? $enabledClass : $disabledClass,
//            'blur' => $blur,
//            'changeColor' => $changeColor,
//        ];
    }
}
