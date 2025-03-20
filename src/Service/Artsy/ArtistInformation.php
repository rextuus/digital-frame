<?php

declare(strict_types=1);

namespace App\Service\Artsy;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ArtistInformation
{
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ArtistInformation
    {
        $this->name = $name;
        return $this;
    }
}
