<?php

namespace App\Repository;

use App\Entity\UnsplashImage;
use App\Entity\UnsplashTag;
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
    public function findNotShownImageByTag(UnsplashTag $tag): ?UnsplashImage
    {
        $qb = $this->createQueryBuilder('i');
        $qb->select('i');
        $qb->join('i.unsplashTag', 't');
        $qb->where('i.viewed IS NULL')
            ->andWhere($qb->expr()->eq('i.unsplashTag', ':tag'))
            ->setParameter('tag', $tag)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        if ($result === []){
            return null;
        }

        return $result[0];
    }
}
