<?php

namespace App\Service\FrameConfiguration;

enum DisplayMode: int
{
    case UNSPLASH = 1;
    case SPOTIFY = 2;
    case GREETING = 3;
    case ARTSY = 4;

    public function getActiveButton(): string
    {
        match ($this) {
            self::UNSPLASH => $value = 'configuration_image',
            self::SPOTIFY => $value = 'configuration_spotify',
            self::GREETING => $value = 'configuration_greeting',
            self::ARTSY => $value = 'configuration_artsy'
        };

        return $value;
    }

    public function getRedirect(): string
    {
        match ($this) {
            self::UNSPLASH => $value = 'app_stage_unsplash',
            self::SPOTIFY => $value = 'app_stage_spotify',
            self::GREETING => $value = 'app_stage_greeting',
            self::ARTSY => $value = 'app_stage_artsy'
        };

        return $value;
    }
}
