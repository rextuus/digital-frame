<?php

namespace App\Twig\Components;

use App\Service\FrameConfiguration\BackgroundStyle;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\FrameConfiguration\ImageStyle;
use App\Service\Nasa\NasaService;
use App\Service\Spotify\SpotifyService;
use App\Service\Stage\ImageDisplayHandlerProvider;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class StageComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $imageUrl = null;

    #[LiveProp(writable: true)]
    public ?string $nasaText = null;

    #[LiveProp(writable: true)]
    public DisplayMode $currentMode = DisplayMode::UNSPLASH;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly SpotifyService $spotifyService,
        private readonly NasaService $nasaService,
        private readonly ImageDisplayHandlerProvider $displayHandlerProvider,
    ) {
    }

    public function getImageUrl(): string
    {
        $this->currentMode = $this->configurationService->getMode();

        // init imageUrl first time page is loaded
        if ($this->imageUrl === null) {
            $handler = $this->displayHandlerProvider->getHandlerForMode($this->currentMode);
            $this->imageUrl = $handler->initialize();
        }

        return $this->imageUrl;
    }

    #[LiveAction]
    public function refresh(): void
    {
        // switch mode or next
        if ($this->configurationService->isWaitingForModeSwitch() || $this->configurationService->isNext()) {
            if ($this->configurationService->isWaitingForModeSwitch()) {
                $this->currentMode = $this->configurationService->getMode();
            }

            $handler = $this->displayHandlerProvider->getHandlerForMode($this->currentMode);
            $this->imageUrl = $handler->refresh();
        }

        // set current spotifyUrl
        if ($this->currentMode === DisplayMode::SPOTIFY) {
            $this->imageUrl = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test';
        }
    }

    public function getBackgroundStyle(): string
    {   //style="background-image: url('{{ this.imageUrl }}');"
        $backgroundConfig = $this->configurationService->getBackgroundConfigurationForCurrentMode();
        return match ($backgroundConfig->getStyle()) {
            BackgroundStyle::COLOR => sprintf('style="background-color: %s"', $backgroundConfig->getColor()),
            default => sprintf('style="background-image: url(\'%s\')"', $this->getImageUrl()),
        };
    }

    public function getClearModeClass(): string
    {
        $backgroundConfig = $this->configurationService->getBackgroundConfigurationForCurrentMode();

        return match ($backgroundConfig->getStyle()) {
            BackgroundStyle::CLEAR => 'clear-mode',
            default => '',
        };
    }

    public function getImageStyleClass(): string
    {
        $backgroundConfig = $this->configurationService->getBackgroundConfigurationForCurrentMode();

        return match ($backgroundConfig->getImageStyle()) {
            ImageStyle::SCREEN_WIDTH => 'maximized',
            ImageStyle::CUSTOM_HEIGHT => 'no-limits',
            default => '',
        };
    }

    public function getMinHeight(): string
    {
        $backgroundConfig = $this->configurationService->getBackgroundConfigurationForCurrentMode();
        $customHeight = $backgroundConfig->getCustomHeight();
        if ($customHeight === null) {
            $customHeight = 1900;
        }

        return match ($backgroundConfig->getImageStyle()) {
            ImageStyle::CUSTOM_HEIGHT => 'style="min-height: '.$customHeight.'px;"',
            default => '',
        };
    }

    public function getNasaText(): ?string
    {
        if ($this->currentMode === DisplayMode::NASA && $this->nasaText === null) {
            $this->nasaText = $this->nasaService->getImageOfTheDay()->getExplanation();
        }

        if ($this->currentMode !== DisplayMode::NASA) {
            $this->nasaText = null;
        }

        return $this->nasaText;
    }
}
