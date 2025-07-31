<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Entity\Favorite;
use App\Repository\SearchTagRepository;
use App\Service\Favorite\LastImageDto;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Stage\Exception\UnsplashNoImagesForTagException;
use App\Service\Stage\ImageDisplayHandlerInterface;
use App\Service\Unsplash\UnsplashImageService;

readonly class UnsplashDisplayHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private UnsplashImageService $unsplashImageService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::UNSPLASH;
    }

    public function initialize(): string
    {
        $currentTag = $this->configurationService->getCurrentTag();

        try {
            $unsplashImage = $this->unsplashImageService->getNextRandomImage($currentTag);
        } catch (UnsplashNoImagesForTagException $e) {

            $defaultTag = $this->unsplashImageService->getDefaultSearchTag();
            $unsplashImage = $this->unsplashImageService->getNextRandomImage($defaultTag);

            $this->configurationService->setCurrentTag($defaultTag);
        }
        return $unsplashImage->getUrl();
    }

    public function refresh(): string
    {
        $unsplashImage = null;

        // check if it's a forced display call from gallery
        $imageId = $this->configurationService->getNextImageId();
        if ($imageId !== null) {
            $unsplashImage = $this->unsplashImageService->getImageById($imageId);
            $this->configurationService->setNextImageId(null);
        }

        if ($unsplashImage === null) {
            $currentTag = $this->configurationService->getCurrentTag();
            try {
                $unsplashImage = $this->unsplashImageService->getNextRandomImage($currentTag);
            } catch (UnsplashNoImagesForTagException $e) {

                $defaultTag = $this->unsplashImageService->getDefaultSearchTag();
                $unsplashImage = $this->unsplashImageService->getNextRandomImage($defaultTag);

                $this->configurationService->setCurrentTag($defaultTag);
            }
        }

        $this->configurationService->setNext(false);
        $this->configurationService->setWaitForModeSwitch(false);

        $this->configurationService->setCurrentDisplayedImage($unsplashImage->getId(), DisplayMode::UNSPLASH);

        return $unsplashImage->getUrl();
    }
}
