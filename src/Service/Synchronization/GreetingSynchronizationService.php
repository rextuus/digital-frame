<?php

declare(strict_types=1);

namespace App\Service\Synchronization;

use App\Entity\Greeting;
use App\Service\Greeting\GreetingService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class GreetingSynchronizationService
{
    public function __construct(
        private readonly DigitalFrameApiGateway $apiGateway,
        private readonly GreetingService $greetingService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function checkForNewGreetings(): bool
    {
        return $this->apiGateway->checkGreetings();
    }

    public function synchronizeDisplayedGreetingsToServer(): void
    {
        $greetingsToSync = $this->greetingService->getDisplayedGreetingsNeedingSync();
        $ids = array_map(fn (Greeting $greeting) => $greeting->getRemoteId(), $greetingsToSync);

        $this->apiGateway->markGreetingsAsDisplayed($ids);

        foreach ($greetingsToSync as $greeting) {
            $greeting->setLastSynced(new DateTime());
            $this->entityManager->persist($greeting);
        }

        $this->entityManager->flush();
    }

    public function synchronizeGreetingsFromServer(): void
    {
        $greetings = $this->apiGateway->getGreetings();

        $ids = [];
        foreach ($greetings as $greetingData) {
            $this->greetingService->createByData($greetingData);
            $ids[] = $greetingData->getRemoteId();
        }

        $this->apiGateway->markGreetingsAsDelivered($ids);
    }
}
