<?php

namespace App\Service\Stage;

use App\Service\FrameConfiguration\DisplayMode;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ImageDisplayHandlerInterface::SERVICE_TAG)]
interface ImageDisplayHandlerInterface
{
    public const SERVICE_TAG = 'image_display_handler';

    public function supports(DisplayMode $displayMode): bool;

    public function initialize(): string;

    public function refresh(): string;
}
