<?php

namespace App\Service\Scheduling\Message;

use App\Service\Scheduling\Scheduler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final readonly class RunScheduleMessageHandler
{
    public function __construct(
        private Scheduler $scheduler,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(RunScheduleMessage $message): void
    {
        // Run the scheduling logic
        $this->scheduler->schedule();

        // Calculate delay until the next full minute + 1 second
        $currentTimestamp = time();
        $nextMinute = (int)(ceil($currentTimestamp / 60) * 60) + 1;
        $delay = ($nextMinute - $currentTimestamp) * 1000;

        // Dispatch the next RunScheduleMessage with the calculated delay
        $this->messageBus->dispatch(new RunScheduleMessage(), [
            new DelayStamp($delay)
        ]);
    }

}
