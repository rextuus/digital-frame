<?php

declare(strict_types=1);

namespace App\Service\Favorite;

use App\Entity\Favorite;
use App\Entity\FavoriteList;
use App\Repository\FavoriteListRepository;
use App\Repository\FavoriteRepository;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Doctrine\ORM\EntityManagerInterface;

readonly class FavoriteService
{
    private const DEFAULT_FAVORITE_LIST_NAME = 'Favorites';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FavoriteRepository $favoriteRepository,
        private FavoriteListRepository $favoriteListRepository,
        private ModeToFavoriteConvertProvider $modeToFavoriteConvertProvider,
        private FrameConfigurationService $frameConfigurationService,
    )
    {
    }

    public function storeFavorite(Favorite $favorite, ?FavoriteList $favoriteList = null): void
    {
        if ($favoriteList === null) {
            $favoriteList = $this->getDefaultFavoriteList();
        }
        $favoriteList->addFavorite($favorite);
        $favorite->addFavoriteList($favoriteList);

        $this->entityManager->persist($favoriteList);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();
    }

    public function getDefaultFavoriteList(): FavoriteList
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

    /**
     * @return array<FavoriteList>
     */
    public function getFavoriteListsForTarget(): array
    {
        $converter = $this->modeToFavoriteConvertProvider->getFittingConverterForCurrentMode();
        $lastImageDto = $converter->getLastImageDto();

        return $this->favoriteListRepository->findListsByFavorite($lastImageDto->getUrl());
    }

    public function removeFavoriteFromList(Favorite $favorite, FavoriteList $favoriteList): void
    {
        $favoriteList->removeFavorite($favorite);
        $favorite->removeFavoriteList($favoriteList);

        // Persist and flush changes to sync before checking dependencies
//        $this->entityManager->persist($favoriteList);
        $this->entityManager->flush();

        // Check if Favorite has no associated FavoriteLists left
        if ($favorite->getFavoriteLists()->isEmpty()) {
            $this->entityManager->remove($favorite);
        }
        // Final flush after removing the Favorite
        $this->entityManager->flush();
    }

    public function toggleFavoriteAndList(Favorite $favorite, FavoriteList $favoriteList): void
    {
        $isInList = false;
        foreach ($favoriteList->getFavorites() as $listFavorite) {
            if ($listFavorite->getDisplayUrl() === $favorite->getDisplayUrl()) {
                // we need to exchange the favorite coming in with the one from the list.
                // incoming is a not persisted Favorite and more like a dto cause it comes from a provider
                $favorite = $listFavorite;

                $isInList = true;
                break;
            }
        }

        if ($isInList) {
            $this->removeFavoriteFromList($favorite, $favoriteList);
        } else {
            $this->storeFavorite($favorite, $favoriteList);
        }
    }

    public function getFavorite(int $favoriteId): ?Favorite
    {
        return $this->favoriteRepository->find($favoriteId);
    }

    public function getNextForCurrentFavoriteList(): Favorite
    {
        $configuration = $this->frameConfigurationService->getConfiguration();
        $currentList = $configuration->getCurrentFavoriteList();
        $currentIndex = $configuration->getCurrentFavoriteListIndex();
        if ($currentList === null) {
            $currentList = $this->getDefaultFavoriteList();
        }

        $nextIndex = $currentIndex + 1;
        $nextFavorite = $currentList->getFavorites()->get($nextIndex);
        if ($nextFavorite === null) {
            $nextFavorite = $currentList->getFavorites()->first();
            $nextIndex = 0;
        }
        $updateData = $this->frameConfigurationService->getDefaultUpdateData();
        $updateData->setCurrentFavoriteListIndex($nextIndex);
        $updateData->setCurrentFavoriteList($currentList);
        $this->frameConfigurationService->update($updateData);

        return $nextFavorite;
    }
}
