<?php

namespace App\Service\Artsy;

enum ArtworkDimensionFilter: string
{
    case ALL = 'all';
    case LANDSCAPE = 'landscape';
    case PORTRAIT = 'portrait';
}
