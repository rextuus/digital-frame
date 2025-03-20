<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\Favorite;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\Spotify\SpotifyService;
use App\Service\Spotify\SpotifyToFavoriteDto;

class SpotifyConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::SPOTIFY;

    public function __construct(private readonly SpotifyService $spotifyService)
    {
    }

    public function supports(DisplayMode $mode): bool
    {
        return $mode === $this->mode;
    }

    public function getLastImageDto(): LastImageDto
    {
        $dto = new LastImageDto();
        $metaData = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();
        $dto->setUrl($metaData['url']);
        $dto->setArtist($metaData['artist']);
        $dto->setTitle($metaData['name'] . ' (' . $metaData['album'] . ')');

        return $dto;
    }

    public function convertToFavoriteEntity(FavoriteConvertable $favoriteConvertable): Favorite
    {
        if (!$favoriteConvertable instanceof SpotifyToFavoriteDto) {
            throw new ConverterNotSupportsException(
                'SpotifyConverter expects an SpotifyToFavoriteDto, got ' . get_class($favoriteConvertable)
            );
        }

        $metaData = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();

        $favorite = new Favorite();
        $favorite->setDisplayMode($this->mode);
        $favorite->setEntityId(null);
        $favorite->setTitle($metaData['name'] . ' (' . $metaData['album'] . ')');
        $favorite->setArtist($metaData['artist']);
        $favorite->setDisplayUrl($metaData['url']);

        return $favorite;
    }
}
