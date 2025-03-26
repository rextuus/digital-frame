<?php

namespace App\Service\FrameConfiguration;

enum DisplayMode: int
{
    case UNSPLASH = 1;
    case SPOTIFY = 2;
    case GREETING = 3;
    case ARTSY = 4;
    case NASA = 5;

    public function getRedirect(): string
    {
        match ($this) {
            self::UNSPLASH => $value = 'app_stage_unsplash',
            self::SPOTIFY => $value = 'app_stage_spotify',
            self::GREETING => $value = 'app_stage_greeting',
            self::ARTSY => $value = 'app_stage_artsy',
            self::NASA => $value = 'app_stage_nasa',
        };

        return $value;
    }
}
