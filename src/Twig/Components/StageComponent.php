<?php

namespace App\Twig\Components;

use App\Controller\SpotifyController;
use App\Service\Artsy\ArtsyService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Nasa\NasaService;
use App\Service\Spotify\SpotifyService;
use App\Service\Unsplash\UnsplashImageService;
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
    public DisplayMode $currentMode = DisplayMode::UNSPLASH;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly UnsplashImageService $unsplashImageService,
        private readonly ArtsyService $artsyService,
        private readonly SpotifyService $spotifyService,
        private readonly NasaService $nasaService,
    ) {
    }

    public function getImageUrl(): string
    {
        $this->currentMode = $this->configurationService->getMode();

        // init imageUrl first time page is loaded
        if ($this->imageUrl === null) {
            $this->imageUrl = match ($this->currentMode) {
                DisplayMode::UNSPLASH => $this->unsplashImageService
                    ->getNextRandomImage($this->configurationService->getCurrentTag())
                    ->getUrl(),
                DisplayMode::ARTSY => $this->artsyService->getCurrentArtwork()->getBestResolutionUrl(),
                DisplayMode::SPOTIFY => $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test',
                DisplayMode::NASA => $this->nasaService->getImageOfTheDay()->getUrl(),
                default => null,
            };
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

            match ($this->currentMode) {
                DisplayMode::UNSPLASH => $this->nextUnsplashImage(),
                DisplayMode::ARTSY => $this->nextArtsyImage(),
                DisplayMode::SPOTIFY => $this->switchToSpotify(),
                DisplayMode::NASA => $this->switchToNasa(),
                default => null,
            };
        }

        // set current spotifyUrl
        if ($this->currentMode === DisplayMode::SPOTIFY) {
            $this->imageUrl = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test';
        }
    }

    public function getBackgroundStyle(): string
    {   //style="background-image: url('{{ this.imageUrl }}');"
        $color = $this->configurationService->getBackgroundColorForCurrentMode();
        if ($color !== FrameConfigurationService::COLOR_BLUR) {
            return sprintf('style="background-color: %s"', $color);
        }

        return sprintf('style="background-image: url(\'%s\')"', $this->getImageUrl());
    }

    private function nextUnsplashImage(): void
    {
        $currentTag = $this->configurationService->getCurrentTag();
        $unsplashImage = $this->unsplashImageService->getNextRandomImage($currentTag);
        $this->imageUrl = $unsplashImage->getUrl();

        $this->configurationService->setNext(false);
        $this->configurationService->setWaitForModeSwitch(false);

        $this->configurationService->setCurrentDisplayedImage($unsplashImage->getId(), DisplayMode::UNSPLASH);
    }

    private function nextArtsyImage(): void
    {
        $artsyImage = null;

        // check if it's a forced display call from gallery
        $imageId = $this->configurationService->getNextImageId();
        if ($imageId !== null) {
            $artsyImage = $this->artsyService->getArtworkById($imageId);
            $this->configurationService->setNextImageId(null);
        }

        if ($artsyImage === null) {
            $artsyImage = $this->artsyService->getCurrentArtWork();
        }

        $this->imageUrl = $artsyImage->getBestResolutionUrl();
        $this->configurationService->setCurrentDisplayedImage($artsyImage->getId(), DisplayMode::ARTSY);
        $this->configurationService->setNext(false);
        $this->configurationService->setWaitForModeSwitch(false);
    }

    private function switchToSpotify(): void
    {
        $this->configurationService->setCurrentDisplayedImage(null, DisplayMode::SPOTIFY);
        $this->configurationService->setWaitForModeSwitch(false);
        $this->imageUrl = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test';
    }

    private function switchToNasa(): void
    {
        $this->configurationService->setCurrentDisplayedImage(null, DisplayMode::NASA);
        $this->configurationService->setWaitForModeSwitch(false);

        $imageOfTheDay = $this->nasaService->getImageOfTheDay();
        $this->imageUrl = $imageOfTheDay->getUrl();
    }
}
