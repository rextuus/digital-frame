<?php

namespace App\Repository;

use App\Entity\ArtsyImage;
use App\Service\Artsy\ArtworkDimensionFilter;
use App\Service\Artsy\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Util\Exception;

use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<ArtsyImage>
 */
class ArtsyImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtsyImage::class);
    }

    public function findCurrentArtwork(
        ArtworkDimensionFilter $artworkDimensionFilter = ArtworkDimensionFilter::ALL
    ): ArtsyImage {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->isNull('a.viewed'));
        $qb->orderBy('a.id', 'ASC');
        $qb->setMaxResults(1);

        match ($artworkDimensionFilter) {
            ArtworkDimensionFilter::ALL => $qb,
            ArtworkDimensionFilter::LANDSCAPE => $qb->andWhere($qb->expr()->gt('a.width', 'a.height')),
            ArtworkDimensionFilter::PORTRAIT => $qb->andWhere($qb->expr()->gt('a.height', 'a.width')),
            default => $qb,
        };

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            throw new Exception('No current artwork found');
        }

        return $result;
    }

    /**
     * @param array<Category> $types
     * @return <ArtsyImage>
     */
    public function findPaginatedImages(int $page, int $limit, array $types): array
    {
        $types = array_map(fn($type) => $type->value, $types);

        $qb = $this->createQueryBuilder('a');
        $qb->select('a')
            ->where($qb->expr()->in('a.category', $types))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);


        return $qb->getQuery()->getResult();
    }

    public function getNextPageUrl(): ArtsyImage
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->isNull('a.nextPageUrlStored'));
        $qb->andWhere($qb->expr()->isNotNull('a.nextPageUrl'));
        $qb->orderBy('a.id', 'ASC');
        $qb->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            throw new Exception('No current artwork found');
        }

        return $result;
    }
}
