<?php

namespace App\Repository;

use App\Entity\Favorite;
use App\Twig\Components\FavoriteGallery;
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

    public function findPaginatedFavorites(
        int $limit,
        int $offset,
        array $modes,
        bool $count = false
    ): array|bool
    {
        $modes = array_map(fn($type) => $type->value, $modes);

        if ($modes === []){
            if ($count) {
                return false;
            }

            return [];
        }

        $qb = $this->createQueryBuilder('f');
        $qb->select('f')
            ->where($qb->expr()->in('f.displayMode', $modes));

        if ($count) {
            $qb->select('COUNT(f.id)');
            $total = $qb->getQuery()->getSingleScalarResult();
            if ($total === 0) {
                return false;
            }

            return $offset/ FavoriteGallery::IMAGES_PER_PAGE +1 < $total / $limit;
        }


        $qb->setMaxResults($limit)
            ->setFirstResult($offset);


        return $qb->getQuery()->getResult();
    }

    public function countFavorites(array $modes): int
    {
        if ($modes === []){
            return 0;
        }

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
