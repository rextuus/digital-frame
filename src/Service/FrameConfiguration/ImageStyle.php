<?php

namespace App\Service\FrameConfiguration;

enum ImageStyle: string
{
    case ORIGINAL = 'original';
    case SCREEN_WIDTH = 'screen_width';
    case CUSTOM_HEIGHT = 'custom_height';
}
