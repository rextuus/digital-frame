<?php

namespace App\Service\Artsy;

enum Category: string
{
    case PAINTING = 'Painting';
    case SCULPTURE = 'Sculpture';
    case DESIGN = 'Design/Decorative Art';
    case PHOTOGRAPHY = 'Photography';
    case PAPER_WORK = 'Drawing, Collage or other Work on Paper';
    case PRINT = 'Print';
    case UNKNOWN = 'unknown';
}
