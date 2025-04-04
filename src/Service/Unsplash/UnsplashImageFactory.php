<?php

namespace App\Service\Unsplash;

use App\Entity\UnsplashImage;

class UnsplashImageFactory
{
    public function createImage(UnsplashImageData $data): UnsplashImage
    {
        $image = self::getInstance();
        $image->setName($data->getName());
        $image->setUrl($data->getUrl());
        $image->setColor($data->getColor());
        $image->setTerm($data->getTag());
        $image->setViewed(null);

        return $image;
    }

    private static function getInstance (): UnsplashImage
    {
        return new UnsplashImage();
    }
}