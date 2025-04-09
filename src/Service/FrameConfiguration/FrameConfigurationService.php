<?php

namespace App\Service\FrameConfiguration;

use App\Entity\BackgroundConfiguration;
use App\Entity\FrameConfiguration;
use App\Entity\SearchTag;
use App\Repository\BackgroundConfigurationRepository;
use App\Repository\FrameConfigurationRepository;
use App\Service\FrameConfiguration\Form\ConfigurationUpdateData;
use App\Service\Unsplash\UnsplashImageService;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class FrameConfigurationService
{
    public function __construct(
        private FrameConfigurationRepository $repository,
        private FrameConfigurationFactory $factory,
        private EntityManagerInterface $entityManager,
        private UnsplashImageService $unsplashImageService,
        private BackgroundConfigurationRepository $backgroundConfigurationRepository,
    ) {
    }

    public function createConfiguration(DisplayMode $mode): FrameConfiguration
    {
        $defaultTag = $this->unsplashImageService->createNewTag(UnsplashImageService::TAG_RANDOM);

        $configuration = $this->factory->createConfiguration($mode);
        $configuration->setNext(false);
        $configuration->setCurrentTag($defaultTag);
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

    public function setCurrentTag(SearchTag $tag): void
    {
        $configuration = $this->getConfiguration();

        $configuration->setCurrentTag($tag);
        $this->repository->save($configuration, true);
    }

    public function getCurrentTag(): SearchTag
    {
        $configuration = $this->getConfiguration();

        $tag = $configuration->getCurrentTag();
        if ($tag === null) {
            $tag = $this->unsplashImageService->createNewTag(UnsplashImageService::TAG_RANDOM);
            $configuration->setCurrentTag($tag);
            $this->repository->save($configuration, true);
        }

        return $tag;
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
        $configuration->setCurrentlyDisplayedMode(DisplayMode::SPOTIFY);
        $configuration->setMode(DisplayMode::SPOTIFY);
        $configuration->setWaitForModeSwitch(true);

        $this->repository->save($configuration, true);
    }

    public function releaseSpotifyInterruption(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setForcedSpotifyInterruption(null);
        $configuration->setMode($configuration->getModeBeforeInterruption());
        $configuration->setWaitForModeSwitch(true);

        $this->repository->save($configuration, true);
    }

    public function setDisplayStateOn(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setDisplayState(DisplayState::ON);
        $this->repository->save($configuration, true);
    }

    public function setDisplayStateOff(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->setDisplayState(DisplayState::OFF);
        $this->repository->save($configuration, true);
    }

    /**
     * @return array<BackgroundConfiguration>
     */
    public function getBackGroundColors(): array
    {
        $configuration = $this->getConfiguration();
        if ($configuration->getBackgroundConfigurations()->count() === 0) {
            foreach (DisplayMode::cases() as $displayMode){
                $backGroundConfig = new BackgroundConfiguration();
                $backGroundConfig->setMode($displayMode);
                $backGroundConfig->setColor(null);
                $backGroundConfig->setStyle(BackgroundStyle::BLUR);
                $backGroundConfig->setImageStyle(ImageStyle::ORIGINAL);
                $backGroundConfig->setConfiguration($configuration);
                $this->entityManager->persist($backGroundConfig);

                $configuration->addBackgroundConfiguration($backGroundConfig);
                $this->entityManager->persist($configuration);
            }
            $this->entityManager->flush();
        }

        return $configuration->getBackgroundConfigurations()->toArray();
    }

    /**
     * @throws Exception
     */
    public function getBackgroundColorForCurrentMode(): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getBackgroundConfigurations() as $backgroundConfiguration) {
            if ($backgroundConfiguration->getMode() === $configuration->getMode()) {
                return $backgroundConfiguration->getColor();
            }
        }

        throw new Exception('No background color found for current mode');
    }

    /**
     * @throws Exception
     */
    public function getBackgroundConfigurationForCurrentMode(): BackgroundConfiguration
    {
        $configuration = $this->getConfiguration();
        foreach ($this->getBackGroundColors() as $backgroundConfiguration) {
            if ($backgroundConfiguration->getMode() === $configuration->getMode()) {
                return $backgroundConfiguration;
            }
        }

        throw new Exception('No background color found for current mode');
    }

    public function setBackgroundStyleForCurrentMode(BackgroundStyle $style): void
    {
        $configuration = $this->getConfiguration();

        $backgroundConfig = $this->backgroundConfigurationRepository->findOneBy(['mode' => $configuration->getMode()]);
        $backgroundConfig->setStyle($style);
        $this->entityManager->persist($backgroundConfig);
        $this->entityManager->flush();
    }

    public function setBackgroundColorForCurrentMode(string $string): void
    {
        $configuration = $this->getConfiguration();

        $backgroundConfig = $this->backgroundConfigurationRepository->findOneBy(['mode' => $configuration->getMode()]);
        $backgroundConfig->setColor($string);
        $backgroundConfig->setStyle(BackgroundStyle::COLOR);
        $this->entityManager->persist($backgroundConfig);
        $this->entityManager->flush();
    }


    public function toggleImageStyleForCurrentMode(): void
    {
        $configuration = $this->getConfiguration();

        $backgroundConfig = $this->backgroundConfigurationRepository->findOneBy(['mode' => $configuration->getMode()]);
        $currentImageStyle = $backgroundConfig->getImageStyle();
        if ($currentImageStyle === ImageStyle::ORIGINAL) {
            $backgroundConfig->setImageStyle(ImageStyle::SCREEN_WIDTH);
        }else{
            $backgroundConfig->setImageStyle(ImageStyle::ORIGINAL);
        }

        $this->entityManager->persist($backgroundConfig);
        $this->entityManager->flush();
    }

    public function setImageStyleForCurrentMode(ImageStyle $style, ?int $height): void
    {
        $configuration = $this->getConfiguration();

        $backgroundConfig = $this->backgroundConfigurationRepository->findOneBy(['mode' => $configuration->getMode()]);
        $backgroundConfig->setImageStyle($style);
        if ($height !== null) {
            $backgroundConfig->setCustomHeight($height);
        }

        $this->entityManager->persist($backgroundConfig);
        $this->entityManager->flush();
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

        $backgroundConfig = $this->getBackgroundConfigurationForCurrentMode();
        $blur = $disabledClass;
        $clear = $disabledClass;
        $changeColor = $enabledClass;
        if ($backgroundConfig->getStyle() === BackgroundStyle::BLUR) {
            $blur = $enabledClass;
            $changeColor = $disabledClass;
        }
        if ($backgroundConfig->getStyle() === BackgroundStyle::CLEAR) {
            $clear = $enabledClass;
            $changeColor = $disabledClass;
        }
        $collection->addButton('blur', new ButtonState($blur));
        $collection->addButton('changeColor', new ButtonState($changeColor));
        $collection->addButton('clear', new ButtonState($clear));

        $maximized = $disabledClass;
        if ($backgroundConfig->getImageStyle() === ImageStyle::SCREEN_WIDTH){
            $maximized = $enabledClass;
        }
        $collection->addButton('maximize', new ButtonState($maximized));

        $customHeight = $disabledClass;
        if ($backgroundConfig->getImageStyle() === ImageStyle::CUSTOM_HEIGHT){
            $customHeight = $enabledClass;
        }
        $collection->addButton('customHeight', new ButtonState($customHeight));

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
        $collection->addButton(
            'displate',
            new ButtonState($currentMode === DisplayMode::DISPLATE ? $enabledClass : $disabledClass)
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
