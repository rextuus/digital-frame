<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    //    /**
    //     * @return Favorite[] Returns an array of Favorite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Favorite
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findPaginatedFavorites(int $page, int $limit, array $modes, string $sort = 'ASC')
    {
        $modes = array_map(fn($type) => $type->value, $modes);

        $qb = $this->createQueryBuilder('f');
        $qb->select('f')
            ->where($qb->expr()->in('f.displayMode', $modes))
            ->orderBy('f.id', $sort)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFavorites(array $modes): int
    {
        $modes = array_map(fn($type) => $type->value, $modes);

        $qb = $this->createQueryBuilder('f');
        $qb->select('COUNT(f.id)')
            ->where($qb->expr()->in('f.displayMode', $modes));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<string>
     */
    public function getPresentModes(): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('DISTINCT f.displayMode');

        return $qb->getQuery()->getResult();
    }
}
