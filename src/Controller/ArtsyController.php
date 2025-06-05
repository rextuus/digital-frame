<?php

namespace App\Controller;

use App\Service\Artsy\ArtsyService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    #[Route('/face-test', name: 'app_face_test')]
    public function index(HttpClientInterface $httpClient): Response
    {
        $imagePath = __DIR__ . '/../../assets/images/test_face.jpg';
        $imageUrl = '/../../assets/images/test_face.jpg';

        // 1. Call /analyze
        $analyzeResponse = $httpClient->request('POST', 'http://localhost:5000/analyze', [
            'body' => ['image' => fopen($imagePath, 'r')]
        ]);
        $analyzeData = $analyzeResponse->toArray();

        // 2. Call /identify
        $identifyResponse = $httpClient->request('POST', 'http://localhost:5000/analyze', [
            'body' => ['image' => fopen($imagePath, 'r')]
        ]);
        $identifyData = $identifyResponse->toArray();

        // 3. Merge both responses by face index
        $faces = [];
        foreach ($analyzeData as $index => $face) {
            $box = $face['region'];
            $match = $identifyData[$index]['best_match'] ?? null;

            $faces[] = [
                'x' => $box['x'],
                'y' => $box['y'],
                'w' => $box['w'],
                'h' => $box['h'],
                'age' => $face['age'],
                'emotion' => $face['dominant_emotion'],
                'gender' => $face['dominant_gender'],
                'name' => $match['identity'] ?? null,
                'confidence' => $match['distance'] ?? null
            ];
        }

        return $this->render('face_test/index.html.twig', [
            'imageUrl' => $imageUrl,
            'faces' => $faces
        ]);
    }
}