<?php

declare(strict_types=1);

namespace App\Service\Favorite;

use App\Service\FrameConfiguration\DisplayMode;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ModeToFavoriteConvertProvider
{
    /**
     * @param iterable<ModeToFavoriteConverterInterface> $converters
     */
    public function __construct(
        #[AutowireIterator(ModeToFavoriteConverterInterface::SERVICE_TAG)]
        private readonly iterable $converters
    ) {
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
