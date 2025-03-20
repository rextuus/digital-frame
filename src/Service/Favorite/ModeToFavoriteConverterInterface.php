<?php

namespace App\Service\Favorite;

use App\Entity\Favorite;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\FrameConfiguration\DisplayMode;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ModeToFavoriteConverterInterface::SERVICE_TAG)]
interface ModeToFavoriteConverterInterface
{
    public const SERVICE_TAG = 'mode_to_favorite_converter';

    public function supports(DisplayMode $mode): bool;
    public function getLastImageDto(): LastImageDto;

    /**
     * @throws ConverterNotSupportsException
     */
    public function convertToFavoriteEntity(FavoriteConvertable $favoriteConvertable): Favorite;
}
