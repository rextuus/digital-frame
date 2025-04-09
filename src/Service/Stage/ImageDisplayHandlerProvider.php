<?php

declare(strict_types=1);

namespace App\Service\Stage;

use App\Service\FrameConfiguration\DisplayMode;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ImageDisplayHandlerProvider
{
    /**
     * @param iterable<ImageDisplayHandlerInterface> $handlers
     */
    public function __construct(
        #[AutowireIterator(ImageDisplayHandlerInterface::SERVICE_TAG)]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getHandlerForMode(DisplayMode $displayMode): ImageDisplayHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($displayMode)) {
                return $handler;
            }
        }
dd($this->handlers);
        throw new Exception('No handler found for mode ' . $displayMode->name);
    }
}
