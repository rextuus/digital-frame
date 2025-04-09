<?php

declare(strict_types=1);

namespace App\Service\Stage\Handler;

use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Nasa\NasaService;
use App\Service\Stage\ImageDisplayHandlerInterface;

readonly class NasaDisplayHandler implements ImageDisplayHandlerInterface
{
    public function __construct(
        private NasaService $nasaService,
        private FrameConfigurationService $configurationService,
    ) {
    }

    public function supports(DisplayMode $displayMode): bool
    {
        return $displayMode === DisplayMode::NASA;
    }

    public function initialize(): string
    {
        return $this->nasaService->getImageOfTheDay()->getUrl();
    }

    public function refresh(): string
    {
        $this->configurationService->setCurrentDisplayedImage(null, DisplayMode::NASA);
        $this->configurationService->setWaitForModeSwitch(false);

        $imageOfTheDay = $this->nasaService->getImageOfTheDay();

        return $imageOfTheDay->getUrl();
    }
}
