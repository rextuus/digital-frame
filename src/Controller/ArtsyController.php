<?php

namespace App\Controller;

use App\Service\Artsy\ArtsyService;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artsy')]
class ArtsyController extends AbstractController
{
    public function __construct(private readonly ArtsyService $artsyService, private FrameConfigurationService $configurationService)
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

    #[Route('/next', name: 'artsy_next')]
    public function next(ArtsyService $artsyService, FrameConfigurationService $configurationService): Response
    {
        $currentArtWork = null;

        $imageId = $configurationService->getNextImageId();
        if ($imageId !== null){
            $currentArtWork = $this->artsyService->getArtworkById($imageId);
            $configurationService->setNextImageId(null);
        }

        if ($currentArtWork === null){
            $currentArtWork = $artsyService->getCurrentArtWork();
        }

        $this->configurationService->setCurrentArtworkId($currentArtWork->getId());

        return new JsonResponse(['image_url' => $currentArtWork->getBestResolutionUrl()]);
    }

    #[Route('/gallery', name: 'artsy_gallery')]
    public function gallery(): Response
    {
        return $this->render('artsy/gallery.html.twig');
    }
}