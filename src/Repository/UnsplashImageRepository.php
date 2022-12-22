<?php

namespace App\Repository;

use App\Entity\UnsplashImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnsplashImage>
 *
 * @method UnsplashImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnsplashImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnsplashImage[]    findAll()
 * @method UnsplashImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnsplashImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnsplashImage::class);
    }

    public function save(UnsplashImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UnsplashImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UnsplashImage[] Returns an array of UnsplashImage objects
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

//    public function findOneBySomeField($value): ?UnsplashImage
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findNotShownImageByTag(?string $tag)
    {
        if (is_null($tag)){
            $tag = 'random';
        }
        $qb = $this->createQueryBuilder('i');
        $qb->where('i.viewed IS NULL')
            ->andWhere($qb->expr()->eq('i.tag', ':tag'))
            ->setParameter('tag', $tag)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        if (empty($result)){
            return null;
        }

        return $result[0];
    }

    public function getDistinctTags()
    {
        $qb = $this->createQueryBuilder('i');
        $qb->select('i.tag')
            ->distinct();
        $result = $qb->getQuery()->getResult();
        if (empty($result)){
            return [];
        }

        return $result;
    }
}
