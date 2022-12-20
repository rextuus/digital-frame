<?php

namespace App\Service\Image;

use App\Entity\Image;
use DateTime;

class ImageFactory
{
    public function createImage(ImageData $data): Image
    {
        $image = self::getInstance();
        $image->setType($data->getType());
        $image->setName($data->getName());
        $image->setUrl($data->getUrl());
        $image->setPath($data->getPath());
        $image->setCreated(new DateTime());

        return $image;
    }

    private static function getInstance (): Image
    {
        return new Image();
    }
}