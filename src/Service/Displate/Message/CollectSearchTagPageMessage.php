<?php

namespace App\Service\Displate\Message;

final readonly class CollectSearchTagPageMessage
{
     public function __construct(
         private int $tagId,
     ) {
     }

    public function getTagId(): int
    {
        return $this->tagId;
    }
}
