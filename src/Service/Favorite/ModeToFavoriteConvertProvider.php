<?php

declare(strict_types=1);

namespace App\Service\Favorite;

use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ModeToFavoriteConvertProvider
{
    /**
     * @param iterable<ModeToFavoriteConverterInterface> $converters
     */
    public function __construct(
        #[AutowireIterator(ModeToFavoriteConverterInterface::SERVICE_TAG)]
        private iterable $converters,
        private FrameConfigurationService $frameConfigurationService
    ) {
    }

    public function getFittingConverterForCurrentMode(): ModeToFavoriteConverterInterface
    {
        return $this->getConverterForMode($this->frameConfigurationService->getCurrentlyDisplayedImageMode());
    }

    /**
     * @throws Exception
     */
    public function getConverterForMode(DisplayMode $mode): ModeToFavoriteConverterInterface
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($mode)) {
                return $converter;
            }
        }

        throw new Exception('No converter found for mode');
    }
}
