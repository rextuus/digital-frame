<?php

namespace App\Repository;

use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SearchTag>
 */
class SearchTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchTag::class);
    }

    //    /**
    //     * @return UnsplashTag[] Returns an array of UnsplashTag objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UnsplashTag
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return array<SearchTag>
     */
    public function getExistingDisplateTags(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t');
        $qb->innerJoin(DisplateImage::class, 'd');
        return $qb->getQuery()->getResult();
    }
}
