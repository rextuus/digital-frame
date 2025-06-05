<?php

namespace App\Twig\Components;

use App\Entity\SearchTag;
use App\Service\Displate\Message\CollectSearchTagPageMessage;
use App\Service\Displate\Message\DetermineSearchTagTotalCountMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SearchTagComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public SearchTag $searchTag;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[LiveAction]
    public function countPages(): void
    {
        $this->searchTag->setCollectingInProgress(true);
        $this->entityManager->persist($this->searchTag);
        $this->entityManager->flush();

        $message = new DetermineSearchTagTotalCountMessage($this->searchTag->getId());
        $this->messageBus->dispatch($message);
    }

    #[LiveAction]
    public function collectImages(): void
    {
        $this->searchTag->setCollectingInProgress(true);
        $this->entityManager->persist($this->searchTag);
        $this->entityManager->flush();

        $message = new CollectSearchTagPageMessage($this->searchTag->getId());
        $this->messageBus->dispatch($message);
    }
}
