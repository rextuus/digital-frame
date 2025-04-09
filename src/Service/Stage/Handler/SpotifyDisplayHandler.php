<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Spotify\SpotifyService;
use App\Service\Stage\ImageDisplayHandlerInterface;

readonly class SpotifyDisplayHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private SpotifyService $spotifyService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::SPOTIFY;
    }

    public function initialize(): string
    {
        return $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test';
    }

    public function refresh(): string
    {
        $this->configurationService->setCurrentDisplayedImage(null, DisplayMode::SPOTIFY);
        $this->configurationService->setWaitForModeSwitch(false);

        return $this->spotifyService->getImageUrlOfCurrentlyPlayingSong()['url'] ?? 'test';
    }
}
