<?php

namespace App\Repository;

use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DisplateImage>
 */
class DisplateImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisplateImage::class);
    }

    //    /**
    //     * @return DisplateImage[] Returns an array of DisplateImage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DisplateImage
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return array<DisplateImage>|int
     */
    public function getPaginatedImagesByTag(
        int $limit,
        int $offset,
        ?SearchTag $tag = null,
        bool $count = false
    ): array|bool {
        $qb = $this->createQueryBuilder('i');

        if ($tag !== null) {
            $qb->join('i.searchTag', 't')
                ->where('t = :tag')
                ->setParameter('tag', $tag);
        }

        if ($count) {
            $qb->select('COUNT(i.id)');
            $total = $qb->getQuery()->getSingleScalarResult();
            if ($total === 0) {
                return false;
            }

            return $offset < $total / $limit;
        }

        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }


    public function getNextNonDisplayedOrBlockedImageForCurrentTag(SearchTag $tag): ?DisplateImage
    {
        $qb = $this->createQueryBuilder('i');
        $qb->join('i.searchTag', 't')
            ->where('t.term = :tag')
            ->andWhere($qb->expr()->isNotNull('i.viewed'))
            ->andWhere($qb->expr()->eq('i.blocked', 'false'))
            ->setParameter('tag', $tag->getTerm())
            ->setMaxResults(1)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getOneOrNullResult();
    }


}
