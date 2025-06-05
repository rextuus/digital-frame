<?php

namespace App\Repository;

use App\Entity\ScheduleSlot;
use App\Service\Scheduling\TimeSlotFrameDto;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduleSlot>
 */
class ScheduleSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleSlot::class);
    }

    /**
     * @return array<ScheduleSlot>
     */
    public function findSlotsInTimeFrame(DateTimeInterface $start, DateTimeInterface $finish): array
    {
        // Adjust input parameters to match the baseline 1970-01-01
        $adjustedStart = $this->prepareDateParameter($start);
        $adjustedFinish = $this->prepareDateParameter($finish);

        // Perform a query to find slots within the adjusted time frame
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.start >= :start')  // Slot starts at or after the adjusted start time
        ->andWhere('s.finish <= :finish') // Slot ends at or before the adjusted finish time
        ->setParameter('start', $adjustedStart)
            ->setParameter('finish', $adjustedFinish);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<TimeSlotFrameDto>
     */
    public function getSlotsByIdentifier(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id, s.frameIdentifier, s.interval, MIN(s.start) as firstSlot, MAX(s.finish) as lastSlot')
            ->groupBy('s.frameIdentifier');

        // Fetch results
        $results = $qb->getQuery()->getResult();

        // Transform the results to the required output format
        return array_map(static function (array $result) {
            return new TimeSlotFrameDto(
                $result['id'],
                $result['frameIdentifier'],
                new DateTimeImmutable($result['firstSlot']),
                new DateTimeImmutable($result['lastSlot']),
                (int) $result['interval'],
            );
        }, $results);
    }

    public function findSlotStartingAt(DateTimeInterface $start): ScheduleSlot
    {
        // Adjust input parameters to match the baseline 1970-01-01
        $adjustedStart = $this->prepareDateParameter($start);

        // Perform a query to find slots within the adjusted time frame
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.start = :start')
        ->setParameter('start', $adjustedStart);

        return $qb->getQuery()->getSingleResult();
    }


    private function prepareDateParameter(DateTimeInterface $date): DateTimeInterface
    {
        return (new \DateTimeImmutable('1970-01-01 00:00:00'))
            ->setTime((int) $date->format('H'), (int) $date->format('i'), (int) $date->format('s'));
    }
}
