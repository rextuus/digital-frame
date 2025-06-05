<?php

namespace App\Service\Displate\Message;

final readonly class CollectDisplateImageMessage
{
     public function __construct(
         private int $tagId,
         private int $displateId,
         private bool $isLast = false,
     ) {
     }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function getDisplateId(): int
    {
        return $this->displateId;
    }

    public function isLast(): bool
    {
        return $this->isLast;
    }
}
