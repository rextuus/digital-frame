<?php

namespace App\Controller;

use App\Entity\FrameConfiguration;
use App\Entity\UnsplashImage;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Image\Unsplash\UnsplashImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route('/image/random', name: 'app_image_random')]
    public function random(
        Request $request,
        UnsplashImageService $imageService,
        FrameConfigurationService $configurationService
    ): Response
    {
        $tag = $request->query->get('tag');
        if (!$tag){
            $tag = $configurationService->getCurrentTag();
        }

        $randomImage = $imageService->getNextRandomImage($tag);
        return new JsonResponse(['url' => $randomImage->getUrl()]);
    }
}
