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

        $url = $metaData['url'] ?? 'Spotify currently not running';
        $found = array_key_exists('url', $metaData);
        $artist = $metaData['artist'] ?? 'Spotify currently not running';
        $name = $metaData['name'] ?? 'Spotify currently not running';
        $album = $metaData['album'] ?? '';

        $dto->setUrl($url);
        $dto->setArtist($artist);
        $dto->setTitle($name . ' (' . $album . ')');
        $dto->setFound($found);

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {
        $metaData = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();

        if (!array_key_exists('url', $metaData)) {
            throw new ConverterNotSupportsException(
                'Cant fetch necessary information from spotify'
            );
        }

        $favorite = new Favorite();
        $favorite->setDisplayMode($this->mode);
        $favorite->setEntityId(null);
        $favorite->setTitle($metaData['name'] . ' (' . $metaData['album'] . ')');
        $favorite->setArtist($metaData['artist']);
        $favorite->setDisplayUrl($metaData['url']);

        return $favorite;
    }
}
