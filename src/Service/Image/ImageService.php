<?php

namespace App\Service\Image;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Symfony\Component\HttpClient\HttpClient;

class ImageService
{

    public function __construct(private ImageFactory $imageFactory, private ImageRepository $imageRepository)
    {
    }

    public function storeImage(ImageData $data): Image
    {
        $image = $this->imageFactory->createImage($data);

        $this->imageRepository->save($image);
        return $image;
    }

    public function getImageUrl(): string
    {

    }

    function downloadImage(string $url, string $savePath)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception(sprintf('Failed to download image from "%s"', $url));
        }

        $imageData = $response->getContent();
        file_put_contents($savePath, $imageData);
    }

}
