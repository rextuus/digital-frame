<?php

namespace App\Repository;

use App\Entity\Greeting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Greeting>
 *
 * @method Greeting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Greeting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Greeting[]    findAll()
 * @method Greeting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GreetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Greeting::class);
    }

    public function save(Greeting $image): void
    {
        $this->getEntityManager()->persist($image);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<Greeting>
     */
    public function getDisplayedGreetingsNeedingSync(): array
    {
        $qb = $this->createQueryBuilder('g');

        $qb->where($qb->expr()->gt('g.displayed', 'g.lastSynced'));

        return $qb->getQuery()->getResult();
    }
}
