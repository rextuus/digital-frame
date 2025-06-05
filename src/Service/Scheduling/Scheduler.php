<?php

declare(strict_types=1);

namespace App\Service\Scheduling;

use App\Repository\ScheduleSlotRepository;
use DateTime;

readonly class Scheduler
{
    public function __construct(
        private ScheduleSlotRepository $scheduleSlotRepository,
        private ScheduleSlotHandlerProvider $scheduleSlotHandlerProvider
    ) {
    }

    public function schedule(): void
    {
        date_default_timezone_set('Europe/Paris');

        $currentDate = new DateTime();
        $start = new DateTime(
            sprintf('%s:%s:00', $currentDate->format('H'), $currentDate->format('i'))
        );

        $currentSlot = $this->scheduleSlotRepository->findSlotStartingAt($start);

        $provider = $this->scheduleSlotHandlerProvider->getHandlerByAction($currentSlot->getScheduleAction());
        if ($currentSlot->isShouldTrigger()){
            $provider->executeAction();
        }
    }
}
