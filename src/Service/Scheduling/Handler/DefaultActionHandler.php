<?php

declare(strict_types=1);

namespace App\Service\Scheduling\Handler;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Scheduling\ScheduleAction;
use App\Service\Scheduling\ScheduleSlotHandlerInterface;

class DefaultActionHandler implements ScheduleSlotHandlerInterface
{
    public function __construct(private FrameConfigurationService $frameConfigurationService)
    {
    }

    public function supports(ScheduleAction $scheduleAction): bool
    {
        return $scheduleAction === ScheduleAction::DEFAULT;
    }

    public function executeAction(): void
    {
        $this->frameConfigurationService->setNext(true);
    }
}
