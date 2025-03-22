<?php

namespace App\Service\Artsy;

enum ArtworkDimensionFilter: string
{
    case ALL = 'all';
    case LANDSCAPE = 'landscape';
    case PORTRAIT = 'portrait';

    public function getFontAwesomeClass(): string
    {
        return match ($this) {
            self::ALL => 'fas fa-images',
            self::LANDSCAPE => 'fas fa-panorama',
            self::PORTRAIT => 'fas fa-portrait',
        };
    }
}
