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

    public static function fromName(string $name): ?self
    {
        return match ($name) {
            'PAINTING' => self::PAINTING,
            'SCULPTURE' => self::SCULPTURE,
            'DESIGN' => self::DESIGN,
            'PHOTOGRAPHY' => self::PHOTOGRAPHY,
            'PAPER_WORK' => self::PAPER_WORK,
            'PRINT' => self::PRINT,
            default => self::UNKNOWN,
        };
    }

    public function getFontAwesomeClass(): string
    {
        return match ($this){
            self::PAINTING => 'fa-solid fa-palette',
            self::SCULPTURE => 'fa-solid fa-screwdriver-wrench',
            self::DESIGN => 'fa-solid fa-shapes',
            self::PHOTOGRAPHY => 'fa-solid fa-image',
            self::PAPER_WORK => 'fa-solid fa-paper-plane',
            self::PRINT => 'fa-solid fa-print',
            default => 'fa-solid fa-tag',
        };
    }
}
