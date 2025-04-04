<?php

namespace App\Service\Unsplash;

use App\Entity\UnsplashImage;
use App\Entity\UnsplashTag;
use App\Repository\UnsplashImageRepository;
use App\Repository\UnsplashTagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Exception;
use Unsplash\Photo;

class UnsplashImageService
{
    public const TAG_RANDOM = 'random';
    private int $tryCounter = 0;

    public function __construct
    (
        private readonly UnsplashImageFactory $imageFactory,
        private readonly UnsplashImageRepository $imageRepository,
        private readonly UnsplashTagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UnsplashApiService $api
    ) {
    }

    public function storeImage(UnsplashImageData $data): UnsplashImage
    {
        $image = $this->imageFactory->createImage($data);

        $this->imageRepository->save($image, true);
        return $image;
    }

    public function updateImage(UnsplashImageData $data, UnsplashImage $image): UnsplashImage
    {
        $image->setViewed($data->getViewed());
        $image->setUrl($data->getUrl());
        $image->setTerm($data->getTag());
        $image->setColor($data->getColor());
        $image->setName($data->getName());

        $this->imageRepository->save($image, true);
        return $image;
    }

    public function storeNewImageByTag(UnsplashTag $tag): void
    {
        // new tags need to checked for pages
        if ($tag->getTotalPages() === 0 && !$tag->isFullyLStored()) {
            $this->storeTotalPagesForTag($tag);
        }

        $newImages = $this->api->getImageLinksByTag($tag);
        $newCurrentPage = $tag->getCurrentPage() + 1;
        if ($newCurrentPage > $tag->getTotalPages()) {
            $tag->setFullyLStored(true);
        }
        $tag->setCurrentPage($newCurrentPage);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $this->storeImagesFromApiResponse($newImages, $tag);
    }

    private function storeTotalPagesForTag(UnsplashTag $tag): void
    {
        $totalPages = $this->api->getTotalPagesForTag($tag);
        if ($totalPages === 0) {
            $tag->setFullyLStored(true);
            $tag->setCurrentPage(0);
        }

        $tag->setTotalPages($totalPages);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
    }

    public function getNextRandomImage(UnsplashTag $tag): UnsplashImage
    {
        $this->tryCounter = $this->tryCounter + 1;
        $image = $this->imageRepository->findNotShownImageByTag($tag);

        // we need new images
        if ($image === null) {
            $this->storeNewImageByTag($tag);

            $image = $this->imageRepository->findNotShownImageByTag($tag);

            // if we cant find a new one my there are non anymore
            if ($image === null && $tag->isFullyLStored()) {
                // reset all images of tag => there are no more to fetch
                foreach ($tag->getImages() as $image) {
                    $image->setViewed(null);
                    $this->entityManager->persist($image);
                }
                $this->entityManager->flush();

                $image = $this->imageRepository->findNotShownImageByTag($tag);

                // now we must have one or the tag is not able to have some
                if ($image === null) {
                    throw new Exception(
                        'Cant load new images for tag: ' . $tag->getTerm() . ' pages: ' . $tag->getTotalPages() . ''
                    );
                }
            }
        }

        $data = (new UnsplashImageData())->initFrom($image);
        $data->setViewed(new DateTime());
        $this->updateImage($data, $image);

        return $image;
    }

    /**
     * @param array<Photo> $newImages
     */
    protected function storeImagesFromApiResponse(array $newImages, UnsplashTag $tag): void
    {
        $this->entityManager->persist($tag);
        foreach ($newImages as $image) {
            $unsplashImage = new UnsplashImage;
            $unsplashImage->setUrl($image['urls']['regular']);
            $unsplashImage->setTerm($tag->getTerm());
            $unsplashImage->setUnsplashTag($tag);
            $unsplashImage->setViewed(null);
            $unsplashImage->setColor($image['color']);
            $unsplashImage->setName(str_replace(' ', '_', $image['description']));

            $tag->addImage($unsplashImage);

            $this->entityManager->persist($unsplashImage);
        }

        $this->entityManager->flush();
    }

    public function createNewTag(string $term): UnsplashTag
    {
        $tag = $this->tagRepository->findOneBy(['term' => $term]);
        if ($tag !== null) {
            return $tag;
        }

        $tag = new UnsplashTag();
        $tag->setTerm($term);
        $tag->setCurrentPage(1);
        $tag->setTotalPages(0);

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }

    /**
     * @return array<UnsplashTag>
     */
    public function getStoredTags(): array
    {
        return $this->tagRepository->findAll();
    }

    public function getImageById(int $id): ?UnsplashImage
    {
        return $this->imageRepository->find($id);
    }
}
