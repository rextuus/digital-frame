<?php

declare(strict_types=1);

namespace App\Service\Favorite\Converter;

use App\Entity\Favorite;
use App\Entity\Greeting;
use App\Service\Favorite\Exception\ConverterNotSupportsException;
use App\Service\Favorite\FavoriteConvertable;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConverterInterface;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Greeting\GreetingService;
use Exception;

class GreetingConverter implements ModeToFavoriteConverterInterface
{
    private DisplayMode $mode = DisplayMode::GREETING;

    public function __construct(
        private readonly FrameConfigurationService $configurationService,
        private readonly GreetingService $greetingService,
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

        $lastGreeting = $this->getLastGreeting();

        $url = 'No greeting showed currently';
        $name = 'No greeting showed currently';
        if ($lastGreeting !== null) {
            $url = $lastGreeting->getCdnUrl();
            $name = $lastGreeting->getName();
            $dto->setFound(true);
        }

        $dto->setUrl($url);
        $dto->setArtist($name);
        $dto->setTitle($name);

        return $dto;
    }

    public function convertToFavoriteEntity(?FavoriteConvertable $favoriteConvertable = null): Favorite
    {
        if (!$favoriteConvertable instanceof Greeting) {
            $favoriteConvertable = $this->getLastGreeting();

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
        $favorite->setArtist($favoriteConvertable->getName());
        $favorite->setDisplayUrl($favoriteConvertable->getCdnUrl());

        return $favorite;
    }

    private function getLastGreeting(): ?Greeting
    {
        $lastDisplayedArtworkId = $this->configurationService->getCurrentlyDisplayedImageId();
        $lastGreeting = null;
        if ($lastDisplayedArtworkId !== null) {
            $lastGreeting = $this->greetingService->find($lastDisplayedArtworkId);
        }
        return $lastGreeting;
    }
}
