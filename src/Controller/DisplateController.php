<?php

namespace App\Controller;

use App\Service\Displate\DisplateImageService;
use App\Service\Displate\ImageDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function PHPUnit\Framework\matches;

#[Route('/displate')]
final class DisplateController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly DisplateImageService $displateImageService,
    )
    {
    }

    #[Route('/', name: 'displate_add', methods: ['GET', 'POST'])]
    public function addDisplateImage(Request $request): Response
    {
        $displateId = $request->get('displate_id');
        $searchTerm = $request->get('search_term');

        $images = [];
        $name = 'Name not found'; // Variable to store the breadcrumb name
        $url = $request->request->get('displate_url');

        if (($request->isMethod('POST') && $url) || ($displateId)) {
            if ($displateId) {
                $url = sprintf('https://displate.com/displate/%s', $displateId);
            }

            // Perform the HTTP GET request
            $response = $this->client->request('GET', $url);

            // Get the page content (HTML source)
            $html = $response->getContent();


            preg_match_all('/"name": "(.*)"/', $html, $matches);
            $names = $matches[1];
            $name = $names[array_key_last($names)];

            // Match all image URLs with the provided pattern
            preg_match_all(
                '/https:\/\/cdn\.displate\.com\/artwork\/(\d+)x(\d+)\/[a-zA-Z0-9\/._-]+\.jpg/',
                $html,
                $matches,
                PREG_SET_ORDER
            );

            // Filter images based on height and remove duplicates
            $seenUrls = [];
            foreach ($matches as $match) {
                $width = (int) $match[1];  // Extract width from the URL
                $height = (int) $match[2]; // Extract height from the URL
                $imageUrl = $match[0];    // Full URL of the image

                // Check height > 1000 and uniqueness
                if ($height > 1000 && !isset($seenUrls[$imageUrl])) {
                    $imageDto = new ImageDto($imageUrl, $name, $width, $height);
                    $images[] = $imageDto;
                    $seenUrls[$imageUrl] = true; // Mark URL as seen
                }
            }
        }

        // Render the template and pass filtered links and breadcrumb name to it
        return $this->render('displate/variant_picker.html.twig', [
            'images' => $images,
            'name' => $name,
            'searchTerm' => $searchTerm ?:  '',
        ]);
    }

    #[Route('/search', name: 'displate_search', methods: ['GET'])]
    public function searchDisplate(Request $request): Response
    {
        $searchTerm = $request->query->get('q', ''); // Get search term from query string
        $images = [];

        if (!empty($searchTerm)) {
            $images = $this->displateImageService->crawlImagesFromDisplateSearchPage(urlencode($searchTerm));
        }

        return $this->render('displate/search.html.twig', [
            'images' => $images,
            'searchTerm' => $searchTerm,
        ]);
    }


}