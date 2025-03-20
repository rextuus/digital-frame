<?php

namespace App\Service\Unsplash;

use App\Entity\UnsplashImage;
use App\Repository\UnsplashImageRepository;
use DateTime;
use PHPUnit\Util\Exception;
use Unsplash\Photo;

class UnsplashImageService
{
    private int $tryCounter = 0;

    public function __construct
    (
        private readonly UnsplashImageFactory $imageFactory,
        private readonly UnsplashImageRepository $imageRepository,
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
        $image->setTag($data->getTag());
        $image->setColor($data->getColor());
        $image->setName($data->getName());

        $this->imageRepository->save($image, true);
        return $image;
    }

    public function storeNewRandomImages(): void
    {
        $newImages = $this->api->getRandomImageLinks();

        $this->storeImagesFromApiResponse($newImages);
    }

    public function storeNewImageByTag(string $tag): void
    {
        $newImages = $this->api->getImageLinksByTag($tag);

        $this->storeImagesFromApiResponse($newImages, $tag);
    }

    public function getNextRandomImage(?string $tag): UnsplashImage
    {
        $this->tryCounter = $this->tryCounter + 1;
        $image = $this->imageRepository->findNotShownImageByTag($tag);

        // we need new images
        if (is_null($image)){
            if ($tag === 'random'){
                $this->storeNewRandomImages();
            }else{
                $this->storeNewImageByTag($tag);
            }
            // TODO make some counter mechanism
            if ($this->tryCounter < 3){
                $image = $this->getNextRandomImage($tag);
            }else{
                throw new Exception('Cant load new images for tag: '.$tag);
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
    protected function storeImagesFromApiResponse(array $newImages, string $tag = 'random'): void
    {
        foreach ($newImages as $image) {
            $data = new UnsplashImageData();
            $data->setUrl($image['urls']['regular']);
            $data->setTag($tag);
            $data->setViewed(null);
            $data->setColor($image['color']);
            $data->setName(str_replace(' ', '_', $image['description']));

            $this->storeImage($data);
        }
    }

    public function getStoredTags()
    {
        return $this->imageRepository->getDistinctTags();
    }

    public function getImageById(int $id): ?UnsplashImage
    {
        return $this->imageRepository->find($id);
    }
}
