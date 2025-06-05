<?php

namespace App\Twig\Components;

use App\Entity\DisplateImage;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DisplateCard
{
    use DefaultActionTrait;

    public DisplateImage $image;
}
