<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Service\Favorite\FavoriteService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Stage\ImageDisplayHandlerInterface;

readonly class FavoriteHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private FavoriteService $favoriteService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::FAVORITE;
    }

    public function initialize(): string
    {
        $favorites = $this->favoriteService->getDefaultFavoriteList()->getFavorites();
        $randId = rand(0, $favorites->count() - 1);

        return $favorites->get($randId)->getDisplayUrl();
    }

    public function refresh(): string
    {
        $favorite = null;

        // check if it's a forced display call from gallery
        $imageId = $this->configurationService->getNextImageId();
        if ($imageId !== null) {
            $favorite = $this->favoriteService->getFavorite($imageId);
            $this->configurationService->setNextImageId(null);
        }

        // get next favorite from list
        if ($favorite === null) {
            $favorite = $this->favoriteService->getNextForCurrentFavoriteList(true);
        }

        $this->configurationService->setCurrentDisplayedImage($favorite->getId(), DisplayMode::FAVORITE);
        $this->configurationService->setNext(false);
        $this->configurationService->setWaitForModeSwitch(false);

        return $favorite->getDisplayUrl();
    }
}
