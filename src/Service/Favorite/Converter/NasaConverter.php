<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\Favorite;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\Nasa\NasaService;

class NasaConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::NASA;

    public function __construct(private readonly NasaService $nasaService)
    {
    }

    public function supports(DisplayMode $mode): bool
    {
        return $mode === $this->mode;
    }

    public function getLastImageDto(): LastImageDto
    {
        $dto = new LastImageDto();
        $metaData = $this->nasaService->getImageOfTheDay();

        $url = $metaData->getUrl();
        $found = $metaData->getUrl() !== 'error';

        $dto->setUrl($url);
        $dto->setArtist('NASA');
        $dto->setTitle($metaData->getTitle());
        $dto->setFound($found);

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {
        $metaData = $this->nasaService->getImageOfTheDay();

        $favorite = new Favorite();
        $favorite->setDisplayMode($this->mode);
        $favorite->setEntityId(null);
        $favorite->setTitle($metaData->getTitle());
        $favorite->setArtist('NASA');
        $favorite->setDisplayUrl($metaData->getUrl());

        return $favorite;
    }
}
