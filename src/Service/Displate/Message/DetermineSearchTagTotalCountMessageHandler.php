<?php

namespace App\Service\Displate\Message;

use App\Service\Displate\DisplateImageService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DetermineSearchTagTotalCountMessageHandler
{
    public function __construct(private DisplateImageService $displateImageService)
    {
    }

    public function __invoke(DetermineSearchTagTotalCountMessage $message): void
    {
        $this->displateImageService->calculateTotalPagesForTag($message->getTagId());
    }
}
