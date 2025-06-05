<?php

namespace App\Service\Displate\Message;

use App\Repository\SearchTagRepository;
use App\Service\Displate\DisplateImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class CollectSearchTagPageMessageHandler
{
    public function __construct(
        private DisplateImageService $displateImageService,
        private SearchTagRepository $searchTagRepository,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CollectSearchTagPageMessage $message): void
    {
        $searchTag = $this->searchTagRepository->find($message->getTagId());
        $currentPage = $searchTag->getCurrentPage();
        if ($currentPage === $searchTag->getTotalPages()) {
            $searchTag->setCollectingInProgress(false);
            $searchTag->setFullyLStored(true);

            $this->entityManager->persist($searchTag);
            $this->entityManager->flush();

            return;
        }

        $this->displateImageService->storeNewImagesForTag($searchTag);
        $currentPage++;
        // break after 2 pages
        if ($currentPage % 2 === 0) {
            return;
        }

        $this->messageBus->dispatch(new CollectSearchTagPageMessage($message->getTagId()));
    }
}
