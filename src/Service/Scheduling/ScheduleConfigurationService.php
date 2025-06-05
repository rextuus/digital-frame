<?php

declare(strict_types=1);

namespace App\Service\Scheduling;

use App\Entity\ScheduleConfiguration;
use App\Entity\ScheduleSlot;
use App\Repository\ScheduleConfigurationRepository;
use App\Repository\ScheduleSlotRepository;
use App\Service\Scheduling\Exception\ScheduleConfigurationException;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class ScheduleConfigurationService
{
    private const DEFAULT_CONFIGURATION_IDENT = 'default_schedule_configuration';
    public const DEFAULT_FRAME_IDENTIFIER = 'default_frame_identifier';

    public function __construct(
        private ScheduleConfigurationRepository $scheduleConfigurationRepository,
        private ScheduleSlotRepository $scheduleSlotRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getConfiguration(): ScheduleConfiguration
    {
        $config = $this->scheduleConfigurationRepository->findOneBy(['identifier' => self::DEFAULT_CONFIGURATION_IDENT]
        );

        if ($config === null) {
            $config = new ScheduleConfiguration();
            $config->setIdentifier(self::DEFAULT_CONFIGURATION_IDENT);
            $this->entityManager->persist($config);
            $this->entityManager->flush();

            // Create a slot for each minute of the day
            $this->createSlotsPerMinute($config);
        }

        return $config;
    }

    private function createSlotsPerMinute(ScheduleConfiguration $config): void
    {
        $start = new DateTimeImmutable('1970-01-01 00:00:00'); // Start at zero time of 1970
        $endTime = $start->modify('+1 day')->modify('-1 second'); // End at 1970-01-01 23:59:59

        $currentTime = $start;
        $startOffset = ((int) $start->format('H') * 60) + (int) $start->format('i');
        $interval = 60;

        while ($currentTime <= $endTime) {
            $slotStart = $currentTime;
            $slotEnd = $currentTime->modify('+1 minute');

            $slot = new ScheduleSlot();
            $slot->setStart($slotStart);
            $slot->setFinish($slotEnd);
            $slot->setShouldTrigger(false);
            $slot->setInterval(60);
            $slot->setScheduleAction(ScheduleAction::DEFAULT); // Default action
            $slot->setFrameIdentifier(self::DEFAULT_FRAME_IDENTIFIER); // Default action
            $slot->setScheduleConfiguration($config);

            $slotOffset = ((int) $slotStart->format('H') * 60) + (int) $slotStart->format('i');
            if (($slotOffset - $startOffset) % $interval === 0) {
                $slot->setShouldTrigger(true);
            }

            $config->addSlot($slot);
            $this->entityManager->persist($slot);

            $currentTime = $slotEnd;
        }

        $this->entityManager->flush(); // Persist all slots to the database in bulk
    }

    /**
     * @throws ScheduleConfigurationException
     */
    public function addScheduleSlot(
        DateTimeInterface $start,
        DateTimeInterface $finish,
        ScheduleAction $action,
        int $interval
    ): void {
        if ($start > $finish) {
//            throw new ScheduleConfigurationException('Start time must be before finish time.');
        }

        $identifier = sprintf('%s|%s|%s', $action->value, $start->format('H:i:s'), $finish->format('H:i:s'));

        // Find all slots in the given time range
        $slots = $this->scheduleSlotRepository->findSlotsInTimeFrame($start, $finish);

        // Convert the start time of the range into total minutes of the day
        $startOffset = ((int) $start->format('H') * 60) + (int) $start->format('i');

        // Iterate through all matching slots
        foreach ($slots as $slot) {
            $slotStart = $slot->getStart();
            $slotOffset = ((int) $slotStart->format('H') * 60) + (int) $slotStart->format('i');

            // Adjust interval logic: Start from $startOffset and only trigger based on $interval
            $slot->setShouldTrigger(false);
            if (($slotOffset - $startOffset) % $interval === 0) {
                $slot->setShouldTrigger(true);
                $slot->setScheduleAction($action);
            }

            $slot->setFrameIdentifier($identifier);
            $slot->setInterval($interval);

            // Persist changes (update the slot)
            $this->entityManager->persist($slot);
        }

        // Save all updates to the database
        $this->entityManager->flush();
    }

    public function resetSchedule(): void
    {
        $slots = $this->scheduleSlotRepository->findAll();
        foreach ($slots as $slot) {
            $slot->setShouldTrigger(false);
            $slot->setFrameIdentifier(self::DEFAULT_FRAME_IDENTIFIER);
            $slot->setInterval(60);
            $slot->setScheduleAction(ScheduleAction::DEFAULT);
            $this->entityManager->persist($slot);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array<TimeSlotFrameDto>
     */
    public function getSlotsByIdentifier(): array
    {
        return $this->scheduleSlotRepository->getSlotsByIdentifier();
    }

    public function findSlotByIdentifier(string $identifier): ?TimeSlotFrameDto
    {
        $filtered = array_filter(
            $this->getSlotsByIdentifier(),
            function (TimeSlotFrameDto $slot) use ($identifier) {
                return $slot->getIdentifier() === $identifier;
            }
        );

        return array_pop($filtered);
    }

}
