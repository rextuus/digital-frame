<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Service\Displate\DisplateImageService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Stage\Exception\DisplateNoImagesForTagException;
use App\Service\Stage\ImageDisplayHandlerInterface;

readonly class DisplateDisplayHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private DisplateImageService $displateImageService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::DISPLATE;
    }

    public function initialize(): string
    {
        return $this->displateImageService->getRandomImage()->getUrl();
    }

    public function refresh(): string
    {
        return $this->switchToDisplate();
    }

    private function switchToDisplate(): string
    {
        $displateImage = null;

        // check if it's a forced display call from gallery
        $imageId = $this->configurationService->getNextImageId();
        if ($imageId !== null) {
            $displateImage = $this->displateImageService->getArtworkById($imageId);
            $this->configurationService->setNextImageId(null);
        }

        if ($displateImage === null) {
            try {
                $displateImage = $this->displateImageService->getNextImageForCurrentTag();
            } catch (DisplateNoImagesForTagException $e) {
                $displateImage = $this->displateImageService->getRandomImage();
            }
        }

        $this->configurationService->setCurrentDisplayedImage($displateImage->getId(), DisplayMode::DISPLATE);
        $this->configurationService->setCurrentTag($displateImage->getSearchTag());
        $this->configurationService->setWaitForModeSwitch(false);
        $this->configurationService->setNext(false);

        return $displateImage->getUrl();
    }
}
