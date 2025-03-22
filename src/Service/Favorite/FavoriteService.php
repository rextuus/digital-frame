<?php

declare(strict_types=1);

namespace App\Service\Favorite;

use App\Entity\Favorite;
use App\Entity\FavoriteList;
use App\Repository\FavoriteListRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class FavoriteService
{
    private const DEFAULT_FAVORITE_LIST_NAME = 'Favorites';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FavoriteListRepository $favoriteListRepository,
    )
    {
    }

    public function storeFavorite(Favorite $favorite): void
    {
        $favoriteList = $this->getDefaultFavoriteList();
        $favoriteList->addFavorite($favorite);
        $favorite->addFavoriteList($favoriteList);

        $this->entityManager->persist($favoriteList);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();
    }

    private function getDefaultFavoriteList(): FavoriteList
    {
        $favoriteList = $this->favoriteListRepository->findOneBy(['ident' => self::DEFAULT_FAVORITE_LIST_NAME]);
        if (!$favoriteList) {
            $favoriteList = new FavoriteList();
            $favoriteList->setIdent(self::DEFAULT_FAVORITE_LIST_NAME);

            $this->entityManager->persist($favoriteList);
            $this->entityManager->flush();
        }

        return $favoriteList;
    }
}
