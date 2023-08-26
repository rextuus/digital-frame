<?php

declare(strict_types=1);

namespace App\Service\Synchronization;

use App\Service\Greeting\GreetingService;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GreetingSynchronizationService
{
    public function __construct(private DigitalFrameApiGateway $apiGateway, private GreetingService $greetingService) { }

    public function checkForNewGreetings(): bool
    {
//        return true;
        return $this->apiGateway->checkGreetings();
    }

    /**
     * @param int[] $ids
     */
    public function markAsDisplayed(array $ids): void
    {
        $this->apiGateway->markGreetingsAsDisplayed($ids);
    }

    public function synchronizeGreetings(): void
    {
        $greetings = $this->apiGateway->getGreetings();
        $ids = [];
        foreach ($greetings as $greetingData){
            $this->greetingService->createByData($greetingData);
            $ids[] = $greetingData->getRemoteId();
        }

        $this->apiGateway->markGreetingsAsDelivered($ids);
    }
}
