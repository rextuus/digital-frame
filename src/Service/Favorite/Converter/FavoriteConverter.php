<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\Favorite;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\FavoriteService;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Exception;

class FavoriteConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::FAVORITE;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly FavoriteService $favoriteService
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
        $dto->setUrl($lastArtwork->getDisplayUrl());
        $dto->setArtist($lastArtwork->getArtist());
        $dto->setTitle($lastArtwork->getTitle());

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {

        if (!$favoriteConvertable instanceof Favorite) {
            $favoriteConvertable = $this->getLastArtwork();

            $class = get_class($favoriteConvertable);
            if ($favoriteConvertable === null) {
                throw new ConverterNotSupportsException(
                    'UnsplashConverter expects an UnsplashImage, got ' . $class
                );
            }
        }

        return $favoriteConvertable;
    }

    private function getLastArtwork(): ?Favorite
    {
        $lastFavoriteId = $this->configurationService->getCurrentlyDisplayedImageId();
        $lastArtwork = null;
        if ($lastFavoriteId !== null) {
            $lastArtwork = $this->favoriteService->getFavorite($lastFavoriteId);
        }

        return $lastArtwork;
    }
}
