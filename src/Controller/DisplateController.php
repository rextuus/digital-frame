<?php

namespace App\Controller;

use App\Repository\SearchTagRepository;
use App\Service\Displate\DisplateImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/displate')]
final class DisplateController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly DisplateImageService $displateImageService,
    ) {
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

            $images = $this->displateImageService->fetchAndFilterImagesFromUrl($url);

            // Assign the name if at least one image exists
            if (!empty($images)) {
                $name = $images[0]->getName();
            }
        }

        // Render the template and pass filtered links and breadcrumb name to it
        return $this->render('displate/variant_picker.html.twig', [
            'images' => $images,
            'name' => $name,
            'searchTerm' => $searchTerm ?: '',
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

    #[Route('/tags', name: 'displate_tags', methods: ['GET'])]
    public function tags(DisplateImageService $displateImageService): Response
    {
        $searchTags = $displateImageService->getExistingDisplateTags();

        return $this->render('displate/tags.html.twig', [
            'tags' => $searchTags,
        ]);
    }

    #[Route('/gallery', name: 'displate_gallery', methods: ['GET'])]
    public function images(Request $request, SearchTagRepository $searchTagRepository): Response
    {
        $selectedTag = $request->query->get('tag', null);

        if ($selectedTag !== null) {
            $selectedTag = $searchTagRepository->findOneBy(['term' => $selectedTag]);
        }

        return $this->render('displate/gallery.html.twig', [
            'selectedTag' => $selectedTag,
        ]);
    }
}