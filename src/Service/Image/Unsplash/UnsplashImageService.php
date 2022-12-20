<?php

namespace App\Service\Image\Unsplash;

use App\Entity\Image;
use App\Entity\UnsplashImage;
use App\Repository\ImageRepository;
use App\Repository\UnsplashImageRepository;
use App\Service\Image\ImageData;
use DateTime;
use Symfony\Component\HttpClient\HttpClient;

class UnsplashImageService
{

    public function __construct
    (
        private UnsplashImageFactory $imageFactory,
        private UnsplashImageRepository $imageRepository,
        private UnsplashApiService $api
    )
    {
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

    public function storeNewRandomImages (){
        $newImages = $this->api->getRandomImageLinks();

        $this->storeImagesFromApiResponse($newImages);
    }

    public function storeNewImageByTag (string $tag){
        $newImages = $this->api->getImageLinksByTag($tag);

        $this->storeImagesFromApiResponse($newImages, $tag);
    }

    public function getNextRandomImage(?string $tag): UnsplashImage
    {
        $image = $this->imageRepository->findNotShownImageByTag($tag);

        // we need new images
        if (is_null($image)){
            if ($tag === 'random'){
                $this->storeNewRandomImages();
            }else{
                $this->storeNewImageByTag($tag);
            }
            // TODO make some counter mechanism
//            $image = $this->getNextRandomImage($tag);
        }

        $data = (new UnsplashImageData())->initFrom($image);
        $data->setViewed(new DateTime());
        $this->updateImage($data, $image);
        return $image;
    }

    /**
     * @param array $newImages
     * @return void
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
}
