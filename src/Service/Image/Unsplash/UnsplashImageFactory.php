<?php

namespace App\Service\Image\Unsplash;

use App\Entity\Image;
use App\Entity\UnsplashImage;
use App\Service\Image\ImageData;

class UnsplashImageFactory
{
    public function createImage(UnsplashImageData $data): UnsplashImage
    {
        $image = self::getInstance();
        $image->setName($data->getName());
        $image->setUrl($data->getUrl());
        $image->setColor($data->getColor());
        $image->setTag($data->getTag());
        $image->setViewed(null);

        return $image;
    }

    private static function getInstance (): UnsplashImage
    {
        return new UnsplashImage();
    }
}