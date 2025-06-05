<?php

declare(strict_types=1);

namespace App\Service\Scheduling\Handler;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Scheduling\ScheduleAction;
use App\Service\Scheduling\ScheduleSlotHandlerInterface;

readonly class ShowRandomFavoriteActionHandler implements ScheduleSlotHandlerInterface
{
    public function __construct(private FrameConfigurationService $frameConfigurationService)
    {
    }

    public function supports(ScheduleAction $scheduleAction): bool
    {
        return $scheduleAction === ScheduleAction::SHOW_RANDOM_FAVORITE_FROM_LIST;
    }

    public function executeAction(): void
    {

    }
}
