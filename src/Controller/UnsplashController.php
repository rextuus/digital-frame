<?php

namespace App\Controller;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Unsplash\UnsplashImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnsplashController extends AbstractController
{
    #[Route('/image/random', name: 'app_image_random')]
    public function random(
        Request $request,
        UnsplashImageService $unsplashImageService,
        FrameConfigurationService $configurationService
    ): Response
    {
        $tag = $request->query->get('tag');
        if (!$tag){
            $tag = $configurationService->getCurrentTag();
        }

        $randomImage = $unsplashImageService->getNextRandomImage($tag);

        $configurationService->setCurrentArtworkId($randomImage->getId());

        return new JsonResponse(['url' => $randomImage->getUrl()]);
    }
}
