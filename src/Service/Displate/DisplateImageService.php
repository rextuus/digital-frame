<?php

declare(strict_types=1);

namespace App\Service\Displate;


use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use App\Repository\DisplateImageRepository;
use App\Repository\SearchTagRepository;
use App\Service\Unsplash\TagVariant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Panther\Client;

class DisplateImageService
{
    private const BASE_URL = 'https://displate.com/search?q=%s';
    private const COMMUNITY_CHOICE_TAG = 'community-choice';


    public function __construct(
        private readonly DisplateImageRepository $displateImageRepository,
        private readonly SearchTagRepository $searchTagRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<SearchTag>
     */
    public function getExistingDisplateTags(): array
    {
        return $this->displateImageRepository->getExistingDisplateTags();
    }

    public function getArtworkById(int $imageId): ?DisplateImage
    {
        return $this->displateImageRepository->find($imageId);
    }

    public function getRandomImage(): ?DisplateImage
    {
        $totalCount = $this->displateImageRepository->count([]);
        $randomId = rand(1, $totalCount);

        return $this->displateImageRepository->find($randomId);
    }

    public function getNextCommunityChoiceImage(): DisplateImage
    {
        $communityChoiceTag = $this->searchTagRepository->findOneBy(['ident' => self::COMMUNITY_CHOICE_TAG]);
        if ($communityChoiceTag === null) {
            $communityChoiceTag = new SearchTag();
            $communityChoiceTag->setVariant(TagVariant::DISPLATE);
            $communityChoiceTag->setTerm(self::COMMUNITY_CHOICE_TAG);
            $communityChoiceTag->setTotalPages(93);
            $communityChoiceTag->setCurrentPage(1);
            $communityChoiceTag->setFullyLStored(false);

            $displateImages = $this->storeNewImagesForTag($communityChoiceTag);

            $this->entityManager->persist($communityChoiceTag);
            $this->entityManager->flush();

            return $displateImages[0];
        }

        $displateImages = $this->storeNewImagesForTag($communityChoiceTag);

        $this->entityManager->flush();


        return $displateImages[0];
    }

    /**
     * @return array<ImageDto>
     */
    public function crawlImagesFromDisplateSearchPage(string $url, ?int $page = null): array
    {
        $url = sprintf(self::BASE_URL, $url);

        if ($page !== null) {
            $url = sprintf('%s&page=%d', $url, $page);
        }

        $images = [];

        $client = Client::createChromeClient();

        // Navigate to the URL
        $client->request('GET', $url);

        // Wait until the results are loaded (based on a CSS selector for results)
        $crawler = $client->waitFor('div[data-testid="masonry-grid-item"]', 5);

        $maxPages = (int)$crawler->filter('span[data-testid="pagination-maxpage"]')->text();

        // Scrape the dynamically loaded content
        $crawler->filter('div[data-testid="masonry-grid-item"]')->each(function ($node) use (&$images, $maxPages) {
            $imageUrl = $node->filter('img[data-testid="artwork-img"]')->attr('src') ?? null;
            $title = $node->filter('img[data-testid="artwork-img"]')->attr('alt') ?? 'No title';
            $relativeSearchLink = $node->filter('a.ElysiumLink_link__xHW5f')->attr('href') ?? null;
            $searchPreviewLink = $imageUrl;

            // Build the full detail link
            $detailLink = $relativeSearchLink ? sprintf('https://displate.com%s', $relativeSearchLink) : null;

            if ($searchPreviewLink && $detailLink) {
                $linkParts = explode('/', $detailLink);
                $images[] = new ImageDto(
                    $searchPreviewLink,
                    $title,
                    null,
                    null,
                    $searchPreviewLink,
                    (int) $linkParts[array_key_last($linkParts)],
                    $maxPages
                );
            }
        });

        $client->quit();

        return $images;
    }

    /**
     * @return array<DisplateImage>
     */
    private function storeNewImagesForTag(SearchTag $searchTag): array
    {
        $imageDtos = $this->crawlImagesFromDisplateSearchPage(
            self::COMMUNITY_CHOICE_TAG,
            $searchTag->getCurrentPage()
        );

        $displateImages = [];
        foreach ($imageDtos as $imageDto) {
            $displateImage = new DisplateImage();
            $displateImage->setName($imageDto->getName());
            $displateImage->setUrl($imageDto->getUrl());
            $displateImage->setSearchTag($searchTag);
            $displateImage->setViewed(null);

            $this->entityManager->persist($displateImage);
            $displateImages[] = $displateImage;
        }
        $searchTag->setCurrentPage($searchTag->getCurrentPage() + 1);
        $this->entityManager->persist($searchTag);


        return $displateImages;
    }
}
