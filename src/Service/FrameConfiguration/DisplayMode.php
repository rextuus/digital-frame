<?php

namespace App\Service\FrameConfiguration;

enum DisplayMode: int
{
    case UNSPLASH = 1;
    case SPOTIFY = 2;
    case GREETING = 3;
    case ARTSY = 4;
    case NASA = 5;
    case DISPLATE = 6;

    public static function fromName(string $mode): DisplayMode
    {
        return match ($mode) {
            'UNSPLASH' => self::UNSPLASH,
            'SPOTIFY' => self::SPOTIFY,
            'GREETING' => self::GREETING,
            'ARTSY' => self::ARTSY,
            'NASA' => self::NASA,
            'DISPLATE' => self::DISPLATE,
        };
    }

    public function getFontAwesomeClass(): string
    {
        return match ($this){
            self::UNSPLASH => 'fa-brands fa-unsplash fa-2x',
            self::SPOTIFY => 'fa-brands fa-spotify fa-2x',
            self::GREETING => 'fa-solid fa-image fa-2x',
            self::ARTSY => 'fa-solid fa-palette fa-2x',
            self::NASA => 'fa-solid fa-shuttle-space fa-2x',
            self::DISPLATE => 'fa-solid fa-d fa-2x',
            default => 'fa-solid fa-tag',
        };
    }

    public function getFavoriteColorStyle(): string
    {
        return match ($this){
            DisplayMode::UNSPLASH => 'bg-light-blue',
            DisplayMode::SPOTIFY => 'bg-green',
            DisplayMode::GREETING => 'bg-yellow',
            DisplayMode::ARTSY => 'bg-primary',
            DisplayMode::NASA => 'bg-dark-blue',
            DisplayMode::DISPLATE => 'bg-orange',
            default => 'bg-default',
        };
    }
}
