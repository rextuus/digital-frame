<?php

namespace App\Repository;

use App\Entity\SpotifyAccessToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<SpotifyAccessToken>
 */
class SpotifyAccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotifyAccessToken::class);
    }

    public function save(SpotifyAccessToken $spotifyAccessToken): void
    {
        $this->getEntityManager()->persist($spotifyAccessToken);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws Exception
     */
    public function getNewestToken(): SpotifyAccessToken
    {
        $newestToken = $this->findOneBy(
            [],
            ['id' => 'DESC']
        );


        if ($newestToken === null) {
            throw new Exception('No tokens found');
        }

        return $newestToken;
    }
}
