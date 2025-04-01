<?php

namespace App\Controller;

use App\Service\Artsy\ArtsyService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artsy')]
class ArtsyController extends AbstractController
{
    public function __construct(private readonly ArtsyService $artsyService)
    {
    }

    #[Route('/search/{query}', name: 'artsy_image')]
    public function getArtworkImage(string $query): JsonResponse
    {
        $artworkImageUrl = $this->artsyService->getArtworks();

        if (!$artworkImageUrl) {
            return new JsonResponse(['error' => 'Artwork not found or no image available'], 404);
        }

        return new JsonResponse(['image_url' => $artworkImageUrl]);
    }

    #[Route('/gallery', name: 'artsy_gallery')]
    public function gallery(): Response
    {
        return $this->render('artsy/gallery.html.twig');
    }
}