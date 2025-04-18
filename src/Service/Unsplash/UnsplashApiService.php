<?php

namespace App\Service\Unsplash;

use App\Entity\SearchTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Unsplash\HttpClient;
use Unsplash\Photo;
use Unsplash\Search;

class UnsplashApiService
{
    private const DEFAULT_PAGES = 30;
    private const DEFAULT_ORIENTATION = 'portrait';

    public function __construct(
        #[Autowire('%env(UNSPLASH_ACCESS_KEY)%')]
        private readonly string $unsplashAccessKey,
        #[Autowire('%env(UNSPLASH_SECRET)%')]
        private readonly string $unsplashSecret,
    ) {
    }

    private function getClient(): void
    {
        HttpClient::init([
            'applicationId'	=> $this->unsplashAccessKey,
            'secret'	=> $this->unsplashSecret,
            'callbackUrl'	=> 'https://your-application.com/oauth/callback',
            'utmSource' => 'digital-frame'
        ]);
        $scopes = ['public'];
        HttpClient::$connection->getConnectionUrl($scopes);
    }

    /**
     * @return array<Photo>
     */
    public function getRandomImageLinks(): array
    {
        $this->getClient();

        $filters = ['count' => self::DEFAULT_PAGES, 'orientation' => self::DEFAULT_ORIENTATION];

        return Photo::random($filters)->toArray();
    }

    /**
     * @return array<Photo>
     */
    public function getImageLinksByTag(SearchTag $tag): array
    {
        $this->getClient();

        $search = $tag->getTerm();

        $page = $tag->getCurrentPage();
        if ($page > $tag->getTotalPages()){
            return [];
        }

        $result = Search::photos(
            $search,
            $page,
            self::DEFAULT_PAGES,
            self::DEFAULT_ORIENTATION
        );

        return $result->getArrayObject()->toArray();
    }

    public function getTotalPagesForTag(SearchTag $tag): int
    {
        $this->getClient();

        $search = $tag->getTerm();

        $result = Search::photos(
            $search,
            1,
            self::DEFAULT_PAGES,
            self::DEFAULT_ORIENTATION
        );

        return $result->getTotalPages();
    }
}