<?php

namespace App\Service\Scheduling;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ScheduleSlotHandlerInterface::SERVICE_TAG)]
interface ScheduleSlotHandlerInterface
{
    public const SERVICE_TAG = 'schedule_slot_handler';

    public function supports(ScheduleAction $scheduleAction): bool;
    public function executeAction(): void;
}
