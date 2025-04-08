<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SearchTag;

class DisplateTagData
{
    private ?SearchTag $existingTag = null;
    private ?string $newTag = null;

    public function getExistingTag(): ?SearchTag
    {
        return $this->existingTag;
    }

    public function setExistingTag(?SearchTag $existingTag): DisplateTagData
    {
        $this->existingTag = $existingTag;
        return $this;
    }

    public function getNewTag(): ?string
    {
        return $this->newTag;
    }

    public function setNewTag(?string $newTag): DisplateTagData
    {
        $this->newTag = $newTag;
        return $this;
    }
}
