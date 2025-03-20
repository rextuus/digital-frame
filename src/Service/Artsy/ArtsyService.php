<?php

namespace App\Service\Artsy;

use App\Entity\ArtsyImage;
use App\Repository\ArtsyImageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtsyService
{
    private ?string $accessToken = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(ARTSY_CLIENT_ID)%')]
        private readonly string $clientId,
        #[Autowire('%env(ARTSY_CLIENT_SECRET)%')]
        private readonly string $clientSecret,
        #[Autowire('%env(ARTSY_API_URL)%')]
        private readonly string $apiUrl,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArtsyImageRepository $artsyImageRepository,
    )
    {
    }

    /**
     * Fetch an access token from the Artsy API.
     *
     * @return string
     */
    public function fetchAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $response = $this->httpClient->request('POST', $this->apiUrl . '/api/tokens/xapp_token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $data = $response->toArray();
        $this->accessToken = $data['token'];

        return $this->accessToken;
    }

    public function getArtworkImageUrl(string $artworkId): ?string
    {
        $accessToken = $this->fetchAccessToken();

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . '/api/artworks/' . $artworkId, [
                'headers' => [
                    'X-XAPP-Token' => $accessToken,
                ],
            ]);

            $data = $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
        if (array_key_exists('_links', $data)) {
            return str_replace('medium', 'large', $data['_links']['thumbnail']['href']);
        }

        // Check if the 'image_url' key exists. Return null if not.
        return $data['image_url'] ?? null;
    }

    public function search(string $query, array $types = ['artist', 'artwork']): array
    {
        $accessToken = $this->fetchAccessToken();

        // Make the API request
        $response = $this->httpClient->request('GET', $this->apiUrl . '/api/search', [
            'headers' => [
                'X-Xapp-Token' => $accessToken,
            ],
            'query' => [
                'q' => $query,
            ],
        ]);

        // If the request fails, handle the error gracefully
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to retrieve search results from Artsy.');
        }

        // Convert response to array
        $data = $response->toArray();

        // Return valid results only
        $results = $data['_embedded']['results'] ?? [];

        $return = [];
        foreach ($results as $result) {
            if (in_array($result['type'], $types)) {
                $return[] = $result;
            }
        }

        $artWorkUrls = [];
        foreach ($return as $key => $value) {
            if (array_key_exists('_links', $value)) {
                $artworkUrl = $value['_links']['self']['href'];
                preg_match('/([^\/]+)$/', $artworkUrl, $matches);
                $id = $matches[1];
                $artWorkUrls[] = $this->getArtworkImageUrl($id);
            }
        }

        return $artWorkUrls;
    }

    public function getArtworks(?string $nextUrl = null): ?array
    {
        $currentUrl = $this->apiUrl . '/api/artworks';
        if($nextUrl !== null){
            $currentUrl = $nextUrl;
        }

        $accessToken = $this->fetchAccessToken();

        try {
            $response = $this->httpClient->request('GET', $currentUrl, [
                'headers' => [
                    'X-XAPP-Token' => $accessToken,
                ],
            ]);

            $data = $response->toArray();

            $nextLink = $data['_links']['next']['href'] ?? null;
            $results = $data['_embedded']['artworks'] ?? [];
        } catch (\Exception $e) {
            return null;
        }

        // Check if the 'image_url' key exists. Return null if not.
        $last = array_key_last($results);
        $artworks = [];
        foreach ($results as $key => $value) {
            $artistInformation = $this->resolveArtistInformation($value['_links']['artists']['href']);

            $link = null;
            if ($last === $key){
                $link = $nextLink;
            }

            $artworks[] = $this->storeArtwork($value, $artistInformation, $link);
        }

//        dd($artworks);
        return $data;
    }

    public function storeArtworksFromNextPageUrlInDatabase(): void
    {
        $imageWithNextUrl = $this->artsyImageRepository->getNextPageUrl();

        $this->getArtworks($imageWithNextUrl->getNextPageUrl());

        $imageWithNextUrl->setNextPageUrlStored(new DateTime());
        $this->entityManager->persist($imageWithNextUrl);
        $this->entityManager->flush();
    }

    public function resolveArtistInformation(string $artistUri): ?ArtistInformation
    {
        $accessToken = $this->fetchAccessToken();
        $artistInformation = new ArtistInformation();

        try {
            $response = $this->httpClient->request('GET', $artistUri, [
                'headers' => [
                    'X-XAPP-Token' => $accessToken,
                ],
            ]);

            $data = $response->toArray();
            $artists = $data['_embedded']['artists'];
            $artistInformation->setName($artists[array_key_first($artists)]['name']);
        } catch (\Exception $e) {
            return null;
        }

        return $artistInformation;
    }

    public function storeArtwork(array $values, ?ArtistInformation $artistInformation, ?string $nextLink = null): ArtsyImage
    {
        $artsyImage = new ArtsyImage();
        $artsyImage->setName($values['title']);

        $artistName = $values['slug'];
        if ($artistInformation !== null) {
            $artistName = $artistInformation->getName();
        }
        $artsyImage->setArtist($artistName);

        $link = $values['_links']['thumbnail']['href'];
        $artsyImage->setMediumResolutionUrl($link);

        $imageVersions = $values['image_versions'];

        $priorityOrder = ['larger', 'large', 'medium'];
        $highestResolution = 'medium';
        foreach ($priorityOrder as $version) {
            if (in_array($version, $imageVersions, true)) {
                $highestResolution = $version;
                break;
            }
        }
        $link = str_replace('medium', $highestResolution, $link);

        $width = $values['dimensions']['cm']['width'] ?? 0.0;
        $height = $values['dimensions']['cm']['height'] ?? 0.0;

        $artsyImage->setBestResolutionUrl($link);
        $artsyImage->setMaxVersion($highestResolution);
        $artsyImage->setWidth($width);
        $artsyImage->setHeight($height);
        $artsyImage->setNextPageUrl($nextLink);
        $artsyImage->setViewed(null);

        $categoryValue = $values['category'];
        $category = Category::tryFrom($categoryValue);
        if ($category === null) {
            $artsyImage->setNewCategory($categoryValue);
            $category = Category::UNKNOWN;
        }
        $artsyImage->setCategory($category);

        $this->entityManager->persist($artsyImage);

        if ($nextLink !== null) {
            $this->entityManager->flush();
        }

        return $artsyImage;
    }

    public function getCurrentArtwork(): ArtsyImage
    {
        $artsyImage = $this->artsyImageRepository->findCurrentArtwork();

        // if it's last one collect more
        if ($artsyImage->getNextPageUrl() !== null) {
            $this->getArtworks($artsyImage->getNextPageUrl());
        }

        // mark new on as viewed
        $artsyImage->setViewed(new DateTime());
        $this->entityManager->persist($artsyImage);
        $this->entityManager->flush();

        return $artsyImage;
    }

    public function getArtworkById(int $imageId): ?ArtsyImage
    {
        return $this->artsyImageRepository->find($imageId);
    }
}
