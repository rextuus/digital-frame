<?php

namespace App\Service\Displate\Message;

use App\Entity\DisplateImage;
use App\Repository\SearchTagRepository;
use App\Service\Displate\DisplateImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CollectDisplateImageMessageHandler
{
    public function __construct(
        private DisplateImageService $displateImageService,
        private SearchTagRepository $searchTagRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CollectDisplateImageMessage $message): void
    {
        $searchTag = $this->searchTagRepository->find($message->getTagId());
        $url = sprintf('https://displate.com/displate/%s', $message->getDisplateId());
        $bestResolutionImages = $this->displateImageService->fetchAndFilterImagesFromUrl($url);
        if (count($bestResolutionImages) > 0) {
            $bestResolutionImage = $bestResolutionImages[array_key_first($bestResolutionImages)];

            // create the new image
            $image = new DisplateImage();
            $image->setSearchTag($searchTag);
            $searchTag->addDisplateImage($image);
            $image->setName($bestResolutionImage->getName());
            $image->setUrl($bestResolutionImage->getUrl());
            $image->setViewed(null);
            $image->setBlocked(false);

            $this->entityManager->persist($image);
            $this->entityManager->flush();
        }

        // mark collecting finished
        if ($message->isLast()) {
            $searchTag->setCollectingInProgress(false);
            $this->entityManager->persist($searchTag);
            $this->entityManager->flush();
        }
    }
}
