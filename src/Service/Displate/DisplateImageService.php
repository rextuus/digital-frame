<?php

declare(strict_types=1);

namespace App\Service\Displate;


use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use App\Repository\DisplateImageRepository;
use App\Repository\SearchTagRepository;
use App\Service\Displate\Message\CollectDisplateImageMessage;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Stage\Exception\DisplateNoImagesForTagException;
use App\Service\Unsplash\TagVariant;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DisplateImageService
{
    private const BASE_URL = 'https://displate.com/search?q=%s';
    private const COMMUNITY_CHOICE_TAG = 'community-choice';

    private const IMAGE_URL_PATTERN = '/https:\/\/cdn\.displate\.com\/artwork\/(\d+)x(\d+)\/[a-zA-Z0-9\/._-]+\.jpg/';
    private const NAME_PATTERN = '/"name": "(.*)"/';

    public function __construct(
        private readonly DisplateImageRepository $displateImageRepository,
        private readonly SearchTagRepository $searchTagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $client,
        private readonly MessageBusInterface $messageBus,
        private readonly FrameConfigurationService $configurationService,
    ) {
    }

    /**
     * @return array<SearchTag>
     */
    public function getExistingDisplateTags(): array
    {
        return $this->searchTagRepository->getExistingDisplateTags();
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

    /**
     * @throws DisplateNoImagesForTagException
     */
    public function getNextImageForCurrentTag(): DisplateImage
    {
        $currentTag = $this->configurationService->getCurrentTag();
        $displateImage = $this->displateImageRepository->getNextNonDisplayedOrBlockedImageForCurrentTag($currentTag);
        if ($displateImage === null) {
            throw new DisplateNoImagesForTagException('No image found');
        }

        $displateImage->setViewed(new DateTime());
        $this->entityManager->persist($displateImage);
        $this->entityManager->flush();

        return $displateImage;
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
        $httpClient = HttpClient::create();

        // random UUID as au is required
        $uuid = Uuid::v4()->toRfc4122();

        $response = $httpClient->request('GET', 'https://displate.com/elysium-api/user/v6/search', [
            'query' => [
                'phrase' => $url,
                'page' => 1,
                'au' => $uuid,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (compatible; Symfony HttpClient)',
            ],
        ]);

        $data = json_decode($response->getContent(), true);
        $searchResult = $data['searchResult'];
        $count = $searchResult['count'];
        $artworks = $searchResult['artworks'];

        $estimatedPageCount = (int) ceil($count / count($artworks));

        $images = [];
        foreach ($artworks as $artwork) {
            $externalId = $artwork['externalId'];
            $title = $artwork['title'];

            $url = sprintf('https://displate.com/displate/%s', $externalId);
            $images[] = new ImageDto(
                $url,
                $title,
                null,
                null,
                $url,
                $externalId,
                $estimatedPageCount
            );
        }

        return $images;
    }

    /**
     * @return array<DisplateImage>
     */
    public function storeNewImagesForTag(SearchTag $searchTag): array
    {
        $imageDtos = $this->crawlImagesFromDisplateSearchPage(
            $searchTag->getTerm(),
            $searchTag->getCurrentPage()
        );

        $last = count($imageDtos) - 1;
        foreach ($imageDtos as $index => $imageDto) {
            $message = new CollectDisplateImageMessage(
                $searchTag->getId(),
                $imageDto->getDisplateId(),
                $index === $last,
            );

            $this->messageBus->dispatch($message);
        }

        $searchTag->setCurrentPage($searchTag->getCurrentPage() + 1);
        $this->entityManager->persist($searchTag);
        $this->entityManager->flush();

        return [];
    }

    public function calculateTotalPagesForTag(int $searchTagId): void
    {
        $searchTag = $this->searchTagRepository->find($searchTagId);

        $imageDtos = $this->crawlImagesFromDisplateSearchPage(
            $searchTag->getTerm(),
        );

        if (count($imageDtos) > 0) {
            /** @var ImageDto $image */
            $image = array_pop($imageDtos);
            $totalPages = $image->getTotalPagesForSearchTag();
            $searchTag->setTotalPages($totalPages);
            $searchTag->setCollectingInProgress(false);

            $this->entityManager->persist($searchTag);
            $this->entityManager->flush();
        }
    }

    /**
     * @return array<ImageDto>
     */
    public function fetchAndFilterImagesFromUrl(string $url): array
    {
        // Perform the HTTP GET request to fetch the HTML content
        $response = $this->client->request('GET', $url);
        $html = $response->getContent();

        // Extract image names from the HTML meta data
        preg_match_all(self::NAME_PATTERN, $html, $nameMatches);
        $names = $nameMatches[1];
        $name = $names ? $names[array_key_last($names)] : 'Name not found';

        // Match all image URLs with the specified pattern
        preg_match_all(self::IMAGE_URL_PATTERN, $html, $imageMatches, PREG_SET_ORDER);

        $images = [];
        $seenUrls = [];

        // Filter images based on size and uniqueness by height > 1000
        foreach ($imageMatches as $match) {
            $width = (int) $match[1];
            $height = (int) $match[2];
            $imageUrl = $match[0];

            if ($height > 1000 && !isset($seenUrls[$imageUrl])) {
                $images[] = new ImageDto($imageUrl, $name, $width, $height);
                $seenUrls[$imageUrl] = true; // Mark the URL as seen
            }
        }

        // Sort images by resolution (width * height) in descending order
        usort($images, function (ImageDto $a, ImageDto $b) {
            return ($b->getWidth() * $b->getHeight()) <=> ($a->getWidth() * $a->getHeight());
        });

        return $images;
    }

    /**
     * @return array<DisplateImage>
     */
    public function getImagesByTagWithPagination(int $limit, int $offset, ?SearchTag $tag = null): array
    {
        return $this->displateImageRepository->getPaginatedImagesByTag($limit, $offset, $tag);
    }

    public function getImageCountByTagWithPagination(int $limit, int $offset, ?SearchTag $tag = null): bool
    {
        return $this->displateImageRepository->getPaginatedImagesByTag($limit, $offset, $tag, true);
    }

    public function blockCurrentlyDisplayedImage(): void
    {
        $id = $this->configurationService->getCurrentlyDisplayedImageId();
        $displateImage = $this->displateImageRepository->find($id);
        $displateImage->setBlocked(true);
        $this->entityManager->persist($displateImage);
        $this->entityManager->flush();
    }
}
