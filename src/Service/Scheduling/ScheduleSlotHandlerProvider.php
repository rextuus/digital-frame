<?php

declare(strict_types=1);

namespace App\Service\Scheduling;

use App\Service\Scheduling\Exception\NoScheduleHandlerFoundException;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ScheduleSlotHandlerProvider
{

    /**
     * @param iterable<ScheduleSlotHandlerInterface> $handlers
     */
    public function __construct(
        #[AutowireIterator(ScheduleSlotHandlerInterface::SERVICE_TAG)]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws NoScheduleHandlerFoundException
     */
    public function getHandlerByAction(ScheduleAction $scheduleAction): ScheduleSlotHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($scheduleAction)) {
                return $handler;
            }
        }

        throw new NoScheduleHandlerFoundException('No handler found for action ' . $scheduleAction->name);
    }
}
