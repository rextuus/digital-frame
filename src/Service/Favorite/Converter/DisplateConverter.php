<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\DisplateImage;
use App\Entity\Favorite;
use App\Service\Displate\DisplateImageService;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Exception;

class DisplateConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::DISPLATE;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly DisplateImageService $displateImageService
    ) {
    }

    public function supports(DisplayMode $mode): bool
    {
        return $mode === $this->mode;
    }

    /**
     * @throws Exception
     */
    public function getLastImageDto(): LastImageDto
    {
        $dto = new LastImageDto();

        $lastArtwork = $this->getLastArtwork();

        if ($lastArtwork === null) {
            return $dto;
        }

        $dto->setFound(true);
        $dto->setUrl($lastArtwork->getUrl());
        $dto->setArtist($lastArtwork->getName());
        $dto->setTitle($lastArtwork->getName());

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {
        if (!$favoriteConvertable instanceof DisplateImage) {
            $favoriteConvertable = $this->getLastArtwork();

            $class = get_class($favoriteConvertable);
            if ($favoriteConvertable === null) {
                throw new ConverterNotSupportsException(
                    'UnsplashConverter expects an UnsplashImage, got ' . $class
                );
            }
        }

        $favorite = new Favorite();
        $favorite->setDisplayMode($this->mode);
        $favorite->setEntityId($favoriteConvertable->getId());
        $favorite->setTitle($favoriteConvertable->getName());
        $favorite->setArtist($favoriteConvertable->getName());
        $favorite->setDisplayUrl($favoriteConvertable->getUrl());

        return $favorite;
    }

    private function getLastArtwork(): ?DisplateImage
    {
        $lastDisplayedArtworkId = $this->configurationService->getCurrentlyDisplayedImageId();
        $lastArtwork = null;
        if ($lastDisplayedArtworkId !== null) {
            $lastArtwork = $this->displateImageService->getArtworkById($lastDisplayedArtworkId);
        }
        return $lastArtwork;
    }
}
