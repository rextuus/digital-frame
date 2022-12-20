<?php

namespace App\Service\Image\Unsplash;

use App\Service\Image\ImageData;
use Unsplash\HttpClient;
use Unsplash\Photo;
use Unsplash\Search;
use function dd;

class UnsplashApiService
{
    private const ACCESS_KEY = 'IggqUsh5jKqF7WtHOiX64x8BYrLSfC86SyrmySDaWFY';
    private const SECRET = 'MUjMY6ouEe8X7f3qz6fO7B3vJjpsbZYTgIdOMrmf1Kw';
    private const DEFAULT_PAGES = 30;
    private const DEFAULT_ORIENTATION = 'portrait';

    public function __construct()
    {
    }


    private function getClient(): void
    {
        HttpClient::init([
            'applicationId'	=> self::ACCESS_KEY,
            'secret'	=> self::SECRET,
            'callbackUrl'	=> 'https://your-application.com/oauth/callback',
            'utmSource' => 'digital-frame'
        ]);
        $scopes = ['public'];
        HttpClient::$connection->getConnectionUrl($scopes);
    }

    public function getRandomImageLinks(): array
    {
        $this->getClient();

        $filters = ['count' => self::DEFAULT_PAGES, 'orientation' => self::DEFAULT_ORIENTATION];
        return Photo::random($filters)->toArray();
    }

    public function getImageLinksByTag(string $search): array
    {
        $this->getClient();

        if ($search === '')
        {
            return [];
        }
        $result = Search::photos(
            $search,
            1,
            self::DEFAULT_PAGES,
            self::DEFAULT_ORIENTATION
        );
        return $result->getArrayObject()->toArray();
    }
}