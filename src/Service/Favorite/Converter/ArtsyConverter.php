<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\ArtsyImage;
use App\Entity\Favorite;
use App\Service\Artsy\ArtsyService;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Exception;

class ArtsyConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::ARTSY;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly ArtsyService $artsyService,
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
        $dto->setUrl($lastArtwork->getMediumResolutionUrl());
        $dto->setArtist($lastArtwork->getArtist());
        $dto->setTitle($lastArtwork->getName());

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {
        if (!$favoriteConvertable instanceof ArtsyImage) {
            $favoriteConvertable = $this->getLastArtwork();

            $class = get_class($favoriteConvertable);
            if ($favoriteConvertable === null) {
                throw new ConverterNotSupportsException(
                    'ArtsyConverter expects an ArtsyImage, got ' . $class
                );
            }
        }

        $favorite = new Favorite();
        $favorite->setDisplayMode($this->mode);
        $favorite->setEntityId($favoriteConvertable->getId());
        $favorite->setTitle($favoriteConvertable->getName());
        $favorite->setArtist($favoriteConvertable->getArtist());
        $favorite->setDisplayUrl($favoriteConvertable->getBestResolutionUrl());

        return $favorite;
    }

    private function getLastArtwork(): ?ArtsyImage
    {
        $lastDisplayedArtworkId = $this->configurationService->getCurrentlyDisplayedImageId();
        $lastArtwork = null;
        if ($lastDisplayedArtworkId !== null) {
            $lastArtwork = $this->artsyService->getArtworkById($lastDisplayedArtworkId);
        }
        return $lastArtwork;
    }
}
