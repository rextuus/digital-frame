<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Entity\Favorite;
use App\Service\Artsy\ArtsyService;
use App\Service\Favorite\LastImageDto;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Stage\ImageDisplayHandlerInterface;

readonly class ArtsyDisplayHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private ArtsyService $artsyService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::ARTSY;
    }

    public function initialize(): string
    {
        return $this->artsyService->getCurrentArtwork()->getBestResolutionUrl();
    }

    public function refresh(): string
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

        $this->configurationService->setCurrentDisplayedImage($artsyImage->getId(), DisplayMode::ARTSY);
        $this->configurationService->setNext(false);
        $this->configurationService->setWaitForModeSwitch(false);

        return $artsyImage->getBestResolutionUrl();
    }
}
